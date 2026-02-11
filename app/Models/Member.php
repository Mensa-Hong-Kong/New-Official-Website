<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Member extends Model
{
    use HasFactory;

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'number',
        'prefix_name',
        'nickname',
        'suffix_name',
    ];

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(
            function (Member $member) {
                $member->number = DB::raw('(SELECT IFNULL(MAX(number), 0)+1 FROM '.(new self)->getTable().' temp)');
            }
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(MembershipOrder::class, 'user_id', 'user_id');
    }

    public function transfers()
    {
        return $this->hasMany(MembershipTransfer::class, 'user_id', 'user_id');
    }

    protected function isActive(): Attribute
    {
        $member = $this;

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($member) {
                $thisYear = now()->year;

                return $member->orders()
                    ->where('status', 'succeeded')
                    ->where(
                        function ($query) use ($thisYear) {
                            $query->whereNull('to_year')
                                ->orWhere('to_year', '>', $thisYear);
                        }
                    )->exists() || $this->transfers()
                    ->where('is_accepted', true)
                    ->whereIn('type', ['in', 'guest'])
                    ->where(
                        function ($query) use ($thisYear) {
                            $query->whereNull('membership_ended_in')
                                ->where('membership_ended_in', '>=', $thisYear);
                        }
                    )->exists();
            }
        );
    }
}
