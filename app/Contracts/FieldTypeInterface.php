<?php

namespace App\Contracts;

use App\Models\FormField;
use Illuminate\Support\Collection;

interface FieldTypeInterface
{
    /**
     * Get validation rules for this field type.
     */
    public function rules(FormField $field): array;

    /**
     * Normalize client-side input for answers_snapshot.
     */
    public function normalize(mixed $input, FormField $field): mixed;

    /**
     * Convert value to answer columns for response_answers (value_text, value_number, value_json).
     * Returns an associative array of columns: ['value_text' => ..., 'value_number' => ..., 'value_json' => ...]
     */
    public function toAnswerColumns(mixed $value): array;

    /**
     * Calculate score for the field (for assessment mode).
     */
    public function score(mixed $value, FormField $field): ?float;

    /**
     * Aggregate responses for analytics.
     */
    public function aggregate(Collection $answers, FormField $field): array;
}
