<?php

namespace Tests\Laravel\Trackers;

use Mockery;
use Tail\Apm;
use Tail\Apm\Span;
use Tests\TestCase;
use Tail\Laravel\Trackers\QueryTracker;
use Illuminate\Database\Events\QueryExecuted;

class QueryTrackerTest extends TestCase
{
    protected QueryTracker $tracker;

    public function setUp(): void
    {
        parent::setUp();
        $this->tracker = new QueryTracker();
    }

    public function test_creates_span_for_database_query()
    {
        $event = Mockery::mock(QueryExecuted::class);
        $event->sql = 'some query';
        $event->time = 120;
        $event->connectionName = 'mysql';
        $event->bindings = ['some' => 'data'];

        $this->tracker->queryExecuted($event);
        $spans = Apm::transaction()->spans();
        $span = array_pop($spans);

        $this->assertSame('some query', $span->database()->query());
        $this->assertEqualsWithDelta(microtime(true) * 1000, $span->endTime(), 10);
        $this->assertEqualsWithDelta((microtime(true) * 1000) - 120, $span->startTime(), 10);
        $this->assertSame('mysql', $span->tags()->get('connection'));
        $this->assertSame(json_encode($event->bindings), $span->tags()->get('bindings'));
    }
}
