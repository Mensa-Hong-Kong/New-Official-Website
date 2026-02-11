<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'district_id',
        'value',
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function admissionTests()
    {
        return $this->hasMany(AdmissionTest::class);
    }

    public function user()
    {
        return $this->hasMany(User::class);
    }

    public function updateAddress($districtID, $value)
    {
        if ($districtID == $this->district_id && $value == $this->value) {
            return $this;
        }
        $address = Address::firstWhere([
            'district_id' => $districtID,
            'value' => $value,
        ]);
        if ($this->user()->count() + $this->admissionTests()->count() == 1) {
            if ($address) {
                $this->delete();
            } else {
                $this->update([
                    'district_id' => $districtID,
                    'value' => $value,
                ]);

                return $this;
            }
        } elseif (! $address) {
            $address = Address::create([
                'district_id' => $districtID,
                'value' => $value,
            ]);
        }

        return $address;
    }
}
