<?php

namespace Tail\Laravel\Trackers;

use Tail\Apm;
use Tail\Apm\Span;
use Illuminate\Contracts\Foundation\Application;

class FrameworkStartupTracker implements Tracker
{

    /** @var Span */
    protected $span;

    /**
     * @var Application $app
     */
    public function register($app)
    {
        $start = defined('LARAVEL_START') ? LARAVEL_START * 1000 : microtime(true) * 1000;
        $this->span = Apm::newSpan('Framework startup', 'framework')->setStartTime($start);

        $app->booting(function ($app) {
            $this->onStartBoot($app);
        });
    }

    /**
     * @var Application $app
     */
    public function onStartBoot($app)
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
