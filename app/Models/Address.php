<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'address',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function admissionTests()
    {
        return $this->hasMany(AdmissionTest::class);
    }

    public function members()
    {
        return $this->hasMany(Member::class);
    }
}
