<?php

namespace Tests\Meta;

use Tests\TestCase;
use Tail\Meta\Service;

class ServiceTest extends TestCase
{

    /** @var Service */
    protected $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = new Service('xx');
    }

    public function test_construct_with_properties()
    {
        $service = new Service('foo', 'foo-env');

        $this->assertSame('foo', $service->name());
        $this->assertSame('foo-env', $service->environment());
    }

    public function test_set_name()
    {
        $result = $this->service->setName('foo');
        $this->assertSame($this->service, $result);
        $this->assertSame('foo', $this->service->name());
    }

    public function test_set_environment()
    {
        $result = $this->service->setEnvironment('foo-env');
        $this->assertSame($this->service, $result);
        $this->assertSame('foo-env', $this->service->environment());
    }

    public function test_merge()
    {
        $service = new Service();
        $service->setEnvironment('env 1');
        $service->setName('name 1');

        $service->merge(['environment' => 'env 2']);

        $this->assertSame(['name' => 'name 1', 'environment' => 'env 2'], $service->toArray());
    }

    public function test_output_to_array()
    {
        $service = new Service('foo', 'foo-env');
        $expect = [
            'name' => 'foo',
            'environment' => 'foo-env',
        ];

        $this->assertSame($expect, $service->toArray());
    }
}
