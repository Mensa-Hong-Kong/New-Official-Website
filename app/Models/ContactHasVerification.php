<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactHasVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'contact_id',
        'contact',
        'type',
        'code',
        'tried_time',
        'closed_at',
        'verified_at',
        'expired_at',
        'creator_id',
        'creator_ip',
        'middleware_should_count',
    ];

    protected $casts = [
        'closed_at' => 'datetime',
        'verified_at' => 'datetime',
        'expired_at' => 'datetime',
    ];

    public function contact()
    {
        return $this->belongsTo(UserHasContact::class, 'contact_id');
    }

    public function isClosed()
    {
        return now() > $this->closed_at;
    }

    public function isTriedTooManyTime()
    {
        return $this->tried_time >= 5;
    }
}
