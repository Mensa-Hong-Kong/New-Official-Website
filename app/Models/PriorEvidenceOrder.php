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
 * @property string $gateway_type
 * @property int $gateway_id
 * @property string|null $reference_number
 * @property numeric|null $gateway_payment_fee
 * @property int $is_returned
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $gateway
 * @property-read \App\Models\PriorEvidenceResult|null $result
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereGatewayId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereGatewayPaymentFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereGatewayType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereIsReturned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder wherePriceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereProductName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereReferenceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PriorEvidenceOrder whereUserId($value)
 *
 * @mixin \Eloquent
 */
class PriorEvidenceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'price_name',
        'price',
        'status',
        'expired_at',
        'gateway_type',
        'gateway_id',
        'reference_number',
        'gateway_payment_fee',
        'is_returned',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function gateway()
    {
        return $this->morphTo();
    }

    public function result()
    {
        return $this->hasOne(PriorEvidenceResult::class, 'order_id');
    }
}
