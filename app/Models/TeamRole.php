<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Role as Model;

class TeamRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'team_id',
        'role_id',
        'display_order',
    ];
}
