<?php

namespace Tail;

use Carbon\Carbon;

class Log
{

    public static $logs = [];

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
        return array_map(function ($log) {
            return array_merge($log, Tail::meta()->toArray());
        }, static::$logs);
    }
}
