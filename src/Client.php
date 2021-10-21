<?php

namespace Tail;

use GuzzleHttp\Client as Guzzle;

class Client
{
    public const LOGS_ENDPOINT = 'https://ingest.tail.dev/logs';
    public const APM_ENDPOINT = 'https://ingest.tail.dev/traces';

    protected $token;

    /** @var Guzzle */
    protected $guzzle;

    protected $logSendHandlers = [];

    protected $apmSendHandlers = [];

    public function __construct($token, ?Guzzle $guzzle = null)
    {
        $this->token = $token;
        $this->guzzle = $guzzle ?? new Guzzle();
        $this->registerDefaultLogSendHandler();
        $this->registerDefaultApmSendHandler();
    }

    public function registerLogSendHandler($handler)
    {
        $this->logSendHandlers[] = $handler;
    }

    public function registerApmSendHandler($handler)
    {
        $this->apmSendHandlers[] = $handler;
    }

    public function sendLogs(array $logs)
    {
        $handlers = array_reverse($this->logSendHandlers);
        foreach ($handlers as $handler) {
            if ($handler($logs) === false) {
                return;
            }
        }
    }

    public function sendApm(array $transaction)
    {
        $handlers = array_reverse($this->apmSendHandlers);
        foreach ($handlers as $handler) {
            if ($handler($transaction) === false) {
                return;
            }
        }
    }

    public function token()
    {
        return $this->token;
    }

    private function registerDefaultLogSendHandler()
    {
        $this->registerLogSendHandler(function (array $logs) {
            $url = getenv('TAIL_LOGS_ENDPOINT') ?: self::LOGS_ENDPOINT;

            $encodedLogs = array_map(function ($log) {
                return json_encode($log);
            }, $logs);

            $this->guzzle->post($url, [
                'body' => implode("\n", $encodedLogs),
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);
        });
    }

    private function registerDefaultApmSendHandler()
    {
        $this->registerApmSendHandler(function ($transaction) {
            $url = getenv('TAIL_APM_ENDPOINT') ?: self::APM_ENDPOINT;

            $this->guzzle->post($url, [
                'body' => json_encode($transaction),
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);
        });
    }
}
