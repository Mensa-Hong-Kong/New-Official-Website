<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_id',
        'name',
        'title',
        'display_order',
    ];

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, ModulePermission::class);
    }

    public function parent()
    {
        return $this->belongsTo(Module::class, 'master_id');
    }

    public function children()
    {
        return $this->hasMany(Module::class, 'master_id')
            ->orderBy('display_order');
    }
}
