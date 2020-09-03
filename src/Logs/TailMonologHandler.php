<?php

namespace Tail\Logs;

use Tail\Log;
use Monolog\Logger as MonologLogger;
use Monolog\Handler\AbstractProcessingHandler;

class TailMonologHandler extends AbstractProcessingHandler
{

    /** @var Log */
    protected $log;

    public function __construct(Log $log, $level = MonologLogger::DEBUG, bool $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->log = $log;
    }

    public function write(array $record): void
    {
        $level = $record['level_name'];
        $message = $record['message'];
        $context = $record['context'];

        $this->log->log($level, $message, $context);
    }

    public function close(): void
    {
        $this->log->flush();
    }
}
