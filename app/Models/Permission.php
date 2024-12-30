<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
    ];

    public function permission()
    {
        return $this->belongsToMany(Module::class, ModulePermission::class);
    }
}