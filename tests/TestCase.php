<?php

namespace Tests;

use Mockery;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class TestCase extends PHPUnitTestCase
{

    use MockeryPHPUnitIntegration;

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
