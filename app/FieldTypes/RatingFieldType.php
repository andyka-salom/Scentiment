<?php

namespace App\FieldTypes;

use App\Models\FormField;

class RatingFieldType extends ScaleFieldType
{
    public function rules(FormField $field): array
    {
        $rules = parent::rules($field);
        
        $config = $field->config ?? [];
        $max = $config['stars'] ?? 5; // default 5 stars

        // Remove the default ScaleFieldType rule and add Rating specific rules
        $rules = array_filter($rules, fn($r) => !str_starts_with($r, 'between:'));
        $rules[] = 'integer';
        $rules[] = 'between:1,' . $max;

        return $rules;
    }
}
