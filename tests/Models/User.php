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
        'is_active' => 'boolean'
    ];

    public function isMale()
    {
        return !is_null($this->gender) and $this->gender == true;
    }

    public function isFemale()
    {
        return !is_null($this->gender) and $this->gender == false;
    }

    public function isActive()
    {
        return $this->is_active;
    }
}
