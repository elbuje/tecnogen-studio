<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CreditLedger extends Model
{
    use HasUuids;

    protected $table = 'credit_ledger';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'id',
        'user_id',
        'amount',
        'action_type',
        'reference_id',
        'description',
        'created_at',
    ];

    protected $casts = [
        'amount' => 'integer',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
