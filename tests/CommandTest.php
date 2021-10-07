<?php

namespace Omalizadeh\QueryFilter\Tests;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class CommandTest extends TestCase
{
    /** @test */
    public function makeFilterCommandCreatesFilterClassTest(): void
    {
        $filterFileName = 'TestFilter.php';
        if (File::exists($this->getFilterPath($filterFileName))) {
            unlink($this->getFilterPath($filterFileName));
        }
        $this->assertFalse(File::exists($this->getFilterPath($filterFileName)));
        Artisan::call('make:filter TestFilter');
        $this->assertTrue(File::exists($this->getFilterPath($filterFileName)));
    }
}
