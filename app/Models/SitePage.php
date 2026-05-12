<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SiteContent> $contents
 * @property-read int|null $contents_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SitePage whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class SitePage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function contents(): HasMany
    {
        return $this->hasMany(SiteContent::class, 'page_id');
    }
}
