<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $product_name
 * @property string|null $price_name
 * @property numeric $price
 * @property string $status
 * @property \Illuminate\Support\Carbon $expired_at
 * @property int|null $from_year
 * @property int|null $to_year
 * @property string $gateway_type
 * @property int $gateway_id
 * @property string|null $reference_number
 * @property numeric|null $gateway_payment_fee
 * @property bool $is_returned
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $gateway
 * @property-read \App\Models\Member|null $member
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereFromYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereGatewayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereGatewayPaymentFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereGatewayType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereIsReturned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder wherePriceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereToYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipOrder whereUserId($value)
 *
 * @mixin \Eloquent
 */
class MembershipOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'price_name',
        'price',
        'status',
        'expired_at',
        'from_year',
        'to_year',
        'gateway_type',
        'gateway_id',
        'reference_number',
        'gateway_payment_fee',
        'is_returned',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'expired_at' => 'datetime',
        'gateway_payment_fee' => 'decimal:2',
        'is_returned' => 'boolean',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class, 'user_id', 'user_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function gateway(): MorphTo
    {
        return $this->morphTo();
    }
}
