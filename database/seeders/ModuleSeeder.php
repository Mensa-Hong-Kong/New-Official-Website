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
        $module->permissions()->sync([
            $viewPermission->id => ['name' => "{$viewPermission->name}:{$module->name}"],
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);
        $module = Module::firstOrCreate(['name' => 'Permission']);
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);
        $module = Module::firstOrCreate(['name' => 'Admission Test']);
        $module->permissions()->sync([
            $editPermission->id => ['name' => "{$editPermission->name}:{$module->name}"],
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
