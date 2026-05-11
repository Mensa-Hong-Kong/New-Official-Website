<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $contact_id
 * @property string $contact
 * @property string $type
 * @property string|null $code
 * @property int $tried_time
 * @property \Illuminate\Support\Carbon|null $closed_at
 * @property \Illuminate\Support\Carbon|null $verified_at
 * @property \Illuminate\Support\Carbon|null $expired_at
 * @property int $creator_id
 * @property string $creator_ip
 * @property bool $middleware_should_count
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereClosedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereContactId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereCreatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereCreatorIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereMiddlewareShouldCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereTriedTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ContactHasVerification whereVerifiedAt($value)
 *
 * @mixin \Eloquent
 */
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
        'middleware_should_count' => 'boolean',
    ];

    public function isClosed(): bool
    {
        return now() > $this->closed_at;
    }

    public function isTriedTooManyTime(): bool
    {
        return $this->tried_time >= 5;
    }
}
