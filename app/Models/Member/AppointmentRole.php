<?php

namespace App\Models\Member;

use Illuminate\Database\Eloquent\Model;

class AppointmentRole extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
    ];
    public function appointments() {
        return $this->hasMany( Appointment::class );
    }
}
