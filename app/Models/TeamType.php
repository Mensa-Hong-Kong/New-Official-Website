<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string|null $title
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Team> $teams
 * @property-read int|null $teams_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TeamType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class TeamType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'display_order',
    ];

    public function teams()
    {
        return $this->hasMany(Team::class, 'type_id');
    }
}
