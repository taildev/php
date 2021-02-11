<?php

namespace Tests\Laravel\Trackers;

use Mockery;
use Tests\TestCase;
use Tail\Laravel\Trackers\FrameworkStartupTracker;
use Illuminate\Contracts\Foundation\Application;

class FrameworkStartupTrackerTest extends TestCase
{

    /** @var FrameworkStartupTracker */
    protected $tracker;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracker = new FrameworkStartupTracker();
    }

    public function test_register_starts_a_span_and_listens_for_booting_to_start()
    {
        $expectedStart = 5000;
        define('LARAVEL_START', $expectedStart/1000);

        $app = Mockery::mock(Application::class);
        $app->shouldReceive('booting')->with(Mockery::any())->once();
        $this->tracker->register($app);

        $span = $this->tracker->getSpan();
        $this->assertNotNull($span);
        $this->assertSame('framework', $span->type());
        $this->assertSame('Framework startup', $span->name());
        $this->assertEqualsWithDelta($expectedStart, $span->startTime(), 2);
    }

    public function test_on_start_boot_finishes_the_span()
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('booting')->with(Mockery::any())->once();
        $this->tracker->register($app);
        $this->tracker->onStartBoot($app);
        $expectedEnd = microtime(true) * 1000;

        $span = $this->tracker->getSpan();
        $this->assertEqualsWithDelta($expectedEnd, $span->endTime(), 2);
    }

    public function test_on_start_boot_ignores_missing_span()
    {
        $app = Mockery::mock(Application::class);
        $this->assertNull($this->tracker->getSpan());
        $this->tracker->onStartBoot($app);
    }
}
