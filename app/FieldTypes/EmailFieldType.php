<?php

namespace App\FieldTypes;

use App\Models\FormField;

class EmailFieldType extends BaseFieldType
{
    public function rules(FormField $field): array
    {
        $rules = parent::rules($field);
        $rules[] = 'email';
        return $rules;
    }
}
