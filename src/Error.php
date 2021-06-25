<?php
namespace Tail;

use Tail\Error\Trace;
use Tail\Meta\Cookies;
use Tail\Meta\Http;

class Error
{
    public static $error;

    /**
     * Capture the exception
     */
    public static function capture($e)
    {
        $error = [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'runtime' => 'php',
            'runtime_version' => phpversion(),
            'time' => gmdate(DATE_ATOM),
            'http' => (new Http())->toArray(),
            'cookies' => (new Cookies())->toArray(),
            'trace' => (new Trace($e->getTrace()))->toArray(),
        ];

        static::$error = $error;

        if (Tail::errorsEnabled()) {
            Tail::client()->sendError(static::$error);
        }
    }
}
