<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property int $user_id
 * @property int $number
 * @property string|null $prefix_name
 * @property string|null $nickname
 * @property string|null $suffix_name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read bool $is_active
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MembershipOrder> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\MembershipTransfer> $transfers
 * @property-read int|null $transfers_count
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member wherePrefixName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereSuffixName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Member whereUserId($value)
 *
 * @mixin \Eloquent
 */
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(MembershipOrder::class, 'user_id', 'user_id');
    }

    public function transfers(): HasMany
    {
        return $this->hasMany(MembershipTransfer::class, 'user_id', 'user_id');
    }

    protected function isActive(): Attribute
    {
        $member = $this;

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($member): bool {
                $thisYear = now()->year;

                return $member->orders()
                    ->where('status', 'succeeded')
                    ->where(
                        function ($query) use ($thisYear) {
                            $query->whereNull('to_year')
                                ->where('is_returned', false)
                                ->orWhere('to_year', '>', $thisYear);
                        }
                    )->exists() || $member->transfers()
                    ->where('is_accepted', true)
                    ->where('type', 'in')
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
