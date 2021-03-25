<?php

namespace Tests;

use Mockery;
use Tail\Client;
use GuzzleHttp\Client as Guzzle;

class ClientTest extends TestCase
{

    /** @var Guzzle|Mockery\Mock */
    protected $guzzle;

    /** @var Client */
    protected $client;

    public function setUp(): void
    {
        $this->guzzle = Mockery::mock(Guzzle::class);
        $this->client = new Client('secret_token', $this->guzzle);
    }

    public function test_send_logs()
    {
        $logs = [
            ['message' => 'message 1'],
            ['message' => "message \n2"],
        ];

        $this->guzzle->shouldReceive('post')->with(
            Client::LOGS_ENDPOINT,
            [
                'json' => $logs,
                'headers' => [
                    'Authorization' => 'Bearer secret_token',
                ],
            ]
        )->once();

        $this->client->sendLogs($logs);
    }

    public function test_register_a_custom_log_send_handler()
    {
        $this->guzzle->shouldReceive('post')->once();

        global $v;
        $v = false;
        $handler = function () {
            global $v;
            $v = true;
        };

        $this->client->registerLogSendHandler($handler);
        $this->client->sendLogs([]);
        $this->assertTrue($v);
    }

    public function test_stop_log_sending_chain_with_handler_that_returns_false()
    {
        $handler = function () {
            return false;
        };

        $this->guzzle->shouldNotReceive('post');
        $this->client->registerLogSendHandler($handler);
        $this->client->sendLogs([]);
    }

    public function test_send_apm()
    {
        $transaction = ['some' => 'data'];

        $this->guzzle->shouldReceive('post')->with(
            Client::APM_ENDPOINT,
            [
                'json' => $transaction,
                'headers' => [
                    'Authorization' => 'Bearer secret_token',
                ],
            ]
        )->once();

        $this->client->sendApm($transaction);
    }

    public function test_register_a_custom_apm_send_handler()
    {
        $this->guzzle->shouldReceive('post')->once();

        global $v;
        $v = false;
        $handler = function () {
            global $v;
            $v = true;
        };

        $this->client->registerApmSendHandler($handler);
        $this->client->sendApm([]);
        $this->assertTrue($v);
    }

    public function test_stop_apm_sending_chain_with_handler_that_returns_false()
    {
        $handler = function () {
            return false;
        };

        $this->guzzle->shouldNotReceive('post');
        $this->client->registerApmSendHandler($handler);
        $this->client->sendApm([]);
    }
}
