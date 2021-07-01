<?php

namespace Tail;

use GuzzleHttp\Client as Guzzle;

class Client
{
    public const ERRORS_ENDPOINT = 'https://api.tail.dev/ingest/errors';
    public const LOGS_ENDPOINT = 'https://api.tail.dev/ingest/logs';
    public const APM_ENDPOINT = 'https://api.tail.dev/ingest/transactions';

    protected $token;

    /** @var Guzzle */
    protected $guzzle;

    protected $errorSendHandlers = [];

    protected $logSendHandlers = [];

    protected $apmSendHandlers = [];

    public function __construct($token, ?Guzzle $guzzle = null)
    {
        $this->token = $token;
        $this->guzzle = $guzzle ?? new Guzzle();
        $this->registerDefaultErrorSendHandler();
        $this->registerDefaultLogSendHandler();
        $this->registerDefaultApmSendHandler();
    }
    public function registerErrorSendHandler($handler)
    {
        $this->errorSendHandlers[] = $handler;
    }

    public function registerLogSendHandler($handler)
    {
        $this->logSendHandlers[] = $handler;
    }

    public function registerApmSendHandler($handler)
    {
        $this->apmSendHandlers[] = $handler;
    }

    public function sendErrors(array $errors)
    {
        $handlers = array_reverse($this->errorSendHandlers);
        foreach ($handlers as $handler) {
            if ($handler($errors) === false) {
                return;
            }
        }
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

    // @todo remove
    public function getToken()
    {
        return $this->token;
    }

    public function token()
    {
        return $this->token;
    }

    private function registerDefaultErrorSendHandler()
    {
        $this->registerErrorSendHandler(function (array $errors) {
            $url = getenv('TAIL_ERRORS_ENDPOINT') ?: self::ERRORS_ENDPOINT;

            $this->guzzle->post($url, [
                'json' => $errors,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);
        });
    }

    private function registerDefaultLogSendHandler()
    {
        $this->registerLogSendHandler(function (array $logs) {
            $url = getenv('TAIL_LOGS_ENDPOINT') ?: self::LOGS_ENDPOINT;

            $this->guzzle->post($url, [
                'json' => $logs,
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
                'json' => $transaction,
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                ],
            ]);
        });
    }
}
