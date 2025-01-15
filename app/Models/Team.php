<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type_id',
        'display_order',
    ];

    public function type()
    {
        return $this->hasMany(TeamType::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, TeamRole::class);
    }
}
