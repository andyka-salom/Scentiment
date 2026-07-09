<?php

namespace App\Services;

use App\Models\Form;
use App\Models\FormVersion;

class FormVersionManager
{
    /**
     * Create a schema version snapshot if the form has responses.
     * Returns the version number if a snapshot was created, or null.
     */
    public function snapshotIfNeeded(Form $form): ?int
    {
        // Check if there are responses for the current version
        $hasResponses = $form->responses()->where('form_version', $form->current_version)->exists();
        if (!$hasResponses) {
            return null;
        }

        // Generate schema snapshot
        $fields = $form->fields()->with('options')->get();
        $schema = [
            'fields' => $fields->map(fn($field) => [
                'id' => $field->id,
                'field_key' => $field->field_key,
                'type' => $field->type,
                'label' => $field->label,
                'description' => $field->description,
                'is_required' => $field->is_required,
                'sort_order' => $field->sort_order,
                'config' => $field->config,
                'logic' => $field->logic,
                'options' => $field->options->map(fn($opt) => [
                    'id' => $opt->id,
                    'value' => $opt->value,
                    'label' => $opt->label,
                    'score' => $opt->score,
                    'sort_order' => $opt->sort_order,
                ])->toArray(),
            ])->toArray()
        ];

        // Save FormVersion
        FormVersion::create([
            'form_id' => $form->id,
            'version' => $form->current_version,
            'schema' => $schema,
        ]);

        // Increment form current version
        $newVersion = $form->current_version + 1;
        $form->current_version = $newVersion;
        $form->save();

        return $newVersion - 1;
    }

    /**
     * Get schema for a specific version.
     */
    public function getSchema(Form $form, int $version): ?array
    {
        $vModel = FormVersion::where('form_id', $form->id)->where('version', $version)->first();
        return $vModel ? $vModel->schema : null;
    }
}
