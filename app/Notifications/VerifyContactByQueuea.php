<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class VerifyContactByQueuea extends VerifyContact implements ShouldQueue
{
    use Queueable;
}
