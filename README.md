[![Latest Stable Version](https://poser.pugx.org/omalizadeh/laravel-query-filter/v)](https://packagist.org/packages/omalizadeh/laravel-query-filter)
[![Tests](https://github.com/omalizadeh/laravel-query-filter/actions/workflows/tests.yml/badge.svg)](https://github.com/omalizadeh/laravel-query-filter/actions/workflows/tests.yml)
[![License](https://poser.pugx.org/omalizadeh/laravel-query-filter/license)](https://packagist.org/packages/omalizadeh/laravel-query-filter)
[![Total Downloads](https://poser.pugx.org/omalizadeh/laravel-query-filter/downloads)](https://packagist.org/packages/omalizadeh/laravel-query-filter)

# Laravel Query Filter

Laravel query filter provides an elegant way to filter resources via request query string. You can specify conditions in
query string to filter eloquent models and resources.

## Installation & Usage

Install via composer:

```
composer require omalizadeh/laravel-query-filter
```

Make a filter class:

```
php artisan make:filter FilterClassName
```

Add trait in model:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Omalizadeh\QueryFilter\Traits\HasFilter;

class User extends Model
{
    use HasFilter;

    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
```

Set filterable attributes, relations and other options in filter class:

```php
<?php

namespace App\Filters;

use Omalizadeh\QueryFilter\ModelFilter;

class UserFilter extends ModelFilter
{
    protected function selectableAttributes(): array
    {
        return [
            'id',
            'phone'
        ];
    }

    protected function sortableAttributes(): array
    {
        return [
            'id',
            'created_at',
            'updated_at'
        ];
    }

    protected function summableAttributes(): array
    {
        return [
            'views'
        ];
    }

    protected function filterableAttributes(): array
    {
        return [
            'id',
            'phone',
            'is_active'
        ];
    }

    protected function filterableRelations(): array
    {
        return [
            'profile' => [
                'gender',
                'first_name'
            ],
            'posts' => [
                'post_body' => 'body'
            ]
        ];
    }

    protected function loadableRelations(): array
    {
        return [
            'profile'
        ];
    }
}
```

Filtering resources via json formatted filter parameter with pagination & sort sent as `q` in query string:

```
api/users?q={
    "page": {
        "limit": 20,
        "offset": 0
    },
    "sorts": [
        {
            "field": "id",
            "dir": "desc"
        }
    ],
    "filters": [
        [
            {
                "field": "is_active",
                "op": "=",
                "value": true
            }
        ]
    ],
    "sums": ["views"],
    "withs": ["profile"]
}
```

In Controller:

```php

public function index(UserFilter $userFilter)
{
    $userFilterResult = User::filter($userFilter);
    
    // total resources count based on filters
    $count = $userFilterResult->count();
    
    // total sum of given attributes in filter if there is any
    $sums = $userFilterResult->sums();
    
    // Get query results as collection (paginated if there is pagination in filters)
    $users = $userFilterResult->data();
    
    // do stuff and return response
}
```

### Available Operators

| Operators | Value          | Description                                            |
| --------- | -------------- | ------------------------------------------------------ |
| =         | string/numeric/bool | Field is equal to value                                |
| !=        | string/numeric/bool | Field is not equal to value                            |
| <>        | string/numeric/bool | Field is not equal to value                            |
| >         | string/numeric | Field is greater than value                            |
| >=        | string/numeric | Field is greater than or equal to value                |
| <         | string/numeric | Field is lower than value                              |
| <=        | string/numeric | Field is lower than or equal to value                  |
| like      | string         | Field is like string value                             |
| not like  | string         | Field is not like string                               |
| in        | array          | Field value is in given array                          |
| not       | null/array     | Field is not null (for null value)/ Not in given array |
| is        | null           | Field is null                                          |

### Query String Format

Example conditions:

```
(`is_active` = 1 OR `phone` like "%912%") AND (`first_name` like "%omid%")
```

Then json filter will be:

```json
{
    "page": {
        "limit": 20,
        "offset": 0
    },
    "sorts": [
        {
            "field": "id",
            "dir": "desc"
        }
    ],
    "filters": [
        [
            {
                "field": "is_active",
                "op": "=",
                "value": 1
            },
            {
                "field": "phone",
                "op": "like",
                "value": "912"
            }
        ],
        [
            {
                "field": "first_name",
                "op": "like",
                "value": "omid",
                "has": true
            }
        ]
    ]
}
```

## License

Laravel Query Filter is open-sourced software licensed under the [MIT license](LICENSE).
