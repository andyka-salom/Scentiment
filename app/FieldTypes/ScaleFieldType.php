<?php

namespace App\FieldTypes;

use App\Models\FormField;
use Illuminate\Support\Collection;

class ScaleFieldType extends BaseFieldType
{
    public function rules(FormField $field): array
    {
        $rules = parent::rules($field);
        $rules[] = 'numeric';

        $config = $field->config ?? [];
        $min = $config['scale_min'] ?? 1;
        $max = $config['scale_max'] ?? 5;

        $rules[] = 'between:' . $min . ',' . $max;

        return $rules;
    }

    public function score(mixed $value, FormField $field): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    public function aggregate(Collection $answers, FormField $field): array
    {
        $config = $field->config ?? [];
        $min = $config['scale_min'] ?? 1;
        $max = $config['scale_max'] ?? 5;

        $counts = [];
        for ($i = $min; $i <= $max; $i++) {
            $counts[$i] = 0;
        }

        foreach ($answers as $answer) {
            $val = $answer->value_number;
            if ($val !== null) {
                $intVal = (int) $val;
                if (isset($counts[$intVal])) {
                    $counts[$intVal]++;
                }
            }
        }

        return $counts;
    }
}
