<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;

    protected $fillable = [
        'address_id',
        'name',
    ];

    public function admissionTests()
    {
        return $this->hasMany(AdmissionTest::class);
    }
}
