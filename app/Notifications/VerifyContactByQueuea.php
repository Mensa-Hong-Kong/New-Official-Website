<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class QueueableVerifyContact extends VerifyContact implements ShouldQueue
{
    use Queueable;
}
