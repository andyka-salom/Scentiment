<?php

namespace App\FieldTypes;

use App\Models\FormField;

class FileFieldType extends BaseFieldType
{
    public function rules(FormField $field): array
    {
        $rules = parent::rules($field);
        
        $config = $field->config ?? [];
        $maxFiles = $config['max_files'] ?? 1;

        if ($maxFiles > 1) {
            $rules[] = 'array';
            $rules[] = 'max:' . $maxFiles;
            // Each element must exist in response_files
            // Note: since it's dynamic validation, we can validate the array items in logic or via:
            // 'value.*' => 'integer|exists:response_files,id'
        } else {
            // Can be array of 1 element or single integer
            return $rules;
        }

        return $rules;
    }
}
