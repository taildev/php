<?php

namespace Tail\Laravel;

use Tail\Apm;
use Tail\Tail;
use Illuminate\Support\ServiceProvider;

class TailServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/tail.php', 'tail');

        Tail::init([
            'client_token' => config('tail.client_token'),
            'service' => config('app.name', 'Unknown'),
            'environment' => $this->app->environment(),
            'apm_enabled' => config('tail.apm_enabled'),
            'logs_enabled' => config('tail.logs_enabled'),
        ]);

        $this->registerTrackers();
        $this->startApm();
        $this->registerShutdown();
    }

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/tail.php' => config_path('tail.php'),
        ]);
    }

    protected function registerShutdown()
    {
        register_shutdown_function(function () {
            if (Apm::transaction()->type() === '_ignore') {
                Tail::disableApm();
            }

            Tail::end();
        });
    }

    protected function registerTrackers()
    {
        $trackers = config('tail.apm_trackers');
        foreach ($trackers as $tracker) {
            $t = $this->app->make($tracker);
            $t->register($this->app);
        }
    }

    /**
     * Transactions start as a custom type named '_ignore'. The transaction must be
     * initialized in order to capture spans so we create it early. Later in the process
     * when we determine if it's a request, job, etc. the type and name are updated accordingly.
     * Otherwise if we make it to the end with the `_ignore` type we'll skip recording it.
     */
    protected function startApm()
    {
        $start = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
        Apm::startCustom('_ignore')->setStartTime(ceil($start * 1000));
    }
}
