<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Permission\Models\Permission as Model;

class ModulePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'module_id',
        'permission_id',
    ];
}
