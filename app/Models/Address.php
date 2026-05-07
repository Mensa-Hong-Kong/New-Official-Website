<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $district_id
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdmissionTest> $admissionTests
 * @property-read int|null $admission_tests_count
 * @property-read \App\Models\District|null $district
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $user
 * @property-read int|null $user_count
 * @method static \Database\Factories\AddressFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereDistrictId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Address whereValue($value)
 * @mixin \Eloquent
 */
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
