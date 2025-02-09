<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Kyslik\ColumnSortable\Sortable;

class CustomPage extends Model
{
    use HasFactory, Sortable;

    protected $fillable = [
        'pathname',
        'title',
        'og_image_url',
        'description',
        'content',
    ];

    public $sortable = [
        'pathname',
        'title',
        'created_at',
        'updated_at',
    ];
}
