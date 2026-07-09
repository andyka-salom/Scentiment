<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormField;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as LaravelValidator;

class DynamicValidatorBuilder
{
    public function __construct(
        protected FieldTypeRegistry $registry,
        protected ConditionalLogicEvaluator $logicEvaluator
    ) {}

    /**
     * Build a validator for form submission.
     */
    public function build(Form $form, array $input, ?array $schema = null): LaravelValidator
    {
        $fields = $this->getFields($form, $schema);
        $rules = [];
        $messages = [];
        $attributes = [];

        // First pass: filter out fields hidden by conditional logic
        // We evaluate logic on the raw input
        $visibleFieldKeys = [];
        foreach ($fields as $field) {
            if ($this->logicEvaluator->isVisible($field, $input)) {
                $visibleFieldKeys[] = $field->field_key;
            }
        }

        foreach ($fields as $field) {
            // If field is hidden, skip rules entirely (required fields won't fail validation)
            if (!in_array($field->field_key, $visibleFieldKeys)) {
                continue;
            }

            if ($this->registry->has($field->type)) {
                $typeHandler = $this->registry->get($field->type);
                $fieldRules = $typeHandler->rules($field);

                if (!empty($fieldRules)) {
                    $rules[$field->field_key] = $fieldRules;
                    $attributes[$field->field_key] = $field->label;
                }
            }
        }

        return Validator::make($input, $rules, $messages, $attributes);
    }

    /**
     * Get fields as Eloquent models or construct them from the version schema.
     * @return FormField[]
     */
    protected function getFields(Form $form, ?array $schema = null): array
    {
        if ($schema && isset($schema['fields'])) {
            $fields = [];
            foreach ($schema['fields'] as $fData) {
                // Instantiate a temporary FormField model so it behaves identically
                $field = new FormField($fData);
                $field->id = $fData['id'] ?? null;
                // Add options relation
                $options = collect();
                if (isset($fData['options'])) {
                    foreach ($fData['options'] as $oData) {
                        $options->push(new \App\Models\FormFieldOption($oData));
                    }
                }
                $field->setRelation('options', $options);
                $fields[] = $field;
            }
            return $fields;
        }

        // Default: load from form fields
        return $form->fields()->with('options')->get()->all();
    }
}
