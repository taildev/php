<?php

namespace Tests\Meta;

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

        $this->assertSame(['name' => 'name 2', 'type' => 'type 2', 'version' => 'version 1'], $agent->toArray());
    }

    public function test_output_to_array()
    {
        $agent = new Agent();

        $this->assertSame('tail-php', $agent->toArray()['name']);
        $this->assertSame('php', $agent->toArray()['type']);
        $this->assertNotEmpty($agent->toArray()['version']);
    }
}
