<?php

namespace App\FieldTypes;

use App\Models\FormField;

class SignatureFieldType extends BaseFieldType
{
    public function rules(FormField $field): array
    {
        $rules = parent::rules($field);
        return $rules;
    }
}
