<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PassportType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PassportType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'display_order',
    ];
}
