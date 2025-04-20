<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionTestProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'minimum_age',
        'maximum_age',
        'stripe_id',
        'synced_to_stripe',
    ];
}
