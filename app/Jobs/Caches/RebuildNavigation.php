<?php

namespace App\Jobs\Caches;

use App\Models\NavigationItem;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

class RebuildNavigation implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public $uniqueFor = 300;

    public function __construct() {}

    public function backoff(): array
    {
        return [
            10,    // 1st failure: wait 10 seconds (handles transient network blips)
            60,    // 2nd failure: wait 1 minute
            300,   // 3rd failure: wait 5 minutes
            900,   // 4th failure: wait 15 minutes
            3600,  // 5th failure and beyond: sleep for 1 hour between retries during long outages
        ];
    }

    public function uniqueId(): string
    {
        return NavigationItem::CACHE_KEY;
    }

    public function handle(): void
    {
        $cacheKey = NavigationItem::CACHE_KEY;
        Cache::lock($cacheKey.'_lock', 10)->block(
            5, function () use ($cacheKey) {
                $latestItems = NavigationItem::orderBy('display_order')
                    ->get(['id', 'master_id', 'name', 'url'])
                    ->toArray();
                Cache::forever($cacheKey, $latestItems);
            }
        );
    }
}
