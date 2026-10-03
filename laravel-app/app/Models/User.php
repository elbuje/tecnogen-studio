<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasUuids;

    protected $table = 'users';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'id',
        'email',
        'password',
        'full_name',
        'role',
        'plan_tier',
        'commercial_status',
        'plan_name',
        'plan_price_monthly',
        'monthly_video_limit',
        'videos_generated_this_month',
        'avatar_minutes_quota',
        'avatar_minutes_used',
        'auto_mode_enabled',
        'sheet_url',
        'sheet_auto_mode',
        'sheet_last_sync_at',
        'notes',
        'credits_balance',
        'google_oauth_token',
        'google_refresh_token',
        'stripe_customer_id',
        'mercadopago_payer_id',
        'plan_renewal_date',
    ];

    protected $hidden = [
        'password',
        'google_oauth_token',
        'google_refresh_token',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'auto_mode_enabled' => 'boolean',
            'sheet_last_sync_at' => 'datetime',
            'plan_renewal_date' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function brands()
    {
        return $this->hasMany(Brand::class, 'user_id');
    }

    public function creditLedger()
    {
        return $this->hasMany(CreditLedger::class, 'user_id');
    }
}
