<?php

namespace App\Concern\Models;

use App\Models\Verification;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait Verifiable
{
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
