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
        $masterModule->sync([$viewPermission => ['name' => "{$viewPermission->name}:{$masterModule->name}"]]);
        $module = Module::firstOrCreate(
            [
                'name' => 'Info',
                'module_id' => $masterModule->id,
            ]
        );
        $editPermission = Permission::firstOrCreate(['name' => 'Edit']);
        $module->sync([$editPermission => ['name' => "{$editPermission->name}:{$module->name}"]]);
        $module = Module::firstOrCreate(
            [
                'name' => 'Contact',
                'module_id' => $masterModule->id,
            ]
        );
        $deletePermission = Permission::firstOrCreate(['name' => 'Delete']);
        $module->sync([
            $editPermission => ['name' => "{$editPermission->name}:{$module->name}"],
            $deletePermission => ['name' => "{$deletePermission->name}:{$module->name}"],
        ]);
    }
}
