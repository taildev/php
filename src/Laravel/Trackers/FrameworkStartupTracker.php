<?php

namespace Tail\Laravel\Trackers;

use Tail\Apm;
use Tail\Apm\Span;
use Illuminate\Contracts\Foundation\Application;

class FrameworkStartupTracker implements Tracker
{
    protected ?Span $span = null;

    public function register(Application $app)
    {
        $start = defined('LARAVEL_START') ? LARAVEL_START * 1000 : microtime(true) * 1000;
        $this->span = Apm::newSpan('Framework startup', 'framework')->setStartTime($start);

        $app->booting(function ($app) {
            $this->onStartBoot($app);
        });
    }

    public function onStartBoot(Application $app)
    {
        if (!$this->span) {
            return;
        }

        $this->span->finish();
    }

    public function getSpan(): ?Span
    {
        return $this->span;
    }
}
