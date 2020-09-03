<?php

namespace Tail;

use GuzzleHttp\Client as Guzzle;

class Client
{

    const LOGS_ENDPOINT = 'https://api.tail.dev/ingest/logs';

    protected $token;

    /** @var Guzzle */
    protected $guzzle;

    public function __construct(string $token, ?Guzzle $guzzle = null)
    {
        $this->token = $token;
        $this->guzzle = $guzzle ?? new Guzzle();
    }

    public function sendLogs(array $logs)
    {
        $encoded = [];
        foreach ($logs as $log) {
            $encoded[] = json_encode($log);
        }

        $payload = implode("\n", $encoded);

        $this->guzzle->post(
            self::LOGS_ENDPOINT,
            [
                'body' => $payload,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]
        );
    }

    public function getToken()
    {
        return $this->token;
    }
}
