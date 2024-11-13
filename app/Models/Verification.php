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


    public function verifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
