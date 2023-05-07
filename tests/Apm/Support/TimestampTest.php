<?php

namespace Tests\Apm\Support;

use Tests\TestCase;
use Tail\Apm\Support\Timestamp;

class TimestampTest extends TestCase
{
    public function test_now_in_ms()
    {
        $expected = ceil(microtime(true) * 1000);
        $actual = Timestamp::nowInMs();
        $tolerance = $actual - $expected;
        $this->assertEqualsWithDelta($expected, $actual, $tolerance);
    }
}
