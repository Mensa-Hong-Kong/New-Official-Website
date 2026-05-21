<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $master_id
 * @property string $name
 * @property string|null $url
 * @property int $display_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, NavigationItem> $children
 * @property-read int|null $children_count
 * @property-read NavigationItem|null $parent
 *
 * @method static \Database\Factories\NavigationItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereMasterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NavigationItem whereUrl($value)
 *
 * @mixin \Eloquent
 */
class NavigationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_id',
        'name',
        'url',
        'display_order',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(NavigationItem::class, 'master_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(NavigationItem::class, 'master_id')
            ->orderBy('display_order');
    }
}
