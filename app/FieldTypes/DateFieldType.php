<?php

namespace App\FieldTypes;

use App\Models\FormField;

class DateFieldType extends BaseFieldType
{
    public function rules(FormField $field): array
    {
        $rules = parent::rules($field);
        $rules[] = 'date';

        $config = $field->config ?? [];
        if (isset($config['min_date'])) {
            $rules[] = 'after_or_equal:' . $config['min_date'];
        }
        if (isset($config['max_date'])) {
            $rules[] = 'before_or_equal:' . $config['max_date'];
        }

        return $rules;
    }
}
