<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TeamType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'display_order',
    ];

    public function teams()
    {
        return $this->hasMany(Team::class, 'type_id');
    }
}
