<?php

namespace App\Services;

use App\Models\Form;
use App\Models\Response as FormResponse;
use App\Models\ResponseAnswer;
use App\Models\ResponseFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class FormSubmissionService
{
    public function __construct(
        protected DynamicValidatorBuilder $validatorBuilder,
        protected ScoreCalculator $scoreCalculator,
        protected FieldTypeRegistry $registry,
        protected ConditionalLogicEvaluator $logicEvaluator
    ) {}

    /**
     * Submit a form response.
     * @throws ValidationException|Exception
     */
    public function submit(Form $form, array $rawInput, string $ip, string $userAgent, ?int $userId = null): FormResponse
    {
        // ── Security: Honeypot check (bot trap) ──────────────────────────────
        // _hp field must be empty; _duration must be >= 3 seconds
        if (!empty($rawInput['_hp'])) {
            // Silent reject — return a fake success to avoid fingerprinting
            throw new Exception("Permintaan Anda tidak dapat diproses. Silakan coba lagi.");
        }
        $fillDuration = isset($rawInput['_duration']) ? (int) $rawInput['_duration'] : 999;
        if ($fillDuration < 3) {
            throw new Exception("Form diisi terlalu cepat. Silakan coba lagi.");
        }
        // 1. Validate Form Status & Open/Close Dates
        if ($form->status !== Form::STATUS_PUBLISHED) {
            throw new Exception("Form is not published.");
        }

        $now = now();
        if ($form->opens_at && $now->lt($form->opens_at)) {
            throw new Exception("Form has not opened yet.");
        }
        if ($form->closes_at && $now->gt($form->closes_at)) {
            throw new Exception("Form has been closed.");
        }

        // 2. Perform submission inside transaction for consistency, concurrency and quotas
        return DB::transaction(function () use ($form, $rawInput, $ip, $userAgent, $userId) {
            // Lock form record to prevent race conditions on quota
            $lockedForm = Form::where('id', $form->id)->lockForUpdate()->first();
            $settings = $lockedForm->settings ?? [];

            // ── Security: Whitelist field_key from valid form definition ──────
            // CLAUDE.md: never use $request->all() directly; only accept keys defined in the form
            $fields = $lockedForm->fields()->with('options')->get();
            $allowedKeys = $fields->pluck('field_key')->toArray();
            $systemKeys  = ['_token', '_method', '_hp', '_duration', '_submit_token'];
            $filteredInput = array_filter(
                $rawInput,
                fn($key) => in_array($key, $allowedKeys) || in_array($key, $systemKeys),
                ARRAY_FILTER_USE_KEY
            );

            // Check Quota
            if (isset($settings['response_limit']) && (int) $settings['response_limit'] > 0) {
                $completedCount = $lockedForm->responses()->where('status', 'complete')->count();
                if ($completedCount >= (int) $settings['response_limit']) {
                    throw new Exception("Kuota respon untuk form ini telah terpenuhi.");
                }
            }

            // Check One Response Limit
            if (isset($settings['one_response_per_user']) && $settings['one_response_per_user']) {
                if ($userId) {
                    $exists = $lockedForm->responses()->where('user_id', $userId)->where('status', 'complete')->exists();
                    if ($exists) {
                        throw new Exception("Anda sudah mengirimkan respon untuk form ini.");
                    }
                } else {
                    // Public - track by IP Hash as fallback
                    $ipHash = hash('sha256', $ip . config('app.key'));
                    $exists = $lockedForm->responses()->where('ip_hash', $ipHash)->where('status', 'complete')->exists();
                    if ($exists) {
                        throw new Exception("Browser ini sudah mengirimkan respon untuk form ini.");
                    }
                }
            }

            // 3. Dynamic Validation — uses filteredInput (whitelisted)
            $validator = $this->validatorBuilder->build($lockedForm, $filteredInput);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            $validatedData = $validator->validated();

            // 4. Calculate Scores
            $scoring = $this->scoreCalculator->calculate($lockedForm, $validatedData);

            // 5. Build normalized answers_snapshot
            $snapshot = [];
            $visibleFields = [];
            
            // $fields already loaded above in whitelist block

            
            // First pass: identify visible fields based on validated logic
            foreach ($fields as $field) {
                if ($this->logicEvaluator->isVisible($field, $validatedData)) {
                    $visibleFields[] = $field;
                }
            }

            foreach ($visibleFields as $field) {
                if (!$this->registry->has($field->type)) {
                    continue;
                }

                $typeHandler = $this->registry->get($field->type);
                $value = $validatedData[$field->field_key] ?? null;

                if ($value !== null) {
                    $normalizedVal = $typeHandler->normalize($value, $field);
                    $snapshot[$field->field_key] = $normalizedVal;
                }
            }

            // 6. Create Response — IP stored as SHA-256 hash (CLAUDE.md security)
            $ipHash = hash('sha256', $ip . config('app.key'));
            
            $response = FormResponse::create([
                'form_id'          => $lockedForm->id,
                'form_version'     => $lockedForm->current_version,
                'user_id'          => $userId,
                'status'           => 'complete',
                'answers_snapshot' => $snapshot,
                'score'            => $scoring['score'],
                'score_breakdown'  => $scoring['breakdown'],
                'grade'            => $scoring['grade'],
                'duration_seconds' => isset($rawInput['_duration']) ? (int) $rawInput['_duration'] : null,
                'ip_hash'          => $ipHash,
                'user_agent'       => substr($userAgent ?? '', 0, 500),
                'submitted_at'     => now(),
            ]);

            // 7. Write to response_answers for analytics
            foreach ($visibleFields as $field) {
                if (!$this->registry->has($field->type)) {
                    continue;
                }

                $typeHandler = $this->registry->get($field->type);
                $snapshotValue = $snapshot[$field->field_key] ?? null;

                if ($snapshotValue !== null) {
                    $cols = $typeHandler->toAnswerColumns($snapshotValue);
                    if (!empty($cols) || array_key_exists('value_text', $cols)) {
                        ResponseAnswer::create([
                            'response_id' => $response->id,
                            'form_id' => $lockedForm->id,
                            'field_id' => $field->id,
                            'field_key' => $field->field_key,
                            'value_text' => $cols['value_text'] ?? null,
                            'value_number' => $cols['value_number'] ?? null,
                            'value_json' => $cols['value_json'] ?? null,
                        ]);
                    }

                    // If file upload or signature, link file to response
                    if (in_array($field->type, ['file', 'signature'])) {
                        $fileIds = is_array($snapshotValue) ? $snapshotValue : [$snapshotValue];
                        ResponseFile::whereIn('id', array_filter($fileIds))
                            ->where('field_id', $field->id)
                            ->update(['response_id' => $response->id]);
                    }
                }
            }

            return $response;
        });
    }
}
