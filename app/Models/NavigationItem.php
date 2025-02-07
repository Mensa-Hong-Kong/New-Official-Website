<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NavigationItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'master_id',
        'name',
        'url',
        'display_order',
    ];

    public function parent()
    {
        return $this->belongsTo(NavigationItem::class, 'master_id');
    }

    public function children()
    {
        return $this->hasMany(NavigationItem::class, 'master_id')
            ->orderBy('display_order');
    }
}
