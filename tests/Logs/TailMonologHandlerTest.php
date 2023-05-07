<?php

namespace Tests\Logs;

use Mockery;
use Tail\Log;
use Tail\Tail;
use Tail\Client;
use Tests\TestCase;
use Tail\Logs\TailMonologHandler;

class TailMonologHandlerTest extends TestCase
{
    protected $handler;

    public function setUp(): void
    {
        parent::setUp();
        Log::$logs = [];
        $this->handler = new TailMonologHandler();
    }

    public function test_write()
    {
        $record = [
            'message' => 'some log message',
            'level_name' => 'debug',
            'context' => ['foo' => 'bar'],
        ];

        $this->handler->write($record);
        $this->assertSame($record['message'], Log::$logs[0]['message']);
        $this->assertSame($record['level_name'], Log::$logs[0]['level']);
        $this->assertSame($record['context'], Log::$logs[0]['tags']);
    }

    public function test_close()
    {
        Tail::init();
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('sendLogs');
        Tail::setClient($client);

        $this->handler->write(['message' => 'some log message', 'level_name' => 'debug']);
        $this->handler->close();
        $this->assertEmpty(Log::$logs);
    }
}
