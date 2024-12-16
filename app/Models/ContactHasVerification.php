<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ContactHasVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'tried_time',
        'code',
        'closed_at',
        'verified_at',
        'expired_at',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'verified_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function isClosed()
    {
        return now() > $this->closed_at;
    }

    public function isTriedTooManyTime()
    {
        return $this->tried_time >= 5;
    }

}
