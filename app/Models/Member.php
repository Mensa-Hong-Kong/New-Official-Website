<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Member extends Model
{
    use HasFactory;

    public $incrementing = false;

    protected $fillable = [
        'id',
        'user_id',
        'is_active',
        'expired_on',
        'actual_expired_on',
        'prefix_name',
        'nickname',
        'suffix_name',
        'address_id',
        'forward_email',
    ];

    protected $casts = [
        'expired_on' => 'date',
        'actual_expired_on' => 'date',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(
            function (Member $member) {
                $member->id = DB::raw('(SELECT IFNULL(MAX(id), 0)+1 FROM '.(new self)->getTable().' temp)');
            }
        );
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }
}
