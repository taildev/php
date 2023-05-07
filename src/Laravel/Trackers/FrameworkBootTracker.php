<?php

namespace Tail\Laravel\Trackers;

use Tail\Apm;
use Tail\Apm\Span;
use Illuminate\Contracts\Foundation\Application;

class FrameworkBootTracker implements Tracker
{
    protected ?Span $span = null;

    public function register(Application $app)
    {
        $app->booting(function ($app) {
            $this->onStartBoot($app);
        });

        $app->booted(function ($app) {
            $this->onFinishBoot($app);
        });
    }

    public function onStartBoot(Application $app)
    {
        $this->span = Apm::newSpan('Framework boot', 'framework');
    }

    public function onFinishBoot(Application $app)
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
