<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Form extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_DRAFT = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_CLOSED = 'closed';
    const STATUS_ARCHIVED = 'archived';

    const ACCESS_PUBLIC = 'public';
    const ACCESS_INTERNAL = 'internal';
    const ACCESS_TOKEN = 'token';

    protected $fillable = [
        'uuid',
        'user_id',
        'title',
        'description',
        'slug',
        'status',
        'access_type',
        'is_assessment',
        'settings',
        'current_version',
        'opens_at',
        'closes_at',
        'published_at',
    ];

    protected $casts = [
        'is_assessment' => 'boolean',
        'settings' => 'array',
        'current_version' => 'integer',
        'opens_at' => 'datetime',
        'closes_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($form) {
            if (empty($form->uuid)) {
                $form->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(FormField::class)->orderBy('sort_order');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(FormVersion::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(Response::class);
    }

    public function shares(): HasMany
    {
        return $this->hasMany(FormShare::class);
    }
}
