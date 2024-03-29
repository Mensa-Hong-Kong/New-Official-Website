<?php

namespace App\Models\Member;

use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        "name",
        "is_limit",
    ];
    public function members() {
        return $this->belongsToMany( Member::class );
    }
}
