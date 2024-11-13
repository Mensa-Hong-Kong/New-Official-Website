<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class VerifyCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'type_id',
        'verify_code',
    ];


    public function contact(): MorphTo
    {
        return $this->morphTo('verifyCode', 'type', 'type_id');
    }
}
