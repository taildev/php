<?php

namespace Tail;

use Carbon\Carbon;
use Psr\Log\LoggerInterface;

class Log implements LoggerInterface
{

    public static $instance;

    /**
     * Initialize a new logger instance
     * 
     * @param string $token Auth token for tail.dev
     * @param string|null $serviceName Name of service being logged
     * @param string|null $serviceEnvironment Environment of service being logged
     * @return Log
     */
    public static function init(string $token, $serviceName = null, $serviceEnvironment = null)
    {
        $client = new Client($token);
        self::$instance = new Log($client, $serviceName, $serviceEnvironment);

        register_shutdown_function([self::$instance, 'flush']);

        return self::$instance;
    }

    /**
     * Get logger instance. Be sure to call init() first.
     * 
     * @return Log|null 
     */
    public static function get()
    {
        return self::$instance;
    }

    /** @var Client */
    protected $client;

    /** @var string|null */
    protected $serviceName;

    /** @var string|null */
    protected $serviceEnvironment;

    protected $logs = [];

    public function __construct(Client $client, $serviceName = null, $serviceEnvironment = null)
    {
        $this->client = $client;
        $this->serviceName = $serviceName;
        $this->serviceEnvironment = $serviceEnvironment;
    }

    public function emergency($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function alert($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function critical($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function error($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function warning($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function notice($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function info($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function debug($message, array $context = [])
    {
        $this->log(__FUNCTION__, $message, $context);
    }

    public function log($level, $message, array $context = [])
    {
        $log = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'time' => Carbon::now()->toIso8601String(),
        ];

        if (! is_null($this->serviceName)) {
            $log['service_name'] = $this->serviceName;
        }

        if (! is_null($this->serviceEnvironment)) {
            $log['service_environment'] = $this->serviceEnvironment;
        }

        $this->logs[] = $log;
    }

    public function getLogs(): array
    {
        return $this->logs;
    }

    public function setServiceName(?string $name)
    {
        $this->serviceName = $name;
    }

    public function getServiceName()
    {
        return $this->serviceName;
    }

    public function setServiceEnvironment(?string $env)
    {
        $this->serviceEnvironment = $env;
    }

    public function getServiceEnvironment()
    {
        return $this->serviceEnvironment;
    }

    /**
     * Get tail.dev client
     * 
     * @return Client 
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Flush will send and then delete any existing logs
     */
    public function flush()
    {
        $logs = $this->getLogs();
        if (count($logs) === 0) {
            return;
        }

        $this->client->sendLogs($logs);

        $this->logs = [];
    }
}
