<?php

namespace App\Schedules;

use App\Models\ContactHasVerification;

class ClearUnusedAdminVerifiyRecode
{
    public function __invoke()
    {
        ContactHasVerification::whereNull('code')
            ->where('expired_at', '<=', now())
            ->delete();
    }
}