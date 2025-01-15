<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Team;
use App\Models\TeamRole;
use App\Models\TeamType;
use Illuminate\Database\Seeder;

class TeamRoleSeeder extends Seeder
{
    public function run(): void
    {
        TeamRole::firstOrCreate(['name' => 'Super Administrator']);

        // Board
        $type = TeamType::firstOrCreate(['name' => 'Board']);
        $team = Team::firstOrCreate([
            'name' => 'Board of Directors',
            'type_id' => $type->id,
        ]);
        $roles = [];
        $roles[] = Role::firstOrCreate(['name' => 'Chairperson']);
        $roles[] = Role::firstOrCreate(['name' => 'Vice-chairperson']);
        $roles[] = Role::firstOrCreate(['name' => 'Honorary Secretary']);
        $roles[] = Role::firstOrCreate(['name' => 'Honorary Treasurer']);
        $roles[] = Role::firstOrCreate(['name' => 'Director']);
        foreach ($roles as $role) {
            TeamRole::firstOrCreate([
                'name' => "{$team->name}:{$role->name}",
                'team_id' => $team->id,
                'role_id' => $role->id,
            ]);
        }

        // Committees
        $type = TeamType::firstOrCreate(['name' => 'Committee']);
        $teams = [];
        $teams[] = Team::firstOrCreate([
            'name' => 'Event Organizing Committee',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => 'Branding and Communication Committee',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => 'Internal Publications Committee',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => 'Information Technology Committee',
            'type_id' => $type->id,
        ]);
        $roles = [];
        $roles[] = Role::firstOrCreate(['name' => 'Chairperson']);
        $roles[] = Role::firstOrCreate(['name' => 'Member']);
        foreach ($teams as $team) {
            foreach ($roles as $role) {
                TeamRole::firstOrCreate([
                    'name' => "{$team->name}:{$role->name}",
                    'team_id' => $team->id,
                    'role_id' => $role->id,
                ]);
            }
        }

        // Special Interest Groups
        $teams = [];
        $type = TeamType::firstOrCreate(['name' => 'Special Interest Group']);
        $teams[] = Team::firstOrCreate([
            'name' => 'Chess SIG',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => 'Classical Music SIG',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => 'Dream SIG',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => 'Entrepreneur Development Group SIG 企業家發展學會',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => 'LARP & TRPG SIG 劇本殺 & 桌上角色扮演遊戲',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => 'MBN Business Networking SIG',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => 'Mensa Mind Sports Competition and Strategy Study and Discussion Group',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => 'Neurohacking SIG',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => '神秘學會 SIG (Mystic SIG)',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => 'Poker SIG 啤牌聚會',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => 'Sofiesta 高明薈',
            'type_id' => $type->id,
        ]);
        $teams[] = Team::firstOrCreate([
            'name' => '🍁Wildlife Go! SIG🐾🏔 行山SIG',
            'type_id' => $type->id,
        ]);
        $roles = [];
        $roles[] = Role::firstOrCreate(['name' => 'Convenor']);
        $roles[] = Role::firstOrCreate(['name' => 'Deputy Convenor']);
        foreach ($teams as $team) {
            foreach ($roles as $role) {
                TeamRole::firstOrCreate([
                    'name' => "{$team->name}:{$role->name}",
                    'team_id' => $team->id,
                    'role_id' => $role->id,
                ]);
            }
        }
    }
}
