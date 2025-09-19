<?php

namespace App\Abstracts\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;

abstract class Order implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    protected string $model;

    public function __construct(
        protected int $modelID
    ) {}

    public function middleware(): array
    {
        return [
            new WithoutOverlapping($this->modelID),
        ];
    }
}
