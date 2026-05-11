<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $test_id
 * @property int $user_id
 * @property int|null $order_id
 * @property int|null $seat_number
 * @property bool|null $is_present
 * @property bool|null $is_passed
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $candidate
 * @property-read bool $has_result
 * @property-read bool $is_free
 * @property-read \App\Models\AdmissionTest $test
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereIsPass($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereIsPresent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereSeatNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasCandidate whereUserId($value)
 *
 * @mixin \Eloquent
 */
class AdmissionTestHasCandidate extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'user_id',
        'order_id',
        'seat_number',
        'is_present',
        'is_passed',
    ];

    protected $casts = [
        'is_present' => 'boolean',
        'is_passed' => 'boolean',
    ];

    public function test(): BelongsTo
    {
        return $this->belongsTo(AdmissionTest::class, 'test_id');
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isFree(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): bool {
                return ! $attributes['order_id'];
            }
        );
    }

    public function hasResult(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): bool {
                return $attributes['is_passed'] !== null;
            }
        );
    }
}
