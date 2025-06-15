<?php

namespace App\Http\Middleware;

use App\Models\NavigationItem;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    public function navChildren($items, $masterID)
    {
        $return = [];
        foreach ($items as $id => $thisItem) {
            if ($thisItem['master_id'] == $masterID) {
                $item = [
                    'name' => $thisItem['name'],
                    'url' => $thisItem['url'],
                ];
                unset($items[$id]);
                $children = $this->navChildren($items, $id);
                $items = array_diff_key($items, $children);
                if (count($children)) {
                    $item['children'] = $children;
                }
                $return[$id] = $item;
            }
        }

        return $return;
    }

	public function share(Request $request): array
	{
		return [
			...parent::share($request),
            'csrf_token' => csrf_token(),
            'auth.user' => function(Request $request) {
                $user = $request->user();
                if($user) {
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
            'nav' => $this->navChildren(
                NavigationItem::orderBy('display_order')
                    ->get(['id', 'master_id', 'name', 'url'])
                    ->keyBy('id')
                    ->toArray(),
                null
            ),
            'flash' => function () {
                return [
                    'success' => session('success'),
                    'error' => session('error'),
                ];
            },
		];
	}
}
