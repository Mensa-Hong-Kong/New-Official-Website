<?php

namespace App\Library\Stripe\Abstracts\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\ThrottlesExceptions;
use Illuminate\Queue\Middleware\WithoutOverlapping;

abstract class Base implements ShouldQueue
{
    use InteractsWithQueue, Queueable;

    protected string $model;

    public function __construct(
        protected int $modelID
    ) {}

    public function middleware(): array
    {
        return [
            new ThrottlesExceptions(10, 5 * 60),
            new WithoutOverlapping($this->modelID),
        ];
    }

    abstract public function handle(): void;
}
