<?php

namespace Tests\Meta;

use stdClass;
use Tests\TestCase;
use Tail\Meta\System;

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

    public function test_merge()
    {
        $system = new System();
        $system->setHostname('host 1');

        $system->merge(['hostname' => 'host 2']);

        $this->assertSame(['hostname' => 'host 2'], $system->serialize());
    }

    public function test_serialize()
    {
        $system = new System();
        $system->setHostname('foo-host');

        $expect = [
            'hostname' => 'foo-host',
        ];

        $this->assertSame($expect, $system->serialize());
    }

    public function test_serialize_empty()
    {
        $system = new System();
        $system->setHostname(null);

        $expect = new stdClass();

        $this->assertEquals($expect, $system->serialize());
    }
}
