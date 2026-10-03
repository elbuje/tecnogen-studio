<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    use HasUuids;

    protected $table = 'contents';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'brand_id',
        'type',
        'title',
        'hook_text',
        'caption_copy',
        'hashtags',
        'status',
        'source',
        'sheet_row_ref',
        'total_slides',
        'metricool_post_id',
        'scheduled_for',
        'version',
    ];

    protected $casts = [
        'scheduled_for' => 'datetime',
        'total_slides' => 'integer',
        'version' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id');
    }

    public function slides()
    {
        return $this->hasMany(Slide::class, 'content_id')->orderBy('slide_number');
    }
}
