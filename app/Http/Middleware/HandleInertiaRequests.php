<?php

namespace App\Http\Middleware;

use App\Models\NavigationItem;
use Illuminate\Cache\DatabaseStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\MessageBag;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth.user' => function (Request $request) {
                $user = $request->user();
                if ($user) {
                    return [
                        'id' => $user->id,
                        'roles' => $user->getRoleNames(),
                        'permissions' => $user->getAllPermissions()->pluck('name'),
                        'hasProctorTests' => $user->proctorTests()->count(),
                    ];
                }

                return null;
            },
            'navigationItems' => function () {
                $cacheKey = NavigationItem::CACHE_KEY;
                if (Cache::store('redis')->has($cacheKey)) {
                    return Cache::store('redis')->get($cacheKey);
                }
                /** @var DatabaseStore $dbStore */
                $dbStore = Cache::store('database');

                return $dbStore->lock($cacheKey.'_db_lock', 10)->block(
                    5, function () use ($cacheKey) {
                        if (Cache::store('redis')->has($cacheKey)) {
                            return Cache::store('redis')->get($cacheKey);
                        }
                        $latestItems = NavigationItem::orderBy('display_order')
                            ->get(['id', 'master_id', 'name', 'url'])
                            ->toArray();
                        try {
                            Cache::store('redis')->forever($cacheKey, $latestItems);
                        } catch (\Throwable $e) {
                            report($e);
                        }

                        return $latestItems;
                    }
                );
            },
            'flash' => [
                'success' => session('success'),
                'error' => session('errors', new MessageBag)->first('message') ?? null,
            ],
        ];
    }
}
