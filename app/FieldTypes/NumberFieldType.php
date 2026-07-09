<?php

namespace App\FieldTypes;

use App\Models\FormField;

class NumberFieldType extends BaseFieldType
{
    public function rules(FormField $field): array
    {
        $rules = parent::rules($field);
        $rules[] = 'numeric';

        $config = $field->config ?? [];
        if (isset($config['min'])) {
            $rules[] = 'min:' . $config['min'];
        }
        if (isset($config['max'])) {
            $rules[] = 'max:' . $config['max'];
        }

        return $rules;
    }
}
