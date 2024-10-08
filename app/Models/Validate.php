<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Validate extends Model
{
    use HasFactory;

    protected $fillable = [
        'validatable_type',
        'validatable_id',
        'code',
        'tried_time',
        'status',
        'expiry_at',
    ];


    public function validatable(): MorphTo
    {
        return $this->morphTo();
    }
}
