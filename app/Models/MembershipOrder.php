<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $product_name
 * @property string|null $price_name
 * @property numeric $price
 * @property string $status
 * @property string $expired_at
 * @property int|null $from_year
 * @property int|null $to_year
 * @property string $gateway_type
 * @property int $gateway_id
 * @property string|null $reference_number
 * @property numeric|null $gateway_payment_fee
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
    ];

    public function member()
    {
        return $this->belongsTo(Member::class, 'user_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gateway()
    {
        return $this->morphTo();
    }
}
