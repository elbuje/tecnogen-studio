<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class AISetting extends Model
{
    use HasUuids;

    protected $table = 'ai_settings';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'provider',
        'category',
        'model_name',
        'api_key_override',
        'is_active',
        'parameters',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'parameters' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
