<?php

namespace App\Notifications\AdmissionTest\Admin;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class RemovedAdmissionTestRecordByQueue extends RemovedAdmissionTestRecord implements ShouldQueue
{
    use Queueable;
}
