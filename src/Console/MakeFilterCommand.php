<?php

namespace Omalizadeh\QueryFilter\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputArgument;

class MakeFilterCommand extends GeneratorCommand
{
    protected $signature = 'make:filter {name}';
    protected $type = "Filter";

    protected function getStub()
    {
        return __DIR__ . '/stubs/filter.php.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Filters';
    }

    protected function getArguments()
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the filter class.'],
        ];
    }
}
