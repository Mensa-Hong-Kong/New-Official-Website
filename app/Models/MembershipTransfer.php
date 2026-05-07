<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $user_id
 * @property string $type
 * @property int $national_mensa_id
 * @property int|null $membership_number
 * @property int|null $membership_ended_in
 * @property string|null $remark
 * @property int|null $is_accepted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\NationalMensa|null $nationalMensa
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereIsAccepted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereMembershipEndedIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereMembershipNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereNationalMensaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereRemark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MembershipTransfer whereUserId($value)
 * @mixin \Eloquent
 */
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
        'is_accepted',
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
