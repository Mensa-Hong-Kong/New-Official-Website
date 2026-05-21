<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $master_id
 * @property string $name
 * @property string|null $title
 * @property int $display_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Module> $children
 * @property-read int|null $children_count
 * @property-read Module|null $parent
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereMasterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Module whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_id',
        'name',
        'title',
        'display_order',
    ];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, ModulePermission::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'master_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Module::class, 'master_id')
            ->orderBy('display_order');
    }
}
