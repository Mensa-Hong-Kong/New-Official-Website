<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gender extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function updateName($name)
    {
        if ($name == $this->name) {
            return $this;
        }
        $gender = Gender::firstWhere(['name' => $name]);
        if ($this->users()->count() == 1) {
            if (! $gender) {
                $this->update(['name' => $name]);

                return $this;
            } else {
                $this->delete();
            }
        } elseif (! $gender) {
            $gender = Gender::create(['name' => $name]);
        }

        return $gender;
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
