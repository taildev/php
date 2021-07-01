<?php

namespace Tests\Error;

use Tail\Error\Trace;
use Tests\TestCase;

class TraceTest extends TestCase
{
    public function test_trace()
    {
        // Arrange
        $exceptionTrace = [
            'file' => __FILE__,
            'function' => 'test_trace',
            'class' => get_class($this),
            'type' => '->',
            'line' => __LINE__,
            'args' => [[], 24],
        ];

        // Act
        $trace = new Trace([$exceptionTrace]);
        $firstTrace = $trace->toArray()[0];

        // Assert
        $this->assertSame($exceptionTrace['file'], $firstTrace['file']);
        $this->assertSame($exceptionTrace['line'], $firstTrace['line']);
        $this->assertSame($exceptionTrace['class'], $firstTrace['class']);
        $this->assertSame($exceptionTrace['type'], $firstTrace['type']);
        $this->assertSame($exceptionTrace['function'], $firstTrace['function']);
        $this->assertSame(['[]', 24], $firstTrace['args']);

        $this->assertCount(5, $firstTrace['context']['context_before']);
        $this->assertCount(5, $firstTrace['context']['context_after']);

        $this->assertSame("            'type' => '->',", $firstTrace['context']['context_before'][4]);
        $this->assertSame("            'line' => __LINE__,", $firstTrace['context']['context_line']);
        $this->assertSame("            'args' => [[], 24],", $firstTrace['context']['context_after'][0]);
    }
}
