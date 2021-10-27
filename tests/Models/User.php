<?php

namespace Omalizadeh\QueryFilter\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Omalizadeh\QueryFilter\Traits\HasFilter;

class User extends Model
{
    use HasFilter;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean'
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
}
