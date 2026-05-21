<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $page_id
 * @property string $name
 * @property string|null $content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read SitePage|null $page
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent wherePageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SiteContent whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class SiteContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'name',
        'content',
    ];

    public function page(): BelongsTo
    {
        return $this->belongsTo(SitePage::class, 'page_id');
    }
}
