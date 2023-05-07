<?php

namespace Tail\Laravel\Trackers;

use Tail\Apm;
use Illuminate\Contracts\Foundation\Application;

class ArtisanTracker implements Tracker
{
    public function register(Application $app)
    {
        if ($app->runningInConsole()) {
            $this->registerArtisan($app);
        }
    }

    public function registerArtisan(Application $app)
    {
        $name = join(' ', $_SERVER['argv']);

        Apm::transaction()
            ->setType('artisan')
            ->setName($name);
    }
}
