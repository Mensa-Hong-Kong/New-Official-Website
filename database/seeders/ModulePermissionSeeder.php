<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Seeder;

/**
 * This seeder populates the modules table with predefined data as follows. The script update the tables (e.g., modules, permissions, module_permission)
 *
 * The 'permissions' table will contain:
 * | id  | name | title | display_order | created_at | updated_at |
 * | --- | ---- | ----- | ------------- | ---------- | ---------- |
 * | 1   | View | NULL  | 0             | ...        | ...        |
 * | 2   | Edit | NULL  | 1             | ...        | ...        |
 *
 * The 'modules' table will contain:
 * | id  | master_id | name                     | title     | display_order | created_at | updated_at |
 * | --- | ----------| ------------------------ | --------- | ------------- | ---------- | ---------- |
 * | 1   | NULL      | User                     | NULL      | 1             | ...        | ...        |
 * | 2   | NULL      | Permission               | NULL      | 2             | ...        | ...        |
 * | 3   | NULL      | Admission Test           | NULL      | 3             | ...        | ...        |
 * | 4   | NULL      | Admission Test Order     | NULL      | 4             | ...        | ...        |
 * | 5   | NULL      | Site Content             | NULL      | 5             | ...        | ...        |
 * | 6   | NULL      | Custom Web Page          | NULL      | 6             | ...        | ...        |
 * | 7   | NULL      | Navigation Item          | NULL      | 7             | ...        | ...        |
 * | 8   | NULL      | Other Payment Gateway    | NULL      | 8             | ...        | ...        |
 * | 9   | 3         | Admission Test Proctor   | Proctor   | 1             | ...        | ...        |
 * | 10  | 3         | Admission Test Candidate | Candidate | 2             | ...        | ...        |
 * | 11  | 3         | Admission Test Result    | Result    | 3             | ...        | ...        |
 *
 * The 'module_permissions' table will contain:
 * | id  | name                          | module_id | permission_id | guard_name | created_at | updated_at |
 * | --- | ----------------------------- | --------- | ------------- | ---------- | ---------- | ---------- |
 * | 1   | View:User                     | 1         | 1             | web        | ...        | ...        |
 * | 2   | Edit:User                     | 1         | 2             | web        | ...        | ...        |
 * | 3   | Edit:Permission               | 2         | 2             | web        | ...        | ...        |
 * | 4   | Edit:Admission Test           | 3         | 2             | web        | ...        | ...        |
 * | 5   | View:Admission Test Order     | 4         | 1             | web        | ...        | ...        |
 * | 6   | Edit:Admission Test Order     | 4         | 2             | web        | ...        | ...        |
 * | 7   | Edit:Site Content             | 5         | 2             | web        | ...        | ...        |
 * | 8   | Edit:Custom Web Page          | 6         | 2             | web        | ...        | ...        |
 * | 9   | Edit:Navigation Item          | 7         | 2             | web        | ...        | ...        |
 * | 10  | Edit:Other Payment Gateway    | 8         | 2             | web        | ...        | ...        |
 * | 11  | Edit:Admission Test Proctor   | 8         | 2             | web        | ...        | ...        |
 * | 11  | View:Admission Test Candidate | 8         | 2             | web        | ...        | ...        |
 * | 11  | Edit:Admission Test Candidate | 8         | 2             | web        | ...        | ...        |
 * | 11  | View:Admission Test Result    | 8         | 2             | web        | ...        | ...        |
 * | 11  | Edit:Admission Test Result    | 8         | 2             | web        | ...        | ...        |
 */
class ModulePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $viewPermission = Permission::firstOrCreate(
            ['name' => 'View'],
            ['display_order' => 0]
        );
        $editPermission = Permission::firstOrCreate(
            ['name' => 'Edit'],
            ['display_order' => 1]
        );

        $module = Module::firstOrCreate(
            ['name' => 'User'],
            ['display_order' => 1]
        );
        $module->permissions()->sync([
            $viewPermission->id => ['name' => "{$viewPermission->name}:{$module->name}"],
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(
            ['name' => 'Permission'],
            ['display_order' => 2]
        );
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(
            ['name' => 'Admission Test'],
            ['display_order' => 3]
        );
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(
            ['name' => 'Admission Test Order'],
            ['display_order' => 4]
        );
        $module->permissions()->sync([
            $viewPermission->id => ['name' => "{$viewPermission->name}:{$module->name}"],
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(
            ['name' => 'Site Content'],
            ['display_order' => 5]
        );
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(
            ['name' => 'Custom Web Page'],
            ['display_order' => 6]
        );
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(
            ['name' => 'Navigation Item'],
            ['display_order' => 7]
        );
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(
            ['name' => 'Other Payment Gateway'],
            ['display_order' => 8]
        );
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(
            [
                'name' => 'Admission Test Proctor',
                'title' => 'Proctor',
                'master_id' => 3,
            ],
            ['display_order' => 1]
        );
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(
            [
                'name' => 'Admission Test Candidate',
                'title' => 'Candidate',
                'master_id' => 3,
            ],
            ['display_order' => 2]
        );
        $module->permissions()->sync([
            $viewPermission->id => ['name' => "{$viewPermission->name}:{$module->name}"],
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(
            [
                'name' => 'Admission Test Result',
                'title' => 'Result',
                'master_id' => 3,
            ],
            ['display_order' => 3]
        );
        $module->permissions()->sync([
            $viewPermission->id => ['name' => "{$viewPermission->name}:{$module->name}"],
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
