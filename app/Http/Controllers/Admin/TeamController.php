<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Team\FormRequest;
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
            ->get(['id', 'name']);
        $displayOptions = [];
        foreach ($types as $type) {
            $displayOptions[$type->id] = [];
            foreach ($type->teams as $team) {
                $displayOptions[$type->id][$team->display_order] = "before \"$team->name\"";
            }
            $displayOptions[$type->id][0] = 'top';
            $displayOptions[$type->id][max(array_keys($displayOptions[$type->id])) + 1] = 'latest';
        }
        $types = $types->pluck('name', 'id')
            ->toArray();

        return view('admin.teams.create')
            ->with('types', $types)
            ->with('displayOptions', $displayOptions);
    }

    public function store(FormRequest $request)
    {
        DB::beginTransaction();
        Team::where('type_id', $request->type_id)
            ->where('display_order', '>=', $request->display_order)
            ->increment('display_order');
        $team = Team::create([
            'name' => $request->name,
            'type_id' => $request->type_id,
            'display_order' => $request->display_order,
        ]);
        DB::commit();

        return redirect()->route('admin.teams.show', ['team' => $team]);
    }

    public function show(Team $team)
    {
        return view('admin.teams.show')
            ->with(
                'team', $team->load([
                    'roles' => function ($query) {
                        $query->orderBy('pivot_display_order')
                            ->orderBy('id');
                    },
                ])
            );
    }

    public function edit(Team $team)
    {
        $types = TeamType::with([
            'teams' => function ($query) {
                $query->orderBy('display_order')
                    ->orderBy('id');
            },
        ])->orderBy('display_order')
            ->orderBy('id')
            ->get(['id', 'name']);
        $displayOptions = [];
        foreach ($types as $type) {
            $displayOptions[$type->id] = [];
            foreach ($type->teams as $thisTeam) {
                if (
                    $type->id != $team->type_id ||
                    $thisTeam->id != $team->id
                ) {
                    $displayOptions[$type->id][$thisTeam->display_order] = "before \"$team->name\"";
                }
            }
            $displayOptions[$type->id][0] = 'top';
            $displayOptions[$type->id][max(array_keys($displayOptions[$type->id])) + 1] = 'latest';
        }
        $types = $types->pluck('name', 'id')
            ->toArray();

        return view('admin.teams.edit')
            ->with('types', $types)
            ->with('displayOptions', $displayOptions)
            ->with('team', $team);
    }

    public function update(FormRequest $request, Team $team)
    {
        DB::beginTransaction();
        Team::where('type_id', $team->type_id)
            ->where('display_order', '>', $team->display_order)
            ->decrement('display_order');
        Team::where('type_id', $request->type_id)
            ->where('display_order', '>=', $request->display_order)
            ->whereNot('id', $team->id)
            ->increment('display_order');
        $team->update([
            'name' => $request->name,
            'type_id' => $request->type_id,
            'display_order' => $request->display_order,
        ]);
        $sync = [];
        foreach ($team->roles as $role) {
            $sync[$role->id] = ['name' => "{$team->type->name}:{$team->name}:$role->name"];
        }
        $team->roles()->sync($sync);
        DB::commit();

        return redirect()->route('admin.teams.show', ['team' => $team]);
    }
}
