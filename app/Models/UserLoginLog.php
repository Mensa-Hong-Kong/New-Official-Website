<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property int $user_id
 * @property bool $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserLoginLog whereUserId($value)
 *
 * @mixin \Eloquent
 */
class UserLoginLog extends Authenticatable
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
    ];

    public $sortable = [
        'created_at',
    ];

    public $casts = [
        'status' => 'boolean',
    ];
}
