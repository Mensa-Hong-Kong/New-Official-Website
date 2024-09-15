<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PassportType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];
}
