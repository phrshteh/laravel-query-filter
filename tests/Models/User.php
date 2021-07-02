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
}
