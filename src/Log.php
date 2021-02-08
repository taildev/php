<?php

namespace Tail;

use Carbon\Carbon;
use Tail\Logs\LogMeta;

class Log
{

    protected static $meta;

    public static $logs = [];

    public static function meta()
    {
        if (!static::$meta) {
            static::resetMeta();
        }

        return static::$meta;
    }

    /**
     * Clears all existing metadata
     */
    public static function resetMeta()
    {
        self::$meta = new LogMeta();
    }

    public static function emergency($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function alert($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function critical($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function error($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function warning($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function notice($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function info($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function debug($message, array $context = [])
    {
        static::log(__FUNCTION__, $message, $context);
    }

    public static function log($level, $message, array $context = [])
    {
        $log = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
            'time' => Carbon::now()->toIso8601String(),
        ];

        static::$logs[] = $log;
    }

    /**
     * Flush will send and then delete any existing logs
     */
    public static function flush()
    {
        if (count(static::$logs) === 0) {
            return;
        }

        $logs = static::logsWithMetadata();
        if (Tail::logsEnabled()) {
            Tail::client()->sendLogs($logs);
        }

        static::$logs = [];
    }

    protected static function logsWithMetadata()
    {
        if (!static::meta()->service()->name()) {
            static::meta()->service()->setName(Tail::service());
        }

        if (!static::meta()->service()->environment()) {
            static::meta()->service()->setEnvironment(Tail::environment());
        }

        return array_map(function ($log) {
            return array_merge($log, static::meta()->toArray());
        }, static::$logs);
    }
}
