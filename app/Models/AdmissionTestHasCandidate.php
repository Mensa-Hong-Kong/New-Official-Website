<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AdmissionTestHasCandidate extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'user_id',
        'is_present',
        'is_pass',
        'order_id',
    ];

    public function test()
    {
        return $this->belongsTo(AdmissionTest::class, 'test_id');
    }

    public function candidate()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
