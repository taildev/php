<?php
namespace Tail\Error;

class Trace
{
    /** @var array Trace of files */
    protected $trace = [];

    public function __construct(array $trace)
    {
        foreach ($trace as $t) {
            $traceArr = [
                'file' => isset($t['file']) ? $t['file'] : '',
                'line' => isset($t['line']) ? $t['line'] : '',
                'function' => isset($t['function']) ? $t['function'] : '',
                'class' => isset($t['class']) ? $t['class'] : '',
                'type' => isset($t['type']) ? $t['type'] : '',
                'context' => isset($t['file']) && isset($t['line']) ? $this->getContext($t['file'], $t['line']) : [], // get 5 lines before and after and main line
                'args' => [],
            ];

            foreach ($t['args'] as $arg) {
                if (is_array($arg)) {
                    $traceArr['args'][] = json_encode($arg);
                } else if (is_object($arg)) {
                    $traceArr['args'][] = get_class($arg);
                } else {
                    $traceArr['args'][] = $arg;
                }
            }

            $this->trace[] = $traceArr;
        }
    }

    private function getContext($path, $targetLineNumber): array
    {
        if (!is_numeric($targetLineNumber)) {
            return [];
        }
        $lineContext = [
            'context_before' => [],
            'context_line' => '',
            'context_after' => [],
        ];

        $firstLineToRead = max(0, $targetLineNumber - 6);
        $currentLineNumber = $firstLineToRead + 1;

        $file = new \SplFileObject($path);
        $file->seek($firstLineToRead);
        while (!$file->eof()) {
            $line = $file->current();
            $line = rtrim($line, "\r\n");

            if ($currentLineNumber == $targetLineNumber) {
                $lineContext['context_line'] = $line;
            } elseif ($currentLineNumber < $targetLineNumber) {
                $lineContext['context_before'][] = $line;
            } elseif ($currentLineNumber > $targetLineNumber) {
                $lineContext['context_after'][] = $line;
            }

            $currentLineNumber++;

            if ($currentLineNumber > $targetLineNumber + 5) {
                break;
            }

            $file->next();
        }

        return $lineContext;
    }

    public function toArray(): array
    {
        return $this->trace;
    }
}
