<?php

namespace Tail\Logs;

use Tail\Log;
use Monolog\Handler\AbstractProcessingHandler;

class TailMonologHandler extends AbstractProcessingHandler
{

    public function write(array $record): void
    {
        $level = $record['level_name'];
        $message = $record['message'];
        $context = [];

        if (array_key_exists('context', $record)) {
            $context = $record['context'];
        }

        Log::log($level, $message, $context);
    }

    public function close(): void
    {
        Log::flush();
    }
}
