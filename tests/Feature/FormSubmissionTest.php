<?php

use App\Models\Form;
use App\Models\FormField;
use App\Models\FormFieldOption;
use App\Models\User;
use App\Services\FormSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('submit form writes to both response layers and computes score', function () {
    // 1. Create a User
    $user = User::factory()->create();

    // 2. Create a Published Assessment Form
    $form = Form::create([
        'user_id' => $user->id,
        'title' => 'Test Assessment Form',
        'slug' => 'test-assessment',
        'status' => Form::STATUS_PUBLISHED,
        'access_type' => Form::ACCESS_PUBLIC,
        'is_assessment' => true,
        'settings' => [
            'success_message' => 'Tersimpan!',
            'show_score' => true,
            'grade_map' => [
                ['min' => 0, 'max' => 50, 'label' => 'Cukup'],
                ['min' => 51, 'max' => 100, 'label' => 'Sempurna']
            ]
        ]
    ]);

    // 3. Create fields: 1 short text, 1 radio with scores
    $textField = FormField::create([
        'form_id' => $form->id,
        'field_key' => 'fullname',
        'type' => 'short_text',
        'label' => 'Full Name',
        'is_required' => true,
        'sort_order' => 1,
    ]);

    $radioField = FormField::create([
        'form_id' => $form->id,
        'field_key' => 'satisfaction',
        'type' => 'radio',
        'label' => 'Satisfaction Level',
        'is_required' => true,
        'sort_order' => 2,
        'config' => ['weight' => 2.0]
    ]);

    $opt1 = FormFieldOption::create([
        'field_id' => $radioField->id,
        'value' => 'sangat_baik',
        'label' => 'Sangat Baik',
        'score' => 50,
        'sort_order' => 1,
    ]);

    $opt2 = FormFieldOption::create([
        'field_id' => $radioField->id,
        'value' => 'kurang',
        'label' => 'Kurang',
        'score' => 10,
        'sort_order' => 2,
    ]);

    // 4. Submit payload
    $payload = [
        'fullname' => 'John Doe',
        'satisfaction' => 'sangat_baik',
        '_duration' => 45,
    ];

    $service = app(FormSubmissionService::class);
    $response = $service->submit($form, $payload, '127.0.0.1', 'Mozilla/5.0');

    // 5. Assertions
    expect($response)->not->toBeNull();
    
    // Check Snapshot
    expect($response->answers_snapshot)->toHaveKey('fullname', 'John Doe');
    expect($response->answers_snapshot)->toHaveKey('satisfaction', 'sangat_baik');
    
    // Check Scores (sangat_baik is 50, weight is 2.0 -> score should be 100)
    expect((float) $response->score)->toBe(100.0);
    expect($response->grade)->toBe('Sempurna');
    expect($response->duration_seconds)->toBe(45);

    // Check individual relational answers (dual-layer storage)
    $this->assertDatabaseHas('response_answers', [
        'response_id' => $response->id,
        'field_key' => 'fullname',
        'value_text' => 'John Doe'
    ]);

    $this->assertDatabaseHas('response_answers', [
        'response_id' => $response->id,
        'field_key' => 'satisfaction',
        'value_text' => 'sangat_baik'
    ]);
});
