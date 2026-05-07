<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $url
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NationalMensa whereUrl($value)
 *
 * @mixin \Eloquent
 */
class NationalMensa extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'url',
        'is_active',
    ];
}
