<?php
namespace Tail;

use Tail\Error\Trace;

class Error
{
    public static $errors = [];

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
            'trace' => (new Trace($e->getTrace()))->toArray(),
        ];

        static::$errors[] = $error;
    }

    /**
     * Send and then delete any existing errors
     */
    public static function send()
    {
        if (count(static::$errors) === 0) {
            return;
        }

        $errors = static::errorsWithMetadata();
        if (Tail::errorsEnabled()) {
            Tail::client()->sendErrors($errors);
        }

        static::$errors = [];
    }

    protected static function errorsWithMetadata()
    {
        return array_map(function ($error) {
            return array_merge($error, Tail::meta()->toArray());
        }, static::$errors);
    }
}
