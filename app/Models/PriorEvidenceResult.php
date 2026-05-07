<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $order_id
 * @property int $test_id
 * @property string $taken_on
 * @property string $score
 * @property numeric|null $percent_of_group
 * @property bool|null $is_accepted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PriorEvidenceOrder|null $order
 * @property-read \App\Models\QualifyingTest|null $test
 * @property-read \App\Models\User|null $user
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
        'is_accepted' => 'boolean',
    ];

    public function order()
    {
        return $this->belongsTo(PriorEvidenceOrder::class, 'order_id');
    }

    public function test()
    {
        return $this->belongsTo(QualifyingTest::class, 'test_id');
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, PriorEvidenceOrder::class, 'id', 'id', 'order_id', 'user_id');
    }
}
