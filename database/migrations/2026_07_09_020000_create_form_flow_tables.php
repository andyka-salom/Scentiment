<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. FORMS
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('slug', 100)->unique();
            $table->string('status', 20)->default('draft'); // draft | published | closed | archived
            $table->string('access_type', 20)->default('public'); // public | internal | token
            $table->boolean('is_assessment')->default(false);
            $table->jsonb('settings')->default('{}');
            $table->integer('current_version')->default(1);
            $table->timestampTz('opens_at')->nullable();
            $table->timestampTz('closes_at')->nullable();
            $table->timestampTz('published_at')->nullable();
            $table->softDeletesTz();
            $table->timestampsTz();

            // Indexes
            $table->index(['status', 'deleted_at'], 'idx_forms_status_active');
            $table->index('user_id', 'idx_forms_user');
        });

        // 2. FORM VERSIONS
        Schema::create('form_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->integer('version');
            $table->jsonb('schema'); // snapshot fields + options + logic
            $table->timestampsTz();

            $table->unique(['form_id', 'version']);
        });

        // 3. FORM FIELDS
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('field_key', 100);
            $table->string('type', 30); // short_text | radio | scale | ...
            $table->string('label', 500);
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('sort_order')->default(0);
            $table->jsonb('config')->default('{}');
            $table->jsonb('logic')->nullable();
            $table->softDeletesTz();
            $table->timestampsTz();

            $table->unique(['form_id', 'field_key']);
            $table->index(['form_id', 'sort_order', 'deleted_at'], 'idx_fields_form_active');
        });

        // 4. FORM FIELD OPTIONS
        Schema::create('form_field_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained('form_fields')->cascadeOnDelete();
            $table->string('value', 255);
            $table->string('label', 500);
            $table->decimal('score', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestampsTz();

            $table->index(['field_id', 'sort_order'], 'idx_options_field');
        });

        // 5. RESPONSES
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->integer('form_version')->default(1);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // NULL for public
            $table->string('status', 20)->default('complete'); // draft | complete
            $table->jsonb('answers_snapshot')->default('{}'); // {field_key: value}
            $table->decimal('score', 10, 2)->nullable();
            $table->jsonb('score_breakdown')->nullable();
            $table->string('grade', 50)->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->string('resume_token', 64)->nullable()->unique();
            $table->boolean('is_flagged')->default(false);
            $table->text('internal_note')->nullable();
            $table->timestampTz('submitted_at')->nullable();
            $table->timestampsTz();

            $table->index(['form_id', 'submitted_at'], 'idx_responses_form');
            $table->index(['form_id', 'user_id'], 'idx_responses_form_user');
            
            // GIN index for answers_snapshot in PostgreSQL
            // SQLite does not support GIN indexes, so we skip it if driver is sqlite
            if (config('database.default') === 'pgsql') {
                $table->index('answers_snapshot', 'idx_responses_answers_gin', 'gin');
            }
        });

        // 6. RESPONSE ANSWERS
        Schema::create('response_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('form_fields');
            $table->string('field_key', 100);
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 14, 4)->nullable();
            $table->jsonb('value_json')->nullable();
            $table->timestampsTz();

            $table->index(['form_id', 'field_id'], 'idx_answers_agg');
            $table->index(['field_id', 'value_number'], 'idx_answers_num');
            $table->index('response_id', 'idx_answers_resp');
        });

        // 7. RESPONSE FILES
        Schema::create('response_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained()->cascadeOnDelete();
            $table->foreignId('field_id')->constrained('form_fields');
            $table->string('original_name', 255);
            $table->string('path', 500);
            $table->string('mime_type', 100)->nullable();
            $table->bigInteger('size_bytes')->nullable();
            $table->timestampsTz();
        });

        // 8. FORM SHARES
        Schema::create('form_shares', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('role_name', 100)->nullable();
            $table->string('level', 20); // viewer | editor
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestampsTz();
        });

        // 9. EXPORTS
        Schema::create('exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('format', 10); // xlsx | csv
            $table->jsonb('filters')->nullable();
            $table->string('status', 20)->default('pending'); // pending | processing | done | failed
            $table->string('file_path', 500)->nullable();
            $table->integer('row_count')->nullable();
            $table->timestampTz('expires_at')->nullable();
            $table->timestampTz('completed_at')->nullable();
            $table->timestampsTz();
        });

        // 10. AUDIT LOGS
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50); // form.created, response.deleted, export.run, ...
            $table->string('subject_type', 100);
            $table->unsignedBigInteger('subject_id');
            $table->jsonb('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->timestampsTz();

            $table->index(['subject_type', 'subject_id'], 'idx_audit_subject');
            $table->index('created_at', 'idx_audit_created');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('exports');
        Schema::dropIfExists('form_shares');
        Schema::dropIfExists('response_files');
        Schema::dropIfExists('response_answers');
        Schema::dropIfExists('responses');
        Schema::dropIfExists('form_field_options');
        Schema::dropIfExists('form_fields');
        Schema::dropIfExists('form_versions');
        Schema::dropIfExists('forms');
    }
};
