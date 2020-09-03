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

        $expectedPayload = '{"message":"message 1"}
{"message":"message \n2"}';

        $this->guzzle->shouldReceive('post')->with(
            Client::LOGS_ENDPOINT,
            [
                'body' => $expectedPayload,
                'headers' => [
                    'Authorization' => 'Bearer secret_token',
                ],
            ]
        )->once();

        $this->client->sendLogs($logs);
    }
}
