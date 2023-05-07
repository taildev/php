<?php

namespace Tail;

class Client
{
    public const LOGS_ENDPOINT = 'https://ingest.tail.dev/logs';
    public const APM_ENDPOINT = 'https://ingest.tail.dev/traces';

    protected ?string $token = null;

    protected array $logSendHandlers = [];

    protected array $apmSendHandlers = [];

    public function __construct(?string $token)
    {
        $this->token = $token;
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

    public function token(): ?string
    {
        return $this->token;
    }

    private function registerDefaultLogSendHandler()
    {
        $this->registerLogSendHandler(function (array $logs) {
            $url = getenv('TAIL_LOGS_ENDPOINT') ?: self::LOGS_ENDPOINT;
            $headers = ['Authorization: Bearer ' . $this->token];

            $encodedLogs = array_map(function ($log) {
                return json_encode($log);
            }, $logs);
            $body = implode("\n", $encodedLogs);

            $this->postRequest($url, $body, $headers);
        });
    }

    private function registerDefaultApmSendHandler()
    {
        $this->registerApmSendHandler(function ($transaction) {
            $url = getenv('TAIL_APM_ENDPOINT') ?: self::APM_ENDPOINT;
            $body = json_encode($transaction);
            $headers = ['Authorization: Bearer ' . $this->token];
            $this->postRequest($url, $body, $headers);
        });
    }

    private function postRequest(string $url, string $body, array $headers)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        curl_exec($curl);
        curl_close($curl);
    }
}
