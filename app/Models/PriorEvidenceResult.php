<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Support\Carbon;

/**
 * @property int $order_id
 * @property int $test_id
 * @property Carbon $taken_on
 * @property string $score
 * @property numeric|null $percent_of_group
 * @property bool|null $is_accepted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PriorEvidenceOrder|null $order
 * @property-read QualifyingTest|null $test
 * @property-read User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereIsAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult wherePercentOfGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereTakenOn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceResult whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class PriorEvidenceResult extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';

    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'test_id',
        'taken_on',
        'score',
        'percent_of_group',
        'is_accepted',
    ];

    protected $casts = [
        'taken_on' => 'date',
        'percent_of_group' => 'decimal:2',
        'is_accepted' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(PriorEvidenceOrder::class, 'order_id');
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(QualifyingTest::class, 'test_id');
    }

    public function user(): HasOneThrough
    {
        return $this->hasOneThrough(User::class, PriorEvidenceOrder::class, 'id', 'id', 'order_id', 'user_id');
    }
}
