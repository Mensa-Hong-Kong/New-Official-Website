<?php

namespace Database\Seeders;

use App\Models\Module;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $masterModule = Module::firstOrCreate(['name' => 'User']);
        $viewPermission = Permission::firstOrCreate(['name' => 'View']);
        $viewPermission->update(['display_order' => '0']);
        $editPermission = Permission::firstOrCreate(['name' => 'Edit']);
        $editPermission->update(['display_order' => '1']);
        $masterModule->permissions()->sync([
            $viewPermission->id => ['name' => "{$viewPermission->name}:{$masterModule->name}"],
            $editPermission->id => ['name' => "{$editPermission->name}:{$masterModule->name}"],
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
