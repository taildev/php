<?php

namespace Tests;

use Mockery;
use Tail\Laravel\TailServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class TestCase extends OrchestraTestCase
{

    use MockeryPHPUnitIntegration;

    protected function getPackageProviders($app)
    {
        return [
            TailServiceProvider::class,
        ];
    }

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
