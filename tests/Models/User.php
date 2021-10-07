<?php

namespace Omalizadeh\QueryFilter\Tests\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Omalizadeh\QueryFilter\Traits\HasFilter;

class User extends Authenticatable
{
    use HasFilter;

    protected $guarded = [
        'id'
    ];

    protected $casts = [
        'gender' => 'boolean',
        'is_active' => 'boolean'
    ];

    public function isMale(): bool
    {
        return $this->gender === true;
    }

    public function isFemale(): bool
    {
        return !is_null($this->gender) && $this->gender === false;
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}
