<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormField;

class ScoreCalculator
{
    public function __construct(
        protected FieldTypeRegistry $registry,
        protected ConditionalLogicEvaluator $logicEvaluator
    ) {}

    /**
     * Calculate score, breakdown, and grade for form answers.
     */
    public function calculate(Form $form, array $answers, ?array $schema = null): array
    {
        $fields = $this->getFields($form, $schema);
        $totalScore = 0.0;
        $breakdown = [];
        $hasScorableFields = false;

        // Evaluate visibility so we don't score hidden fields
        $visibleFieldKeys = [];
        foreach ($fields as $field) {
            if ($this->logicEvaluator->isVisible($field, $answers)) {
                $visibleFieldKeys[] = $field->field_key;
            }
        }

        foreach ($fields as $field) {
            if (!in_array($field->field_key, $visibleFieldKeys)) {
                continue;
            }

            if (!$this->registry->has($field->type)) {
                continue;
            }

            $typeHandler = $this->registry->get($field->type);
            $rawValue = $answers[$field->field_key] ?? null;

            $optionScore = $typeHandler->score($rawValue, $field);
            if ($optionScore !== null) {
                $hasScorableFields = true;
                $config = $field->config ?? [];
                $weight = (float) ($config['weight'] ?? 1.0);
                $weightedScore = $optionScore * $weight;

                $totalScore += $weightedScore;
                $breakdown[$field->field_key] = [
                    'value' => $rawValue,
                    'score' => $optionScore,
                    'weight' => $weight,
                    'weighted_score' => $weightedScore,
                ];
            }
        }

        $grade = null;
        if ($hasScorableFields && $form->is_assessment) {
            $settings = $form->settings ?? [];
            $gradeMap = $settings['grade_map'] ?? [];
            foreach ($gradeMap as $map) {
                $min = $map['min'] ?? 0;
                $max = $map['max'] ?? 100;
                if ($totalScore >= $min && $totalScore <= $max) {
                    $grade = $map['label'] ?? null;
                    break;
                }
            }
        }

        return [
            'score' => $hasScorableFields ? $totalScore : null,
            'breakdown' => $breakdown,
            'grade' => $grade,
        ];
    }

    /**
     * @return FormField[]
     */
    protected function getFields(Form $form, ?array $schema = null): array
    {
        if ($schema && isset($schema['fields'])) {
            $fields = [];
            foreach ($schema['fields'] as $fData) {
                $field = new FormField($fData);
                $field->id = $fData['id'] ?? null;
                $options = collect();
                if (isset($fData['options'])) {
                    foreach ($fData['options'] as $oData) {
                        $options->push(new \App\Models\FormFieldOption($oData));
                    }
                }
                $field->setRelation('options', $options);
                $fields[] = $field;
            }
            return $fields;
        }

        return $form->fields()->with('options')->get()->all();
    }
}
