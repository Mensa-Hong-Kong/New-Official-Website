<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $passport_type_id
 * @property string|null $passport_number
 * @property int|null $user_id
 * @property string $contact_type
 * @property int|null $creator_id
 * @property string $creator_ip
 * @property int $middleware_should_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\PassportType|null $passportType
 * @property-read \App\Models\User|null $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereContactType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereCreatorIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereMiddlewareShouldCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog wherePassportNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog wherePassportTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ResetPasswordLog whereUserId($value)
 *
 * @mixin \Eloquent
 */
class ResetPasswordLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'passport_type_id',
        'passport_number',
        'user_id',
        'contact_type',
        'creator_id',
        'creator_ip',
        'middleware_should_count',
    ];

    public function passportType()
    {
        return $this->belongsTo(PassportType::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }
}
