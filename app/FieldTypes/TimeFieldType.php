<?php

namespace App\FieldTypes;

use App\Models\FormField;

class TimeFieldType extends BaseFieldType
{
    public function rules(FormField $field): array
    {
        $rules = parent::rules($field);
        $rules[] = 'regex:/^(?:[01]\d|2[0-3]):[0-5]\d$/'; // Validates HH:MM format
        return $rules;
    }
}
