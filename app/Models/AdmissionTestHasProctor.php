<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * @property int $id
 * @property int $test_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor whereTestId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestHasProctor whereUserId($value)
 * @mixin \Eloquent
 */
class AdmissionTestHasProctor extends Pivot
{
    use HasFactory;

    protected $fillable = [
        'test_id',
        'user_id',
    ];
}
