<?php

namespace Omalizadeh\QueryFilter;

use Illuminate\Support\ServiceProvider;
use Omalizadeh\QueryFilter\Console\Filter;

class QueryFilterServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Filter::class
            ]);
        }
    }
}
