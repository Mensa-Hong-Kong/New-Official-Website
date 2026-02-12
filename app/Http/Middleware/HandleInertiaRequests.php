<?php

namespace App\Http\Middleware;

use App\Models\NavigationItem;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    public function share(Request $request): array
    {
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
            'navigationItems' => NavigationItem::orderBy('display_order')
                ->get(['id', 'master_id', 'name', 'url']),
            'flash' => [
                'success' => session('success'),
                'error' => session('errors', new MessageBag)->first('message') ?? null,
            ],
        ];
    }
}
