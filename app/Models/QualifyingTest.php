<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, QualifyingTestDetail> $details
 * @property-read int|null $details_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTest whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class QualifyingTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function details(): HasMany
    {
        return $this->hasMany(QualifyingTestDetail::class, 'test_id');
    }
}
