<?php

namespace Tests\Laravel\Trackers;

use Mockery;
use Tests\TestCase;
use Tail\Laravel\LaravelApm;
use Tail\Laravel\Trackers\FrameworkBootTracker;
use Illuminate\Contracts\Foundation\Application;

class FrameworkBootTrackerTest extends TestCase
{
    /** @var FrameworkBootTracker */
    protected $tracker;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracker = new FrameworkBootTracker();
    }

    public function test_on_start_boot_creates_a_span()
    {
        $app = Mockery::mock(Application::class);
        $this->tracker->onStartBoot($app);
        $expectedStart = microtime(true) * 1000;

        $span = $this->tracker->getSpan();
        $this->assertNotNull($span);
        $this->assertSame('framework', $span->type());
        $this->assertSame('Framework boot', $span->name());
        $this->assertEqualsWithDelta($expectedStart, $span->startTime(), 2);
    }

    public function test_on_finish_boot_marks_the_span_as_complete()
    {
        $app = Mockery::mock(Application::class);
        $this->tracker->onStartBoot($app);
        $this->tracker->onFinishBoot($app);
        $expectedEnd = microtime(true) * 1000;

        $span = $this->tracker->getSpan();
        $this->assertEqualsWithDelta($expectedEnd, $span->endTime(), 2);
    }

    public function test_on_finish_ignores_missing_span()
    {
        $app = Mockery::mock(Application::class);
        $this->assertNull($this->tracker->getSpan());
        $this->tracker->onFinishBoot($app);
    }
}
