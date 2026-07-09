<?php

namespace App\Services;

use App\Models\FormField;

class ConditionalLogicEvaluator
{
    /**
     * Determine if a field should be visible based on current answers.
     */
    public function isVisible(FormField $field, array $answers): bool
    {
        $logic = $field->logic;
        if (empty($logic) || empty($logic['rules'])) {
            return true;
        }

        $operator = strtolower($logic['operator'] ?? 'and');
        $rules = $logic['rules'];

        $results = [];
        foreach ($rules as $rule) {
            $targetKey = $rule['field_key'] ?? null;
            if (!$targetKey) {
                continue;
            }

            $op = $rule['op'] ?? 'equals';
            $expectedValue = $rule['value'] ?? null;
            $actualValue = $answers[$targetKey] ?? null;

            $results[] = $this->evaluateRule($op, $expectedValue, $actualValue);
        }

        if (empty($results)) {
            return true;
        }

        if ($operator === 'or') {
            return in_array(true, $results, true);
        }

        // Default to AND
        return !in_array(false, $results, true);
    }

    protected function evaluateRule(string $op, mixed $expected, mixed $actual): bool
    {
        switch ($op) {
            case 'equals':
                return $actual == $expected;
            case 'not_equals':
                return $actual != $expected;
            case 'contains':
                if (is_array($actual)) {
                    return in_array($expected, $actual);
                }
                return is_string($actual) && str_contains($actual, (string) $expected);
            case 'greater_than':
                return (float) $actual > (float) $expected;
            case 'less_than':
                return (float) $actual < (float) $expected;
            case 'is_answered':
                return !empty($actual);
            case 'is_empty':
                return empty($actual);
            default:
                return true;
        }
    }
}
