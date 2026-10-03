<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasUuids;

    protected $table = 'brands';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false; // SQLAlchemy created_at only or custom timestamps

    protected $fillable = [
        'id',
        'user_id',
        'name',
        'primary_color',
        'accent_color',
        'bg_color',
        'font_style_title',
        'font_style_body',
        'layout_preset',
        'logo_position',
        'logo_width_px',
        'gdrive_logos_folder_id',
        'gdrive_subjects_folder_id',
        'gdrive_brand_manual_folder_id',
        'gdrive_templates_folder_id',
        'gdrive_products_folder_id',
        'gdrive_input_folder_id',
        'gdrive_output_folder_id',
        'sheets_url',
        'metricool_user_token',
        'metricool_blog_id',
        'brand_rules',
        'created_at',
    ];

    protected $casts = [
        'brand_rules' => 'array',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assets()
    {
        return $this->hasMany(BrandAsset::class, 'brand_id');
    }

    public function contents()
    {
        return $this->hasMany(Content::class, 'brand_id');
    }
}
