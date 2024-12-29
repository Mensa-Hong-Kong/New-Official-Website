<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
    ];

    public function teams()
    {
        return $this->belongsToMany(Team::class, TeamRole::class);
    }
}
