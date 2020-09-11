<?php

namespace Tests\Apm\Meta;

use Tests\TestCase;
use Tail\Apm\Meta\Agent;

class AgentTest extends TestCase
{

    protected $agent;

    public function test_create_default()
    {
        $agent = Agent::createDefault();
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


    public function test_output_to_array()
    {
        $agent = Agent::createDefault();

        $this->assertSame('tail-php', $agent->toArray()['name']);
        $this->assertSame('php', $agent->toArray()['type']);
        $this->assertNotEmpty($agent->toArray()['version']);
    }
}
