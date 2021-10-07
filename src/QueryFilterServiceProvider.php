<?php

namespace Omalizadeh\QueryFilter;

use Illuminate\Support\ServiceProvider;
use Omalizadeh\QueryFilter\Console\MakeFilterCommand;

class QueryFilterServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeFilterCommand::class
            ]);
        }
    }
}
