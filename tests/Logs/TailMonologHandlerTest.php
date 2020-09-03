<?php

namespace Tests\Logs;

use Mockery;
use Tail\Log;
use Tests\TestCase;
use Tail\Logs\TailMonologHandler;

class TailMonologHandlerTest extends TestCase
{

    /** @var Log|Mockery\Mock */
    protected $log;

    /** @var TailMonologHandler */
    protected $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->log = Mockery::mock(Log::class);
        $this->handler = new TailMonologHandler($this->log);
    }

    public function test_write()
    {
        $record = [
            'message' => 'some log message',
            'level_name' => 'debug',
            'context' => ['foo' => 'bar'],
        ];

        $this->log->shouldReceive('log')->with('debug', 'some log message', ['foo' => 'bar'])->once();
        $this->handler->write($record);
    }

    public function test_close()
    {
        $this->log->shouldReceive('flush')->once();
        $this->handler->close();
    }
}
