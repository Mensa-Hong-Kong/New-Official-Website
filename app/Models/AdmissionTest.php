<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;
use Kyslik\ColumnSortable\Sortable;

/**
 * @property int $id
 * @property int $type_id
 * @property \Illuminate\Support\Carbon $testing_at
 * @property \Illuminate\Support\Carbon|null $expect_end_at
 * @property int|null $location_id
 * @property int|null $address_id
 * @property int|null $maximum_candidates
 * @property bool $is_free
 * @property bool $is_public
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Address|null $address
 * @property-read \App\Models\AdmissionTestHasProctor|\App\Models\AdmissionTestHasCandidate|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $candidates
 * @property-read int|null $candidates_count
 * @property-read bool $current_user_is_proctor
 * @property-read bool $in_testing_time_range
 * @property-read \App\Models\Location|null $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $proctors
 * @property-read int|null $proctors_count
 * @property-read \App\Models\AdmissionTestType|null $type
 *
 * @method static \Database\Factories\AdmissionTestFactory factory($count = null, $state = [])
 * @method static Builder<static>|AdmissionTest newModelQuery()
 * @method static Builder<static>|AdmissionTest newQuery()
 * @method static Builder<static>|AdmissionTest query()
 * @method static Builder<static>|AdmissionTest sortable($defaultParameters = null)
 * @method static Builder<static>|AdmissionTest whereAddressId($value)
 * @method static Builder<static>|AdmissionTest whereAvailable()
 * @method static Builder<static>|AdmissionTest whereCreatedAt($value)
 * @method static Builder<static>|AdmissionTest whereExpectEndAt($value)
 * @method static Builder<static>|AdmissionTest whereId($value)
 * @method static Builder<static>|AdmissionTest whereIsFree($value)
 * @method static Builder<static>|AdmissionTest whereIsPublic($value)
 * @method static Builder<static>|AdmissionTest whereLocationId($value)
 * @method static Builder<static>|AdmissionTest whereMaximumCandidates($value)
 * @method static Builder<static>|AdmissionTest whereTestingAt($value)
 * @method static Builder<static>|AdmissionTest whereTypeId($value)
 * @method static Builder<static>|AdmissionTest whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class AdmissionTest extends Model
{
    use HasFactory, Sortable;

    protected $fillable = [
        'type_id',
        'testing_at',
        'expect_end_at',
        'location_id',
        'address_id',
        'maximum_candidates',
        'is_free',
        'is_public',
    ];

    public array $sortable = [
        'id',
        'testing_at',
    ];

    protected $casts = [
        'testing_at' => 'datetime',
        'expect_end_at' => 'datetime',
        'is_free' => 'boolean',
        'is_public' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(AdmissionTestType::class, 'type_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function proctors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, AdmissionTestHasProctor::class, 'test_id');
    }

    public function candidates(): BelongsToMany
    {
        return $this->belongsToMany(User::class, AdmissionTestHasCandidate::class, 'test_id')
            ->withPivot(['order_id', 'seat_number', 'is_present', 'is_pass']);
    }

    protected function inTestingTimeRange(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes): bool {
                return $attributes['testing_at'] <= now()->addHours(2) &&
                    $attributes['expect_end_at'] >= now()->subHour();
            }
        );
    }

    public function scopeWhereAvailable(Builder $query): void
    {
        $query->whereHas(
            'candidates', null, '<=',
            DB::raw($this->getTable().'.maximum_candidates')
        );
    }

    public function currentUserIsProctor(): Attribute
    {
        $test = $this;

        return Attribute::make(
            get: function (mixed $value, array $attributes) use ($test): bool {
                return $test->proctors()
                    ->where('user_id', request()->user()->id)
                    ->exists();
            }
        );
    }
}
