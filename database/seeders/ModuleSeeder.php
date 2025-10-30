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
 * | id  | name                  | title | display_order | created_at | updated_at |
 * | --- | --------------------- | ----- | ------------- | ---------- | ---------- |
 * | 1   | User                  | NULL  | 1             | ...        | ...        |
 * | 2   | Permission            | NULL  | 2             | ...        | ...        |
 * | 3   | Admission Test        | NULL  | 3             | ...        | ...        |
 * | 4   | Admission Test Order  | NULL  | 4             | ...        | ...        |
 * | 5   | Site Content          | NULL  | 5             | ...        | ...        |
 * | 6   | Custom Web Page       | NULL  | 6             | ...        | ...        |
 * | 7   | Navigation Item       | NULL  | 7             | ...        | ...        |
 * | 8   | Other Payment Gateway | NULL  | 8             | ...        | ...        |
 *
 * The 'module_permissions' table will contain:
 * | id  | name                       | module_id | permission_id | guard_name | created_at | updated_at |
 * | --- | -------------------------- | --------- | ------------- | ---------- | ---------- | ---------- |
 * | 1   | View:User                  | 1         | 1             | web        | ...        | ...        |
 * | 2   | Edit:User                  | 1         | 2             | web        | ...        | ...        |
 * | 3   | Edit:Permission            | 2         | 2             | web        | ...        | ...        |
 * | 4   | Edit:Admission Test        | 3         | 2             | web        | ...        | ...        |
 * | 5   | View:Admission Test Order  | 4         | 1             | web        | ...        | ...        |
 * | 6   | Edit:Admission Test Order  | 4         | 2             | web        | ...        | ...        |
 * | 7   | Edit:Site Content          | 5         | 2             | web        | ...        | ...        |
 * | 8   | Edit:Custom Web Page       | 6         | 2             | web        | ...        | ...        |
 * | 9   | Edit:Navigation Item       | 7         | 2             | web        | ...        | ...        |
 * | 10  | Edit:Other Payment Gateway | 8         | 2             | web        | ...        | ...        |
 */
class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $viewPermission = Permission::firstOrCreate(['name' => 'View']);
        $viewPermission->update(['display_order' => 0]);
        $editPermission = Permission::firstOrCreate(['name' => 'Edit']);
        $editPermission->update(['display_order' => 1]);

        $module = Module::firstOrCreate(['name' => 'User']);
        $module->update(['display_order' => 1]);
        $module->permissions()->sync([
            $viewPermission->id => ['name' => "{$viewPermission->name}:{$module->name}"],
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(['name' => 'Permission']);
        $module->update(['display_order' => 2]);
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(['name' => 'Admission Test']);
        $module->update(['display_order' => 3]);
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(['name' => 'Admission Test Order']);
        $module->update(['display_order' => 4]);
        $module->permissions()->sync([
            $viewPermission->id => ['name' => "{$viewPermission->name}:{$module->name}"],
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(['name' => 'Site Content']);
        $module->update(['display_order' => 5]);
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(['name' => 'Custom Web Page']);
        $module->update(['display_order' => 6]);
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(['name' => 'Navigation Item']);
        $module->update(['display_order' => 7]);
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        $module = Module::firstOrCreate(['name' => 'Other Payment Gateway']);
        $module->update(['display_order' => 8]);
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
