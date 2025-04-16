<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdmissionTestType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'interval_month',
        'is_active',
        'display_order',
    ];
}
