<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'response_id',
        'form_id',
        'field_id',
        'field_key',
        'value_text',
        'value_number',
        'value_json',
    ];

    protected $casts = [
        'value_number' => 'float',
        'value_json' => 'array',
    ];

    public function response(): BelongsTo
    {
        return $this->belongsTo(Response::class);
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class);
    }
}
