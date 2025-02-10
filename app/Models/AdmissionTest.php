<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class AdmissionTest extends Model
{
    use HasFactory, Sortable;

    protected $fillable = [
        'testing_at',
        'location_id',
        'address_id',
        'maximum_candidates',
        'is_public',
    ];

    public $sortable = [
        'id',
        'testing_at',
    ];

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

    public function inTestingTimeRange()
    {
        return $this->testing_at >= now()->subHours(2) && $this->testing_at <= now()->addHours(2);
    }
}
