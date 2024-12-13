<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Verification extends Model
{
    use HasFactory;

    protected $fillable = [
        'verifiable_type',
        'verifiable_id',
        'tried_time',
        'code',
        'closed_at',
        'verified_at',
        'expired_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'verified_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function verifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
