<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'module_id',
    ];

    public function master()
    {
        return $this->belongsTo(Module::class);
    }

    public function children()
    {
        return $this->hasMany(Module::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, ModulePermission::class);
    }
}
