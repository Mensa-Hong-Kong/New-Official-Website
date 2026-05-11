<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $test_id
 * @property \Illuminate\Support\Carbon|null $taken_from
 * @property \Illuminate\Support\Carbon|null $taken_to
 * @property string|null $score
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\QualifyingTest|null $test
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereTakenFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereTakenTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|QualifyingTestDetail whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class QualifyingTestDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'taken_from',
        'taken_to',
        'score',
        'is_accepted',
    ];

    protected $casts = [
        'taken_from' => 'date',
        'taken_to' => 'date',
        'is_accepted' => 'boolean',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(QualifyingTest::class, 'test_id');
    }
}
