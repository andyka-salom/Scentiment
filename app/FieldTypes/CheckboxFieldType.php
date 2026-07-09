<?php

namespace App\FieldTypes;

use App\Models\FormField;
use Illuminate\Support\Collection;

class CheckboxFieldType extends BaseFieldType
{
    public function rules(FormField $field): array
    {
        $rules = parent::rules($field);
        $rules[] = 'array';

        $config = $field->config ?? [];
        if (isset($config['min'])) {
            $rules[] = 'min:' . $config['min'];
        }
        if (isset($config['max'])) {
            $rules[] = 'max:' . $config['max'];
        }

        return $rules;
    }

    public function score(mixed $value, FormField $field): ?float
    {
        if (!is_array($value)) {
            return null;
        }

        $scoreSum = 0;
        foreach ($value as $val) {
            $option = $field->options->firstWhere('value', $val);
            if ($option) {
                $scoreSum += (float) $option->score;
            }
        }

        return $scoreSum;
    }

    public function aggregate(Collection $answers, FormField $field): array
    {
        $counts = [];

        // Initialize counts for all options to 0
        foreach ($field->options as $option) {
            $counts[$option->label] = 0;
        }

        foreach ($answers as $answer) {
            $val = $answer->value_json;
            if (!is_array($val)) {
                $val = json_decode($answer->value_text ?? '[]', true);
            }

            if (is_array($val)) {
                foreach ($val as $singleVal) {
                    $option = $field->options->firstWhere('value', $singleVal);
                    if ($option) {
                        $counts[$option->label] = ($counts[$option->label] ?? 0) + 1;
                    }
                }
            }
        }

        return $counts;
    }
}
