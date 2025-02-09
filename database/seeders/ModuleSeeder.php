<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $module = Module::firstOrCreate(['name' => 'User']);
        $viewPermission = Permission::firstOrCreate(['name' => 'View']);
        $viewPermission->update(['display_order' => '0']);
        $editPermission = Permission::firstOrCreate(['name' => 'Edit']);
        $editPermission->update(['display_order' => '1']);
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
        $module = Module::firstOrCreate(['name' => 'Custom Page']);
        $module->update(['display_order' => 4]);
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);
        $module = Module::firstOrCreate(['name' => 'Navigation Item']);
        $module->update(['display_order' => 5]);
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
