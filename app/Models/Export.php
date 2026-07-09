<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Export extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'user_id',
        'format',
        'filters',
        'status', // pending | processing | done | failed
        'file_path',
        'row_count',
        'expires_at',
        'completed_at',
    ];

    protected $casts = [
        'filters' => 'array',
        'row_count' => 'integer',
        'expires_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
