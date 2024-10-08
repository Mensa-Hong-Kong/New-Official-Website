<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class UserContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type_id',
        'contact',
        'is_display',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function type(){
        return $this->belongsTo(ContactType::class, 'type_id');
    }

    public function validates(): MorphMany
    {
        return $this->morphMany(Validate::class, 'validatable');
    }

    public function validated(): MorphOne
    {
        return $this->morphOne(Validate::class, 'validatable')
            ->where('status', true)
            ->where(
                function($query) {
                    $query->whereNull('expiry_at')
                        ->orWhere('expiry_at', '<=', now());
                }
            );
    }

    public function lastValidate(): MorphOne
    {
        return $this->morphOne(Validate::class, 'validatable')
            ->latest('id');
    }
}
