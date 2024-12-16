<?php

namespace App\Schedules;

use App\Models\ContactHasVerification;

class ClearUnusedVerifiyCode
{
    public function __invoke()
    {
        ContactHasVerification::whereNull('verified_at')
            ->where('closed_at', '<', now()->subMonths(3))
            ->delete();
    }
}
