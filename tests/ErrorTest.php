<?php

namespace Tests;

use Exception;
use Mockery;
use Tail\Client;
use Tail\Error;
use Tail\Meta\Http;
use Tail\Tail;

class ErrorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Tail::$initialized = false;
    }

    public function test_send_error()
    {
        // Arrange
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('sendError')
            ->withAnyArgs()->once();
        Tail::init(['errors_enabled' => true]);
        Tail::setClient($client);

        // Act
        Error::capture(new Exception());
    }

    public function test_error()
    {
        // Arrange
        Tail::init(['errors_enabled' => false]);

        // Act
        Error::capture(new Exception('Your cars extended warrenty is about to expire'));

        // Assert
        $this->assertSame(Error::$error['exception'], Exception::class);
        $this->assertSame(Error::$error['message'], 'Your cars extended warrenty is about to expire');
        $this->assertSame(Error::$error['file'], __FILE__);
        $this->assertIsNumeric(Error::$error['line']);
        $this->assertSame(Error::$error['runtime'], 'php');
        $this->assertSame(Error::$error['runtime_version'], phpversion());
        $this->assertEqualsWithDelta(strtotime(Error::$error['time']), time(), 1);
        $this->assertSame(Error::$error['http'], (new Http())->toArray());
        $this->assertSame(Error::$error['cookies'], $_COOKIE);
        $this->assertIsArray(Error::$error['trace']);
    }
}
