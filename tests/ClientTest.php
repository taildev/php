<?php

namespace Tests;

use Mockery;
use Tail\Client;

class ClientTest extends TestCase
{

    /** @var Client */
    protected $client;

    public function setUp(): void
    {
        $this->client = new Client('secret_token');
    }

    // public function test_send_logs()
    // {
    //     $logs = [
    //         ['message' => 'message 1'],
    //         ['message' => "message \n2"],
    //     ];

    //     $expectEncoded = "{\"message\":\"message 1\"}\n{\"message\":\"message \\n2\"}";

    //     $this->guzzle->shouldReceive('post')->with(
    //         Client::LOGS_ENDPOINT,
    //         [
    //             'body' => $expectEncoded,
    //             'headers' => [
    //                 'Authorization' => 'Bearer secret_token',
    //             ],
    //         ]
    //     )->once();

    //     $this->client->sendLogs($logs);
    // }

    public function test_register_a_custom_log_send_handler()
    {
        global $v;
        $v = false;
        $handler = function () {
            global $v;
            $v = true;
            return false;
        };

        $this->client->registerLogSendHandler($handler);
        $this->client->sendLogs([]);
        $this->assertTrue($v);
    }

    // public function test_send_apm()
    // {
    //     $transaction = ['some' => 'data'];
    //     $expectEncoded = "{\"some\":\"data\"}";

    //     $this->guzzle->shouldReceive('post')->with(
    //         Client::APM_ENDPOINT,
    //         [
    //             'body' => $expectEncoded,
    //             'headers' => [
    //                 'Authorization' => 'Bearer secret_token',
    //             ],
    //         ]
    //     )->once();

    //     $this->client->sendApm($transaction);
    // }

    public function test_register_a_custom_apm_send_handler()
    {
        global $v;
        $v = false;
        $handler = function () {
            global $v;
            $v = true;
            return false;;
        };

        $this->client->registerApmSendHandler($handler);
        $this->client->sendApm([]);
        $this->assertTrue($v);
    }
}
