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
        $editPermission = Permission::firstOrCreate(['name' => 'Edit']);
        $deletePermission = Permission::firstOrCreate(['name' => 'Delete']);
        $masterModule->permissions()->sync([
            $viewPermission->id => ['name' => "{$viewPermission->name}:{$masterModule->name}"],
            $editPermission->id => ['name' => "{$editPermission->name}:{$masterModule->name}"],
            $deletePermission->id => ['name' => "{$deletePermission->name}:{$masterModule->name}"],
        ]);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
