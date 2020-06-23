<?php

namespace Tests\Apm\Meta;

use Tests\TestCase;
use Tail\Apm\Meta\System;

class SystemTest extends TestCase
{

    protected $system;

    public function setUp(): void
    {
        parent::setUp();
        $this->system = new System();
    }

    public function test_constructed_with_default_hostname()
    {
        $system = new System();
        $this->assertSame(gethostname(), $system->hostname());
    }

    public function test_fill_from_array()
    {
        $system = new System();
        $system->fillFromArray(['hostname' => 'custom-hostname']);
        $this->assertSame('custom-hostname', $system->hostname());
    }

    public function test_set_hostname()
    {
        $result = $this->system->setHostname('foo-host');
        $this->assertSame($this->system, $result);
        $this->assertSame('foo-host', $this->system->hostname());
    }

    public function test_output_to_array()
    {
        $system = new System();
        $system->setHostname('foo-host');

        $expect = [
            'hostname' => 'foo-host',
        ];

        $this->assertSame($expect, $system->toArray());
    }
}
