<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipTransfer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'national_mensa_id',
        'membership_number',
        'membership_ended_in',
        'remark',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nationalMensa()
    {
        return $this->belongsTo(NationalMensa::class);
    }
}
