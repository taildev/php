<?php

namespace Tests\Laravel\Trackers;

use Mockery;
use Tail\Apm;
use Tests\TestCase;
use Tail\Laravel\Trackers\ArtisanTracker;
use Illuminate\Contracts\Foundation\Application;

class ArtisanTrackerTest extends TestCase
{

    public function test_ignores_non_console_transactions()
    {
        $tracker = Mockery::mock(ArtisanTracker::class)->makePartial();
        $tracker->shouldNotReceive('registerArtisan');

        $app = Mockery::mock(Application::class);
        $app->shouldReceive('runningInConsole')->andReturn(false);
        $tracker->register($app);
    }

    public function test_registers_artisan_transaction()
    {
        $app = Mockery::mock(Application::class);
        $app->shouldReceive('runningInConsole')->andReturn(true);
        $tracker = new ArtisanTracker();
        $tracker->register($app);

        $type = Apm::transaction()->type();
        $this->assertSame('artisan', $type);

        $expectedName = join(' ', $_SERVER['argv']);
        $name = Apm::transaction()->name();
        $this->assertSame($expectedName, $name);
    }
}
