<?php

namespace Omalizadeh\QueryFilter\Tests\Models;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Omalizadeh\QueryFilter\Tests\Database\Factories\UserFactory;
use Omalizadeh\QueryFilter\Traits\HasFilter;

class User extends Model
{
    use HasFilter, HasFactory;

    protected $guarded = [
        'id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function isFemale(): bool
    {
        return !empty($this->profile) && !is_null($this->profile->gender) && $this->profile->gender === false;
    }

    public function isMale(): bool
    {
        return !empty($this->profile) && $this->profile->gender === true;
    }

    protected static function newFactory(): Factory
    {
        return new UserFactory();
    }
}
