<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Team\StoreRequest;
use App\Models\Team;
use App\Models\TeamType;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [(new Middleware('permission:Edit:Permission'))->except('index')];
    }

    public function index()
    {
        return view('admin.teams.index')
            ->with(
                'types', TeamType::with([
                    'teams' => function ($query) {
                        $query->orderBy('display_order')
                            ->orderBy('id');
                    },
                ])->orderBy('display_order')
                    ->orderBy('id')
                    ->get()
            );
    }

    public function create()
    {
        $types = TeamType::with([
            'teams' => function ($query) {
                $query->orderBy('display_order')
                    ->orderBy('id');
            },
        ])->orderBy('display_order')
            ->orderBy('id')
            ->get();
        $displayOptions = [];
        foreach ($types as $type) {
            $displayOptions[$type->id] = [];
            foreach ($type->teams as $team) {
                $displayOptions[$type->id][$team->display_order] = "before \"$team->name\"";
            }
            $displayOptions[$type->id][0] = 'top';
            $displayOptions[$type->id][max(array_keys($displayOptions[$type->id])) + 1] = 'latest';
        }

        return view('admin.teams.create')
            ->with(
                'types', TeamType::orderBy('display_order')
                    ->orderBy('id')
                    ->get(['id', 'name'])
                    ->pluck('name', 'id')
                    ->toArray()
            )->with('displayOptions', $displayOptions);
    }

    public function store(StoreRequest $request)
    {
        DB::beginTransaction();
        Team::where('type_id', $request->type_id)
            ->where('display_order', '>=', $request->display_order)
            ->increment('display_order');
        Team::create([
            'name' => $request->name,
            'type_id' => $request->type_id,
            'display_order' => $request->display_order,
        ]);
        DB::commit();

        return redirect()->route('admin.teams.index');
    }
}
