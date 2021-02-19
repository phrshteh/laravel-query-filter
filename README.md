[![Latest Stable Version](https://poser.pugx.org/omalizadeh/laravel-query-filter/v)](https://packagist.org/packages/omalizadeh/laravel-query-filter)
[![License](https://poser.pugx.org/omalizadeh/laravel-query-filter/license)](https://packagist.org/packages/omalizadeh/laravel-query-filter)
[![Total Downloads](https://poser.pugx.org/omalizadeh/laravel-query-filter/downloads)](https://packagist.org/packages/omalizadeh/laravel-query-filter)
# Laravel Query Filter
Laravel query filter provides an elegant way to filter resources via request query string.
You can specify conditions, parameters and relations in query string to filter eloquent models.

## Usage
Install via composer:
```
composer require omalizadeh/laravel-query-filter
```
Make a filter class:
```
php artisan make:filter FilterName
```
Add trait in model:
```php
use Omalizadeh\QueryFilter\Traits\HasFilter;

class Admin extends Model
{
    use HasFilter;
}
```
Set filterable attributes and relations:
```php
<?php

namespace App\Http\Filters;

use Illuminate\Http\Request;
use Omalizadeh\QueryFilter\Filter;

class AdminFilter extends Filter
{
    public function __construct(Request $request)
    {
        parent::__construct($request);
        $this->filterableAttributes = [
            'username',
            'is_active'
        ];
        $this->filterableRelations = [
            'profile' => [
                'gender',
                'first_name',
                'last_name',
                'phone',
                'email',
                'birth_date'
            ]
        ];
        $this->summableAttributes = [];
    }
}
```
Filtering resources from query string in json format (with pagination & sorting):
```
api/admins?filter={"page":{"limit":20,"offset":0},"sort":[{"field":"id","dir":"desc"}],"filters":[[{"field":"is_active","op":"=","value":"="}]]}
```
In Controller:
```php
public function index(AdminFilter $filters)
{
    list($admins, $count) = Admin::filter($filters);
    // count: total resources based on filters
    $admins = $admins->with('profile')->get();
    // do stuff and return response
}
```
### Available Operands
| Operators  | Value | Description |
|---|---|---|
| =  | string/numeric | Equal to value |
| != | string/numeric | Not equal to value |
| <> | string/numeric | Not equal to value |
| like | string | Like string value |
| not like | string | Not like string |
| in | array | Field value is in given array |
| not | NULL/array | Field is not null (for null value)/ Not in given array |
| is | NULL | Field is null |
### Query String Exact Format
Coming Soon...
## License

Laravel Query Filter is open-sourced software licensed under the [MIT license](LICENSE.md).

## Acknowledgments

* This package is based on [Behamin BFilter Package](https://github.com/alirezabahram7/bfilter).
Thanks To [Alireza Bahrami](https://github.com/alirezabahram7) and [Hossein Ebrahimzadeh](https://github.com/Hebrahimzadeh)
