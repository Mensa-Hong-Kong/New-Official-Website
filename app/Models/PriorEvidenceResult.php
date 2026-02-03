<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriorEvidenceResult extends Model
{
    use HasFactory;

    protected $primaryKey = 'order_id';

    public $incrementing = false;

    protected $fillable = [
        'order_id',
        'test_id',
        'taken_on',
        'score',
        'percent_of_group',
        'is_pass',
    ];

    public function order()
    {
        return $this->belongsTo(PriorEvidenceOrder::class, 'order_id');
    }

    public function test()
    {
        return $this->belongsTo(QualifyingTest::class, 'test_id');
    }

    public function user()
    {
        return $this->hasOneThrough(User::class, PriorEvidenceOrder::class, 'id', 'id', 'order_id', 'user_id');
    }
}
