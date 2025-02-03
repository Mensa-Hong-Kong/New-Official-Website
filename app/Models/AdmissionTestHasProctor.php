<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class AdmissionTestHasProctor extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'user_id',
    ];
}
