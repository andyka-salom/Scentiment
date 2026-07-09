<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'form_id',
        'form_version',
        'user_id',
        'status',
        'answers_snapshot',
        'score',
        'score_breakdown',
        'grade',
        'duration_seconds',
        'ip_hash',
        'user_agent',
        'resume_token',
        'is_flagged',
        'internal_note',
        'submitted_at',
    ];

    protected $casts = [
        'form_version' => 'integer',
        'answers_snapshot' => 'array',
        'score' => 'float',
        'score_breakdown' => 'array',
        'duration_seconds' => 'integer',
        'is_flagged' => 'boolean',
        'submitted_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($response) {
            if (empty($response->uuid)) {
                $response->uuid = (string) Str::uuid();
            }
        });
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ResponseAnswer::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(ResponseFile::class);
    }
}
