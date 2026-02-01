<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'prefix_name',
        'nickname',
        'suffix_name',
        'address_id',
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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function orders()
    {
        return $this->hasMany(MembershipOrder::class);
    }

    public function latestOrder()
    {
        return $this->hasOne(MembershipOrder::class)->latestOfMany('id');
    }

    public function latestSucceededOrder()
    {
        return $this->latestOrder()->where('status', 'succeeded');
    }

    public function memberTransfers()
    {
        return $this->hasManyThrough(MembershipTransfer::class, User::class, 'id', 'user_id', 'user_id', 'id');
    }

    public function verifiedMemberTransfers()
    {
        return $this->memberTransfers()
            ->whereNotNull('verified_at');
    }

    protected function isActive(): Attribute
    {
        $member = $this;

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($member) {
                $thisYear = now()->year;

                return (
                    $member->latestSucceededOrder &&
                    $member->latestSucceededOrder->from_year <= $thisYear &&
                    (
                        ! $member->latestSucceededOrder->to_year ||
                        $member->latestSucceededOrder->to_year > $thisYear
                    )
                ) || (
                    $this->verifiedMemberTransfers()
                        ->whereIn('type', ['in', 'guest'])
                        ->where(
                            function($query) use($thisYear) {
                                $query->whereNull('membership_ended_in')
                                    ->where('membership_ended_in', '>=', $thisYear);
                            }
                        )->exists()
                );
            }
        );
    }
}
