<?php

namespace App\FieldTypes;

use App\Models\FormField;
use App\Models\FormFieldOption;
use Illuminate\Support\Collection;

class RadioFieldType extends BaseFieldType
{
    public function rules(FormField $field): array
    {
        $rules = parent::rules($field);
        
        $config = $field->config ?? [];
        if (isset($config['allow_other']) && $config['allow_other']) {
            // Can be array or string if custom text is provided
            return $rules;
        }

        // Must be one of the option values
        $optionValues = $field->options->pluck('value')->toArray();
        if (!empty($optionValues)) {
            $rules[] = 'in:' . implode(',', $optionValues);
        }

        return $rules;
    }

    public function score(mixed $value, FormField $field): ?float
    {
        // If it's the custom input "Lainnya"
        if (is_array($value) && isset($value['value']) && $value['value'] === '__other__') {
            return null;
        }

        $option = $field->options->firstWhere('value', $value);
        return $option ? (float) $option->score : null;
    }

    public function aggregate(Collection $answers, FormField $field): array
    {
        $counts = [];
        
        // Initialize counts for all options to 0
        foreach ($field->options as $option) {
            $counts[$option->label] = 0;
        }

        $otherCount = 0;

        foreach ($answers as $answer) {
            $val = $answer->value_text;
            if (empty($val)) {
                continue;
            }

            // Check if value is JSON (custom "Lainnya")
            if (str_starts_with($val, '{')) {
                $decoded = json_decode($val, true);
                if (isset($decoded['value']) && $decoded['value'] === '__other__') {
                    $otherCount++;
                    continue;
                }
            }

            $option = $field->options->firstWhere('value', $val);
            if ($option) {
                $counts[$option->label] = ($counts[$option->label] ?? 0) + 1;
            } else {
                $otherCount++;
            }
        }

        if ($otherCount > 0) {
            $counts['Lainnya'] = $otherCount;
        }

        return $counts;
    }
}
