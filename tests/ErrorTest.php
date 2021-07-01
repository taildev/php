<?php

namespace Tests;

use Exception;
use Mockery;
use Tail\Client;
use Tail\Error;
use Tail\Tail;

class ErrorTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Tail::$initialized = false;
        Error::$errors = [];
    }

    public function test_send_error()
    {
        // Arrange
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('sendErrors')->once();
        Tail::init(['errors_enabled' => true]);
        Tail::setClient($client);

        // Act
        Error::capture(new Exception());
        Error::send();

        // Assert
        $this->assertCount(0, Error::$errors);
    }

    public function test_error_data()
    {
        // Act
        Error::capture(new Exception('Your cars extended warrenty is about to expire'));

        // Assert
        $firstError = Error::$errors[0];
        $this->assertSame($firstError['exception'], Exception::class);
        $this->assertSame($firstError['message'], 'Your cars extended warrenty is about to expire');
        $this->assertSame($firstError['file'], __FILE__);
        $this->assertIsNumeric($firstError['line']);
        $this->assertSame($firstError['runtime'], 'php');
        $this->assertSame($firstError['runtime_version'], phpversion());
        $this->assertEqualsWithDelta(strtotime($firstError['time']), time(), 1);
        $this->assertIsArray($firstError['trace']);
    }

    public function test_send_errors_only_sends_errors_if_present()
    {
        // Arrange
        Tail::init();
        $client = Mockery::mock(Client::class);
        Tail::setClient($client);

        // Act
        Error::send();

        // Assert
        $client->shouldNotReceive('sendErrors');
    }

    public function test_send_errors_doesnt_send_errors_if_error_reporting_is_disabled()
    {
        // Arrange
        Error::capture(new Exception());
        Tail::init(['errors_enabled' => false]);
        $client = Mockery::mock(Client::class);
        Tail::setClient($client);

        // Act
        Error::send();

        // Assert
        $client->shouldNotReceive('sendErrors');
    }

    public function test_send_errors_attaches_metadata_to_each_record()
    {
        // Arrange / Assert
        Error::capture(new Exception());
        Error::capture(new Exception());

        $expected = array_map(function ($error) {
            return array_merge($error, Tail::meta()->toArray());
        }, Error::$errors);

        Tail::init(['errors_enabled' => true]);
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('sendErrors')->with($expected)->once();

        Tail::setClient($client);

        // Act
        Error::send();
    }
}
