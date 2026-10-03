<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Slide extends Model
{
    use HasUuids;

    protected $table = 'slides';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'content_id',
        'slide_number',
        'slide_type',
        'image_url',
        'gdrive_file_id',
        'headline',
        'body_text',
        'badge',
        'prompt_used',
        'feedback',
        'status',
        'version',
        'created_at',
    ];

    protected $casts = [
        'slide_number' => 'integer',
        'version' => 'integer',
        'created_at' => 'datetime',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id');
    }
}
