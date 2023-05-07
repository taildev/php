<?php

namespace Tail\Laravel\Trackers;

use Tail\Apm;
use Tail\Apm\Span;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Contracts\Foundation\Application;

class QueryTracker implements Tracker
{
    public function register(Application $app)
    {
        $app['events']->listen(QueryExecuted::class, [$this, 'queryExecuted']);
    }

    public function queryExecuted(QueryExecuted $event)
    {
        $span = Apm::newDatabaseSpan('QueryExecuted');
        $span->finish();
        $span->setStartTime($span->endTime() - $event->time);
        $span->database()->setQuery($event->sql);
        $span->tags()->set('connection', $event->connectionName);
        $span->tags()->set('bindings', json_encode($event->bindings));
    }
}
