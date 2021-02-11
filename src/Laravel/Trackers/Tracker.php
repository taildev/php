<?php

namespace Tail\Laravel\Trackers;

use Illuminate\Contracts\Foundation\Application;

interface Tracker
{

    /**
     * @param Application $app
     */
    public function register($app);
}
