<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AdmissionTestHasCandidate extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'user_id',
        'order_id',
        'seat_number',
        'is_present',
        'is_pass',
    ];

    protected $casts = [
        'is_present' => 'boolean',
        'is_pass' => 'boolean',
    ];

    public function test()
    {
        return $this->belongsTo(AdmissionTest::class, 'test_id');
    }

    public function candidate()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function isFree(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return ! $attributes['order_id'];
            }
        );
    }

    public function hasResult(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $attributes['is_pass'] !== null;
            }
        );
    }
}
