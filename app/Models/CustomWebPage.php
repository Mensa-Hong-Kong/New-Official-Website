<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

/**
 * @property int $id
 * @property string $pathname
 * @property string $title
 * @property string|null $og_image_url
 * @property string $description
 * @property string|null $content
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\CustomWebPageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage sortable($defaultParameters = null)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereOgImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage wherePathname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomWebPage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CustomWebPage extends Model
{
    use HasFactory, Sortable;

    protected $fillable = [
        'pathname',
        'title',
        'og_image_url',
        'description',
        'content',
    ];

    public $sortable = [
        'pathname',
        'title',
        'created_at',
        'updated_at',
    ];
}
