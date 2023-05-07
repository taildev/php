<?php

namespace Tests\Meta;

use stdClass;
use Tests\TestCase;
use Tail\Meta\Agent;

class AgentTest extends TestCase
{
    protected $agent;

    public function test_create_default()
    {
        $agent = new Agent();
        $this->assertSame('tail-php', $agent->name());
        $this->assertSame('php', $agent->type());
        $this->assertNotEmpty($agent->version());
    }

    public function test_fill_from_array()
    {
        $agent = new Agent();
        $agent->fillFromArray([
            'name' => 'custom-name',
            'type' => 'custom-type',
            'version' => 'custom-version',
        ]);

        $this->assertSame('custom-name', $agent->name());
        $this->assertSame('custom-type', $agent->type());
        $this->assertSame('custom-version', $agent->version());
    }

    public function test_merge()
    {
        $agent = new Agent();
        $agent->setName('name 1');
        $agent->setType('type 1');
        $agent->setVersion('version 1');

        $agent->merge(['name' => 'name 2', 'type' => 'type 2']);

        $this->assertSame(['name' => 'name 2', 'type' => 'type 2', 'version' => 'version 1'], $agent->serialize());
    }

    public function test_serialize()
    {
        $agent = new Agent();
        $agent->setName('tail-testing');
        $agent->setType('php');
        $agent->setVersion('v1');

        $expect = [
            'name' => 'tail-testing',
            'type' => 'php',
            'version' => 'v1',
        ];

        $this->assertSame($expect, $agent->serialize());
    }

    public function test_serialize_partial()
    {
        $agent = new Agent();
        $agent->setName('tail-testing');
        $agent->setType('php');
        $agent->setVersion(null);

        $expect = [
            'name' => 'tail-testing',
            'type' => 'php',
        ];

        $this->assertSame($expect, $agent->serialize());
    }

    public function test_serialize_empty()
    {
        $agent = new Agent();
        $agent->setName(null);
        $agent->setType(null);
        $agent->setVersion(null);

        $expect = new stdClass();

        $this->assertEquals($expect, $agent->serialize());
    }
}
