<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseFile extends Model
{
    use HasFactory;

    protected $fillable = [
        'response_id',
        'field_id',
        'original_name',
        'path',
        'mime_type',
        'size_bytes',
    ];

    protected $casts = [
        'size_bytes' => 'integer',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class);
    }
}
