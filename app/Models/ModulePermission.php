<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as Model;

/**
 * @property int $id
 * @property string $name
 * @property int $module_id
 * @property int $permission_id
 * @property string $guard_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ModulePermission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\TeamRole> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission whereModuleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission wherePermissionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ModulePermission withoutRole($roles, $guard = null)
 * @mixin \Eloquent
 */
class ModulePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'module_id',
        'permission_id',
    ];
}
