<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class UserHasContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'contact',
        'is_default',
    ];

    public function verifications(): MorphMany
    {
        return $this->morphMany(Verification::class, 'verifiable');
    }

    public function lastVerification(): MorphOne
    {
        return $this->morphOne(Verification::class, 'verifiable')
            ->latest();
    }
}
