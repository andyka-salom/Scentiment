<?php

use App\Services\FieldTypeRegistry;
use App\Models\FormField;
use App\Contracts\FieldTypeInterface;

test('field type registry registers all 17 types', function () {
    $registry = app(FieldTypeRegistry::class);

    $expectedTypes = [
        'short_text', 'long_text', 'email', 'number', 'phone', 
        'radio', 'dropdown', 'checkbox', 'scale', 'rating', 
        'date', 'time', 'file', 'matrix', 'signature', 
        'section', 'statement'
    ];

    foreach ($expectedTypes as $type) {
        expect($registry->has($type))->toBeTrue();
        expect($registry->get($type))->toBeInstanceOf(FieldTypeInterface::class);
    }
});

test('text field type rules', function () {
    $registry = app(FieldTypeRegistry::class);
    $textFieldType = $registry->get('short_text');

    $field = new FormField([
        'is_required' => true,
        'config' => ['min' => 3, 'max' => 10]
    ]);

    $rules = $textFieldType->rules($field);
    expect($rules)->toContain('required')
        ->toContain('string')
        ->toContain('min:3')
        ->toContain('max:10');
});

test('email field type rules', function () {
    $registry = app(FieldTypeRegistry::class);
    $emailFieldType = $registry->get('email');

    $field = new FormField(['is_required' => false]);

    $rules = $emailFieldType->rules($field);
    expect($rules)->toContain('nullable')
        ->toContain('email');
});

test('phone field type rules', function () {
    $registry = app(FieldTypeRegistry::class);
    $phoneFieldType = $registry->get('phone');

    $field = new FormField(['is_required' => true]);

    $rules = $phoneFieldType->rules($field);
    expect($rules)->toContain('required');
    expect($rules[1])->toStartWith('regex:');
});

test('number field type rules', function () {
    $registry = app(FieldTypeRegistry::class);
    $numberFieldType = $registry->get('number');

    $field = new FormField([
        'is_required' => true,
        'config' => ['min' => 5, 'max' => 100]
    ]);

    $rules = $numberFieldType->rules($field);
    expect($rules)->toContain('required')
        ->toContain('numeric')
        ->toContain('min:5')
        ->toContain('max:100');
});

test('scale field type rules and scoring', function () {
    $registry = app(FieldTypeRegistry::class);
    $scaleFieldType = $registry->get('scale');

    $field = new FormField([
        'is_required' => true,
        'config' => ['scale_min' => 1, 'scale_max' => 10]
    ]);

    $rules = $scaleFieldType->rules($field);
    expect($rules)->toContain('required')
        ->toContain('numeric')
        ->toContain('between:1,10');

    expect($scaleFieldType->score(5, $field))->toBe(5.0);
    expect($scaleFieldType->score('invalid', $field))->toBeNull();
});

test('rating field type rules', function () {
    $registry = app(FieldTypeRegistry::class);
    $ratingFieldType = $registry->get('rating');

    $field = new FormField([
        'is_required' => true,
        'config' => ['stars' => 5]
    ]);

    $rules = $ratingFieldType->rules($field);
    expect($rules)->toContain('required')
        ->toContain('integer')
        ->toContain('between:1,5');
});
