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
        'code',
        'verified_at',
        'expired_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function verifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isTimeoutCode(): bool
    {
        return $this->created_at >= now()->subMinutes(5);
    }
}
