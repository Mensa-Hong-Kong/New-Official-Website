<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property int $interval_month
 * @property int|null $minimum_age
 * @property int|null $maximum_age
 * @property bool $is_active
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTest> $test
 * @property-read int|null $test_count
 *
 * @method static \Database\Factories\AdmissionTestTypeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereIntervalMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereMaximumAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereMinimumAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AdmissionTestType whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class AdmissionTestType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'interval_month',
        'minimum_age',
        'maximum_age',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function test(): HasMany
    {
        return $this->hasMany(AdmissionTest::class, 'type_id');
    }
}
