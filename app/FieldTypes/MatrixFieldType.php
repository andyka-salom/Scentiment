<?php

namespace App\FieldTypes;

use App\Models\FormField;

class MatrixFieldType extends BaseFieldType
{
    public function rules(FormField $field): array
    {
        $rules = parent::rules($field);
        $rules[] = 'array';
        return $rules;
    }
}
