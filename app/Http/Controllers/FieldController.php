<?php

namespace App\Http\Controllers;

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormFieldOption;
use App\Services\FormVersionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class FieldController extends Controller
{
    public function __construct(
        protected FormVersionManager $versionManager
    ) {}

    /**
     * Store a new field in the form.
     */
    public function store(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $request->validate([
            'label' => 'required|string|max:255',
            'type' => 'required|string|max:30',
            'is_required' => 'boolean',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
            'options' => 'nullable|array', // for choice fields
        ]);

        // Snapshot if responses exist
        $this->versionManager->snapshotIfNeeded($form);

        return DB::transaction(function () use ($request, $form) {
            // Generate field_key from label
            $baseKey = Str::snake(strtolower($request->label));
            if (empty($baseKey)) {
                $baseKey = 'field';
            }
            $fieldKey = $baseKey;
            $count = 1;
            while ($form->fields()->where('field_key', $fieldKey)->exists()) {
                $fieldKey = $baseKey . '_' . $count;
                $count++;
            }

            // Determine sort order
            $maxOrder = $form->fields()->max('sort_order') ?? 0;

            $field = FormField::create([
                'form_id' => $form->id,
                'field_key' => $fieldKey,
                'type' => $request->type,
                'label' => $request->label,
                'description' => $request->description,
                'is_required' => $request->boolean('is_required'),
                'sort_order' => $maxOrder + 1,
                'config' => $request->config ?? [],
                'logic' => $request->logic,
            ]);

            // Save options if provided
            if ($request->has('options') && is_array($request->options)) {
                foreach ($request->options as $index => $opt) {
                    FormFieldOption::create([
                        'field_id' => $field->id,
                        'value' => $opt['value'] ?? Str::slug($opt['label'] ?? 'option'),
                        'label' => $opt['label'] ?? 'Option',
                        'score' => isset($opt['score']) ? (float) $opt['score'] : null,
                        'sort_order' => $index,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pertanyaan berhasil ditambahkan!',
                'field' => $field->load('options')
            ]);
        });
    }

    /**
     * Update an existing field.
     */
    public function update(Request $request, Form $form, FormField $field)
    {
        $this->authorize('update', $form);

        $request->validate([
            'label' => 'required|string|max:255',
            'is_required' => 'boolean',
            'description' => 'nullable|string',
            'config' => 'nullable|array',
            'logic' => 'nullable|array',
            'options' => 'nullable|array',
        ]);

        // Snapshot if responses exist
        $this->versionManager->snapshotIfNeeded($form);

        return DB::transaction(function () use ($request, $field) {
            $field->update([
                'label' => $request->label,
                'description' => $request->description,
                'is_required' => $request->boolean('is_required'),
                'config' => $request->config ?? [],
                'logic' => $request->logic,
            ]);

            // Sync Options
            if ($request->has('options')) {
                // Delete old options
                $field->options()->delete();
                
                foreach ($request->options as $index => $opt) {
                    if (empty($opt['label'])) continue;

                    FormFieldOption::create([
                        'field_id' => $field->id,
                        'value' => $opt['value'] ?? Str::slug($opt['label']),
                        'label' => $opt['label'],
                        'score' => isset($opt['score']) && $opt['score'] !== '' ? (float) $opt['score'] : null,
                        'sort_order' => $index,
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Pertanyaan berhasil diperbarui!',
                'field' => $field->load('options')
            ]);
        });
    }

    /**
     * Reorder fields (Drag-and-Drop endpoint).
     */
    public function reorder(Request $request, Form $form)
    {
        $this->authorize('update', $form);

        $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:form_fields,id',
        ]);

        // Snapshot if responses exist
        $this->versionManager->snapshotIfNeeded($form);

        DB::transaction(function () use ($request) {
            foreach ($request->order as $index => $fieldId) {
                FormField::where('id', $fieldId)->update(['sort_order' => $index]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Urutan pertanyaan berhasil disimpan!'
        ]);
    }

    /**
     * Delete a field.
     */
    public function destroy(Form $form, FormField $field)
    {
        $this->authorize('update', $form);

        // Snapshot if responses exist
        $this->versionManager->snapshotIfNeeded($form);

        $field->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pertanyaan berhasil dihapus!'
        ]);
    }
}
