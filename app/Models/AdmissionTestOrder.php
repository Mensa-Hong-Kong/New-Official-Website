<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionTestOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'product_name',
        'price_id',
        'quota',
        'type',
        'status',
        'expired_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tests()
    {
        return $this->belongsToMany(AdmissionTest::class, AdmissionTestHasCandidate::class, 'order_id', 'test_id');
    }

    public function attendedTests()
    {
        return $this->tests()->where('is_present', true);
    }
}
