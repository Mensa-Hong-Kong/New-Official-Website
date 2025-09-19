<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Kyslik\ColumnSortable\Sortable;

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
        'is_public',
    ];

    public $sortable = [
        'id',
        'testing_at',
    ];

    protected $casts = [
        'testing_at' => 'datetime',
        'expect_end_at' => 'datetime',
    ];

    public function type()
    {
        return $this->belongsTo(AdmissionTestType::class, 'type_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function proctors()
    {
        return $this->belongsToMany(User::class, AdmissionTestHasProctor::class, 'test_id');
    }

    public function candidates()
    {
        return $this->belongsToMany(User::class, AdmissionTestHasCandidate::class, 'test_id')
            ->withPivot(['is_present', 'is_pass']);
    }

    protected function inTestingTimeRange(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $attributes['testing_at'] <= now()->addHours(2) &&
                    $attributes['expect_end_at'] >= now()->subHour();
            }
        );
    }

    public function scopeWhereAvailable()
    {
        $thisTable = $this->getTable();

        return $this->whereHas(
            'candidates', null, '<=',
            DB::raw("$thisTable.maximum_candidates")
        );
    }
}
