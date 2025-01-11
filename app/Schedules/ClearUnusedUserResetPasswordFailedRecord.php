<?php

namespace App\Schedules;

use App\Models\ResetPasswordLog;

class ClearUnusedUserResetPasswordFailedRecord
{
    public function __invoke()
    {
        ResetPasswordLog::whereNull('user_id')
            ->where('created_at', '<', now()->subDay())
            ->delete();
    }
}
