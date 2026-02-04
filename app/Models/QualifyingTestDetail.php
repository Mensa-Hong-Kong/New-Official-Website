<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QualifyingTestDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'taken_from',
        'taken_to',
        'score',
        'is_accepted',
    ];

    public function test()
    {
        return $this->belongsTo(QualifyingTest::class, 'test_id');
    }
}
