<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'testing_at',
        'location_id',
        'maximum_candidates',
        'is_public',
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }
}
