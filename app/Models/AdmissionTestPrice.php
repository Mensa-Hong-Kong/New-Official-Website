<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionTestPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'name',
        'price',
        'start_at',
        'stripe_id',
        'synced_to_stripe',
    ];

    public function product()
    {
        return $this->belongsTo(AdmissionTestProduct::class, 'product_id');
    }
}
