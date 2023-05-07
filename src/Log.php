<?php

namespace Tail;

use stdClass;

class Log
{
    public static $logs = [];

    public static function emergency(string $message, array $tags = [])
    {
        static::log(__FUNCTION__, $message, $tags);
    }

    public static function alert(string $message, array $tags = [])
    {
        static::log(__FUNCTION__, $message, $tags);
    }

    public static function critical(string $message, array $tags = [])
    {
        static::log(__FUNCTION__, $message, $tags);
    }

    public static function error(string $message, array $tags = [])
    {
        static::log(__FUNCTION__, $message, $tags);
    }

    public static function warning(string $message, array $tags = [])
    {
        static::log(__FUNCTION__, $message, $tags);
    }

    public static function notice(string $message, array $tags = [])
    {
        static::log(__FUNCTION__, $message, $tags);
    }

    public static function info(string $message, array $tags = [])
    {
        static::log(__FUNCTION__, $message, $tags);
    }

    public static function debug(string $message, array $tags = [])
    {
        static::log(__FUNCTION__, $message, $tags);
    }

    public static function log(string $level, string $message, array $tags = [])
    {
        $log = [
            '@timestamp' => gmdate(DATE_ATOM),
            'message' => $message,
            'level' => $level,
            'tags' => $tags,
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
            $tags = array_merge(Tail::meta()->tags()->all(), $log['tags']);
            if ($tags === []) {
                $tags = new stdClass();
            }

            return array_merge($log, [
                'tags' => $tags,
                'service' => Tail::meta()->service()->serialize(),
                'system' => Tail::meta()->system()->serialize(),
            ]);
        }, static::$logs);
    }
}
