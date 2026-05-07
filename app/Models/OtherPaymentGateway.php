<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTestOrder> $admissionTestOrders
 * @property-read int|null $admission_test_orders_count
 * @property-read mixed $type
 * @method static \Database\Factories\OtherPaymentGatewayFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OtherPaymentGateway whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class OtherPaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function admissionTestOrders()
    {
        return $this->morphMany(AdmissionTestOrder::class, 'gateway');
    }

    protected function type(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return 'Manual Handling';
            }
        );
    }
}
