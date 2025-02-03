<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomPage extends Model
{
    use HasFactory;

    protected $primaryKey = 'pathname';

    public $incrementing = false;

    protected $fillable = [
        'pathname',
        'title',
        'og_image_url',
        'description',
        'content',
    ];
}
