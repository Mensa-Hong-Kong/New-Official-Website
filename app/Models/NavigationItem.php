<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int|null $master_id
 * @property string $name
 * @property string|null $url
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, NavigationItem> $children
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

    public function parent()
    {
        return $this->belongsTo(NavigationItem::class, 'master_id');
    }

    public function children()
    {
        return $this->hasMany(NavigationItem::class, 'master_id')
            ->orderBy('display_order');
    }
}
