<?php

namespace App\FieldTypes;

use App\Contracts\FieldTypeInterface;
use App\Models\FormField;
use Illuminate\Support\Collection;

abstract class BaseFieldType implements FieldTypeInterface
{
    public function rules(FormField $field): array
    {
        $rules = [];
        if ($field->is_required) {
            $rules[] = 'required';
        } else {
            $rules[] = 'nullable';
        }
        return $rules;
    }

    public function normalize(mixed $input, FormField $field): mixed
    {
        return $input;
    }

    public function toAnswerColumns(mixed $value): array
    {
        return [
            'value_text' => is_scalar($value) ? (string) $value : json_encode($value),
            'value_number' => is_numeric($value) ? (float) $value : null,
            'value_json' => !is_scalar($value) ? $value : null,
        ];
    }

    public function score(mixed $value, FormField $field): ?float
    {
        return null;
    }

    public function aggregate(Collection $answers, FormField $field): array
    {
        // Default text aggregation or count of unique values
        return $answers->groupBy('value_text')
            ->map(fn($group) => $group->count())
            ->toArray();
    }
}
