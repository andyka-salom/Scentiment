<?php

namespace App\FieldTypes;

use App\Models\FormField;

class PhoneFieldType extends BaseFieldType
{
    public function rules(FormField $field): array
    {
        $rules = parent::rules($field);
        // Validates Indonesian phone numbers (starts with 08, 628, or +628)
        $rules[] = 'regex:/^(\+62|62|0)8[1-9][0-9]{6,11}$/';
        return $rules;
    }
}
