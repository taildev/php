<?php

namespace Tail\Laravel\Trackers;

use Tail\Apm;
use Tail\Apm\Span;
use Illuminate\Contracts\Foundation\Application;

class FrameworkBootTracker implements Tracker
{

    /** @var Span */
    protected $span;

    /**
     * @var Application $app
     */
    public function register($app)
    {
        $app->booting(function ($app) {
            $this->onStartBoot($app);
        });

        $app->booted(function ($app) {
            $this->onFinishBoot($app);
        });
    }

    /**
     * @var Application $app
     */
    public function onStartBoot($app)
    {
        $this->span = Apm::newSpan('Framework boot', 'framework');
    }

    /**
     * @var Application $app
     */
    public function onFinishBoot($app)
    {
        if (!$this->span) {
            return;
        }

        $this->span->finish();
    }

    /**
     * @return Span|null
     */
    public function getSpan()
    {
        return $this->span;
    }
}
