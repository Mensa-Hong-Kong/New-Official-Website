<?php

namespace App\Schedules;

use App\Models\ContactHasVerification;

class ClearClosedOverOneDayNonVerifiedCode
{
    public function __invoke() {
        ContactHasVerification::where('closed_at', '<=', now()->subDay())
            ->whereNull('verified_at')
            ->delete();
    }
}
