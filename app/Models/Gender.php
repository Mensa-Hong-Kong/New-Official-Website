<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Gender whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class Gender extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function updateName(string $name): Gender
    {
        if ($name == $this->name) {
            return $this;
        }
        $gender = Gender::firstWhere(['name' => $name]);
        if ($this->users()->count() == 1) {
            if (! $gender) {
                $this->update(['name' => $name]);

                return $this;
            } else {
                $this->delete();
            }
        } elseif (! $gender) {
            $gender = Gender::create(['name' => $name]);
        }

        return $gender;
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
