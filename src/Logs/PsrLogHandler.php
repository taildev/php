<?php

namespace Tail\Logs;

use Tail\Log;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class PsrLogHandler implements LoggerInterface
{
    use LoggerTrait;

    /**
     * Logs with an arbitrary level.
     *
     * @param mixed   $level
     * @param string  $message
     * @param array $context
     *
     * @return void
     */
    public function log($level, $message, array $context = array()): void
    {
        Log::log($level, $message, $context);
    }
}
