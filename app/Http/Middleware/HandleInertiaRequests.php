<?php

namespace App\Http\Middleware;

use App\Models\NavigationItem;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
        $navigationItems = NavigationItem::orderBy('display_order')
            ->get(['id', 'master_id', 'name', 'url'])
            ->keyBy('id');
        $navigationNodes = array_fill_keys($navigationItems->pluck('id')->toArray(), []);
        $navigationNodes['root'] = [];
        foreach ($navigationItems as $item) {
            $navigationNodes[$item->master_id ?? 'root'][] = $item->id;
        }

        return [
            ...parent::share($request),
            'csrf_token' => csrf_token(),
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
            'ziggy' => new Ziggy,
            'navigationItems' => $navigationItems,
            'navigationNodes' => $navigationNodes,
            'flash' => [
                'success' => session('success'),
                'error' => session('errors', new MessageBag)->first('message') ?? null,
            ],
        ];
    }
}
