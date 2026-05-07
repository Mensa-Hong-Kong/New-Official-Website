<?php

namespace App\Library\Stripe\Models;

use App\Library\Stripe\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $customerable_type
 * @property int $customerable_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|\Eloquent $customerable
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer whereCustomerableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer whereCustomerableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StripeCustomer whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class StripeCustomer extends Model
{
    use HasFactory;

    public ?array $data = null;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'customerable_type',
        'customerable_id',
    ];

    public function customerable()
    {
        return $this->morphTo();
    }

    public function getStripe(): ?array
    {
        if (! $this->data) {
            $this->data = Client::customers()->find($this->id);
        }

        return $this->data;
    }
}
