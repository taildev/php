<?php

namespace Tail\Laravel\Trackers;

use Illuminate\Contracts\Foundation\Application;

interface Tracker
{
    public function register(Application $app);
}
