<?php

namespace App\Library\Stripe\Models;

use App\Library\Stripe\Client;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
