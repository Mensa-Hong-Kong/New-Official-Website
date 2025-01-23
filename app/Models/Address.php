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
}
