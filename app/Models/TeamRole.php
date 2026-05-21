<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role as Model;

/**
 * @property int $id
 * @property string $name
 * @property int|null $team_id
 * @property int|null $role_id
 * @property int $display_order
 * @property string $guard_name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, ModulePermission> $permissions
 * @property-read int|null $permissions_count
 * @property-read Collection<int, User> $users
 * @property-read int|null $users_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereRoleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamRole withoutPermission($permissions)
 *
 * @mixin \Eloquent
 */
class TeamRole extends Model
{
    use HasFactory;

    protected $guard_name = 'web';

    protected $fillable = [
        'name',
        'team_id',
        'role_id',
        'display_order',
        'guard_name',
    ];
}
