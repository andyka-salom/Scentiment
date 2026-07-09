<?php

namespace App\FieldTypes;

use App\Models\FormField;

class SectionFieldType extends BaseFieldType
{
    public function rules(FormField $field): array
    {
        return [];
    }

    public function normalize(mixed $input, FormField $field): mixed
    {
        return null;
    }

    public function toAnswerColumns(mixed $value): array
    {
        return [];
    }
}
