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

        $url = getenv('TAIL_LOGS_ENDPOINT') ?: self::LOGS_ENDPOINT;
        $payload = implode("\n", $encoded);

        $this->guzzle->post(
            $url,
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
