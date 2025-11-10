<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Team;
use App\Models\TeamRole;
use App\Models\TeamType;
use Illuminate\Database\Seeder;

/**
 * This seeder populated the tables, namely roles, teams, team_roles, and team_types with predefined data.
 * 
 * The 'roles' table will contain:
 * | id  | name               | created_at | updated_at |
 * | --- | ------------------ | ---------- | ---------- |
 * | 1   | Chairperson        | ...        | ...        |
 * | 2   | Vice-chairperson   | ...        | ...        |
 * | 3   | Honorary Secretary | ...        | ...        |
 * | 4   | Honorary Treasurer | ...        | ...        |
 * | 5   | Director           | ...        | ...        |
 * | 6   | Member             | ...        | ...        |
 * | 7   | Convenor           | ...        | ...        |
 * | 8   | Deputy Convenor    | ...        | ...        |
 * 
 * The 'teams' table will contain:
 * | id  | name                                                                  | type_id | display_order | created_at | updated_at |
 * | --- | --------------------------------------------------------------------- | ------- | ------------- | ---------- | ---------- |
 * | 1   | Board of Directors                                                    | 1       | 0             | ...        | ...        |
 * | 2   | Event Organizing Committee                                            | 2       | 0             | ...        | ...        |
 * | 3   | Branding and Communication Committee                                  | 2       | 1             | ...        | ...        |
 * | 4   | Internal Publications Committee                                       | 2       | 2             | ...        | ...        |
 * | 5   | Information Technology Committee                                      | 2       | 3             | ...        | ...        |
 * | 6   | Chess SIG                                                             | 3       | 0             | ...        | ...        |
 * | 7   | Classical Music SIG                                                   | 3       | 1             | ...        | ...        |
 * | 8   | Dream SIG                                                             | 3       | 2             | ...        | ...        |
 * | 9   | Entrepreneur Development Group SIG 企業家發展學會                     | 3       | 3             | ...        | ...        |
 * | 10  | LARP & TRPG SIG 劇本殺 & 桌上角色扮演遊戲                             | 3       | 4             | ...        | ...        |
 * | 11  | MBN Business Networking SIG                                           | 3       | 5             | ...        | ...        |
 * | 12  | Mensa Mind Sports Competition and Strategy Study and Discussion Group | 3       | 6             | ...        | ...        |
 * | 13  | Neurohacking SIG                                                      | 3       | 7             | ...        | ...        |
 * | 14  | 神秘學會 SIG (Mystic SIG)                                             | 3       | 8             | ...        | ...        |
 * | 15  | Poker SIG 啤牌聚會                                                    | 3       | 9             | ...        | ...        |
 * | 16  | Sofiesta 高明薈                                                       | 3       | 10            | ...        | ...        |
 * | 17  | 🍁Wildlife Go! SIG🐾🏔 行山 SIG                                        | 3       | 11            | ...        | ...        |
 * 
 * The 'team_roles' table will contain:
 * | id  | name                                                                                                         | team_id | role_id | display_order | guard_name | created_at | updated_at |
 * | --- | ------------------------------------------------------------------------------------------------------------ | ------- | ------- | ------------- | ---------- | ---------- | ---------- |
 * | 1   | Super Administrator                                                                                          | NULL    | NULL    | 0             | web        | ...        | ...        |
 * | 2   | Board:Board of Directors:Chairperson                                                                         | 1       | 1       | 0             | web        | NULL       | NULL       |
 * | 3   | Board:Board of Directors:Vice-chairperson                                                                    | 1       | 2       | 1             | web        | NULL       | NULL       |
 * | 4   | Board:Board of Directors:Honorary Secretary                                                                  | 1       | 3       | 2             | web        | NULL       | NULL       |
 * | 5   | Board:Board of Directors:Honorary Treasurer                                                                  | 1       | 4       | 3             | web        | NULL       | NULL       |
 * | 6   | Board:Board of Directors:Director                                                                            | 1       | 5       | 4             | web        | NULL       | NULL       |
 * | 7   | Committee:Event Organizing Committee:Chairperson                                                             | 2       | 1       | 0             | web        | NULL       | NULL       |
 * | 8   | Committee:Event Organizing Committee:Member                                                                  | 2       | 6       | 1             | web        | NULL       | NULL       |
 * | 9   | Committee:Branding and Communication Committee:Chairperson                                                   | 3       | 1       | 0             | web        | NULL       | NULL       |
 * | 10  | Committee:Branding and Communication Committee:Member                                                        | 3       | 6       | 1             | web        | NULL       | NULL       |
 * | 11  | Committee:Internal Publications Committee:Chairperson                                                        | 4       | 1       | 0             | web        | NULL       | NULL       |
 * | 12  | Committee:Internal Publications Committee:Member                                                             | 4       | 6       | 1             | web        | NULL       | NULL       |
 * | 13  | Committee:Information Technology Committee:Chairperson                                                       | 5       | 1       | 0             | web        | NULL       | NULL       |
 * | 14  | Committee:Information Technology Committee:Member                                                            | 5       | 6       | 1             | web        | NULL       | NULL       |
 * | 15  | Special Interest Group:Chess SIG:Convenor                                                                    | 6       | 7       | 0             | web        | NULL       | NULL       |
 * | 16  | Special Interest Group:Chess SIG:Deputy Convenor                                                             | 6       | 8       | 1             | web        | NULL       | NULL       |
 * | 17  | Special Interest Group:Classical Music SIG:Convenor                                                          | 7       | 7       | 0             | web        | NULL       | NULL       |
 * | 18  | Special Interest Group:Classical Music SIG:Deputy Convenor                                                   | 7       | 8       | 1             | web        | NULL       | NULL       |
 * | 19  | Special Interest Group:Dream SIG:Convenor                                                                    | 8       | 7       | 0             | web        | NULL       | NULL       |
 * | 20  | Special Interest Group:Dream SIG:Deputy Convenor                                                             | 8       | 8       | 1             | web        | NULL       | NULL       |
 * | 21  | Special Interest Group:Entrepreneur Development Group SIG 企業家發展學會:Convenor                            | 9       | 7       | 0             | web        | NULL       | NULL       |
 * | 22  | Special Interest Group:Entrepreneur Development Group SIG 企業家發展學會:Deputy Convenor                     | 9       | 8       | 1             | web        | NULL       | NULL       |
 * | 23  | Special Interest Group:LARP & TRPG SIG 劇本殺 & 桌上角色扮演遊戲:Convenor                                    | 10      | 7       | 0             | web        | NULL       | NULL       |
 * | 24  | Special Interest Group:LARP & TRPG SIG 劇本殺 & 桌上角色扮演遊戲:Deputy Convenor                             | 10      | 8       | 1             | web        | NULL       | NULL       |
 * | 25  | Special Interest Group:MBN Business Networking SIG:Convenor                                                  | 11      | 7       | 0             | web        | NULL       | NULL       |
 * | 26  | Special Interest Group:MBN Business Networking SIG:Deputy Convenor                                           | 11      | 8       | 1             | web        | NULL       | NULL       |
 * | 27  | Special Interest Group:Mensa Mind Sports Competition and Strategy Study and Discussion Group:Convenor        | 12      | 7       | 0             | web        | NULL       | NULL       |
 * | 28  | Special Interest Group:Mensa Mind Sports Competition and Strategy Study and Discussion Group:Deputy Convenor | 12      | 8       | 1             | web        | NULL       | NULL       |
 * | 29  | Special Interest Group:Neurohacking SIG:Convenor                                                             | 13      | 7       | 0             | web        | NULL       | NULL       |
 * | 30  | Special Interest Group:Neurohacking SIG:Deputy Convenor                                                      | 13      | 8       | 1             | web        | NULL       | NULL       |
 * | 31  | Special Interest Group:神秘學會 SIG (Mystic SIG):Convenor                                                    | 14      | 7       | 0             | web        | NULL       | NULL       |
 * | 32  | Special Interest Group:神秘學會 SIG (Mystic SIG):Deputy Convenor                                             | 14      | 8       | 1             | web        | NULL       | NULL       |
 * | 33  | Special Interest Group:Poker SIG 啤牌聚會:Convenor                                                           | 15      | 7       | 0             | web        | NULL       | NULL       |
 * | 34  | Special Interest Group:Poker SIG 啤牌聚會:Deputy Convenor                                                    | 15      | 8       | 1             | web        | NULL       | NULL       |
 * | 35  | Special Interest Group:Sofiesta 高明薈:Convenor                                                              | 16      | 7       | 0             | web        | NULL       | NULL       |
 * | 36  | Special Interest Group:Sofiesta 高明薈:Deputy Convenor                                                       | 16      | 8       | 1             | web        | NULL       | NULL       |
 * | 37  | Special Interest Group:🍁Wildlife Go! SIG🐾🏔 行山 SIG:Convenor                                               | 17      | 7       | 0             | web        | NULL       | NULL       |
 * | 38  | Special Interest Group:🍁Wildlife Go! SIG🐾🏔 行山 SIG:Deputy Convenor                                        | 17      | 8       | 1             | web        | NULL       | NULL       |
 * 
 * The 'team_types' table will contain: 
 * | id  | name                   | title | display_order | created_at | updated_at |
 * | --- | ---------------------- | ----- | ------------- | ---------- | ---------- |
 * | 1   | Board                  | NULL  | 0             | ...        | ...        |
 * | 2   | Committee              | NULL  | 1             | ...        | ...        |
 * | 3   | Special Interest Group | NULL  | 2             | ...        | ...        |
*/
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
        $sync = [];
        foreach ($roles as $index => $role) {
            $sync[$role->id] = [
                'name' => "{$type->name}:{$team->name}:{$role->name}",
                'display_order' => $index,
            ];
        }
        $team->roles()->sync($sync);

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
        $sync = [];
        foreach ($teams as $teamIndex => $team) {
            $team->update(['display_order' => $teamIndex]);
            foreach ($roles as $roleIndex => $role) {
                $sync[$role->id] = [
                    'name' => "{$type->name}:{$team->name}:{$role->name}",
                    'display_order' => $roleIndex,
                ];
            }
            $team->roles()->sync($sync);
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
        $sync = [];
        foreach ($teams as $teamIndex => $team) {
            $team->update(['display_order' => $teamIndex]);
            foreach ($roles as $roleIndex => $role) {
                $sync[$role->id] = [
                    'name' => "{$type->name}:{$team->name}:{$role->name}",
                    'display_order' => $roleIndex,
                ];
            }
            $team->roles()->sync($sync);
        }
    }
}
