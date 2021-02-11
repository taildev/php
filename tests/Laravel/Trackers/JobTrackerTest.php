<?php

namespace Tests\Laravel\Trackers;

use Mockery;
use Tail\Apm;
use Tail\Apm\Span;
use Tests\TestCase;
use Tail\Laravel\LaravelApm;
use Illuminate\Contracts\Queue\Job;
use Illuminate\Queue\Events\JobFailed;
use Tail\Laravel\Trackers\JobTracker;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Config\Repository as Config;

class JobTrackerTest extends TestCase
{

    /** @var JobTracker */
    protected $tracker;

    /** @var Mockery\Mock|Job */
    protected $job;
    
    /** @var Mockery\Mock|Application|array */
    protected $app;

    /** @var Mockery\Mocker|Dispatcher */
    protected $dispatcher;

    /** @var Mockery\Mock|Config */
    protected $config;

    public function setUp(): void
    {
        parent::setUp();
        $this->dispatcher = Mockery::mock(Dispatcher::class);
        $this->dispatcher->shouldReceive('listen')->with(JobProcessing::class, Mockery::any());
        $this->dispatcher->shouldReceive('listen')->with(JobProcessed::class, Mockery::any());
        $this->dispatcher->shouldReceive('listen')->with(JobFailed::class, Mockery::any());
        $this->config = Mockery::mock(Config::class);
        $this->application = [
            'events' => $this->dispatcher,
            'config' => $this->config,
        ];

        $this->tracker = new JobTracker();
        $this->tracker->register($this->application);

        $this->job = Mockery::mock(Job::class);
        $this->job->shouldReceive('resolveName')->andReturn('MyJob');
        $this->job->shouldReceive('getJobId')->andReturn('job-id');
        $this->job->shouldReceive('getConnectionName')->andReturn('some-connection');
        $this->job->shouldReceive('getQueue')->andReturn('some-queue');
        $this->job->shouldReceive('attempts')->andReturn(2);
        $this->job->shouldReceive('payload')->andReturn(['my' => 'data']);
    }

    public function test_job_processing_starts_a_new_transaction()
    {
        $originalTransaction = Apm::transaction();
        $this->config->shouldReceive('get')->with('queue.connections.whatever.driver')->andReturn('anythingButSync');

        $event = Mockery::mock(JobProcessing::class);
        $event->job = $this->job;
        $event->connectionName = 'whatever';
        $this->tracker->jobProcessing($event);
        $this->assertCount(0, $this->tracker->getSpans());

        $transaction = Apm::transaction();
        $this->assertNotSame($originalTransaction, $transaction);
        $this->assertSame('job', $transaction->type());
        $this->assertSame('MyJob', $transaction->name());
        $expectedTags = [
            'job_id' => 'job-id',
            'job_status' => 'processing',
            'connection' => 'some-connection',
            'queue' => 'some-queue',
            'attempts' => 2,
            'payload' => json_encode(['my' => 'data']),
        ];
        $this->assertSame($expectedTags, $transaction->tags()->toArray());
    }

    public function test_job_processing_creates_a_new_span_for_the_job_if_its_a_sync_driver()
    {
        $this->config->shouldReceive('get')->with('queue.connections.sync.driver')->andReturn('sync');

        $event = Mockery::mock(JobProcessing::class);
        $event->job = $this->job;
        $event->connectionName = 'sync';
        $this->tracker->jobProcessing($event);
        $this->assertCount(1, $this->tracker->getSpans());

        $span = $this->tracker->getSpans()['job-id'];
        $this->assertSame('MyJob', $span->name());
        $this->assertSame('job', $span->type());
        $expectedTags = [
            'job_id' => 'job-id',
            'job_status' => 'processing',
            'connection' => 'some-connection',
            'queue' => 'some-queue',
            'attempts' => 2,
            'payload' => json_encode(['my' => 'data']),
        ];
        $this->assertSame($expectedTags, $span->tags()->toArray());
    }

    public function test_job_processing_doesnt_create_a_span_if_job_is_not_set()
    {
        $this->config->shouldReceive('get')->with('queue.connections.sync.driver')->andReturn('sync');
        $event = Mockery::mock(JobProcessing::class);
        $job = Mockery::mock(Job::class);
        $job->shouldReceive('resolveName')->andReturn('MyJob');
        $job->shouldReceive('getJobId')->andReturn(null);
        $job->shouldReceive('getConnectionName')->andReturn('some-connection');
        $job->shouldReceive('getQueue')->andReturn('some-queue');
        $job->shouldReceive('attempts')->andReturn(2);
        $job->shouldReceive('payload')->andReturn(['my' => 'data']);

        $event->job = $job;
        $event->connectionName = 'sync';
        $this->tracker->jobProcessing($event);
        $this->assertCount(0, $this->tracker->getSpans());
    }

    public function test_job_processed_sets_the_span_status_and_stop_time_when_driver_is_sync()
    {
        $this->config->shouldReceive('get')->with('queue.connections.sync.driver')->andReturn('sync');

        $processing = Mockery::mock(JobProcessing::class);
        $processing->job = $this->job;
        $processing->connectionName = 'sync';
        $this->tracker->jobProcessing($processing);

        $event = Mockery::mock(JobProcessed::class);
        $event->job = $this->job;
        $event->connectionName = 'sync';
        $this->tracker->jobProcessed($event);

        /** @var Span $span */
        $span = $this->tracker->getSpans()['job-id'];
        $this->assertSame('processed', $span->tags()->get('job_status'));
        $this->assertNotNull($span->endTime());
    }

    public function test_job_processed_sets_the_transaction_status_and_stop_time()
    {
        $this->config->shouldReceive('get')->with('queue.connections.whatever.driver')->andReturn('anythingButSync');

        $processing = Mockery::mock(JobProcessing::class);
        $processing->job = $this->job;
        $processing->connectionName = 'whatever';
        $this->tracker->jobProcessing($processing);

        $transaction = Apm::transaction();
        $event = Mockery::mock(JobProcessed::class);
        $event->job = $this->job;
        $event->connectionName = 'whatever';
        $this->tracker->jobProcessed($event);

        $this->assertCount(0, $this->tracker->getSpans());
        $this->assertSame('processed', $transaction->tags()->get('job_status'));
    }

    public function test_job_processed_ignores_span_if_not_found()
    {
        $this->config->shouldReceive('get')->with('queue.connections.sync.driver')->andReturn('sync');
        $this->assertSame([], $this->tracker->getSpans());
        $event = Mockery::mock(JobProcessed::class);
        $event->connectionName = 'sync';
        $event->job = $this->job;
        $this->tracker->jobProcessed($event);
    }

    public function test_job_failed_sets_the_span_status_and_stop_time_when_driver_is_sync()
    {
        $this->config->shouldReceive('get')->with('queue.connections.sync.driver')->andReturn('sync');

        $processing = Mockery::mock(JobProcessing::class);
        $processing->job = $this->job;
        $processing->connectionName = 'sync';
        $this->tracker->jobProcessing($processing);

        $event = Mockery::mock(JobFailed::class);
        $event->job = $this->job;
        $event->connectionName = 'sync';
        $this->tracker->jobFailed($event);

        /** @var Span $span */
        $span = $this->tracker->getSpans()['job-id'];
        $this->assertSame('failed', $span->tags()->get('job_status'));
        $this->assertNotNull($span->endTime());
    }

    public function test_job_failed_sets_the_transaction_status_and_finishes_when_driver_is_not_sync()
    {
        $this->config->shouldReceive('get')->with('queue.connections.whatever.driver')->andReturn('anythingButSync');

        $processing = Mockery::mock(JobProcessing::class);
        $processing->job = $this->job;
        $processing->connectionName = 'whatever';
        $this->tracker->jobProcessing($processing);

        $transaction = Apm::transaction();
        $event = Mockery::mock(JobFailed::class);
        $event->job = $this->job;
        $event->connectionName = 'whatever';
        $this->tracker->jobFailed($event);

        $this->assertSame('failed', $transaction->tags()->get('job_status'));
    }

    public function test_job_failed_ignores_span_if_not_found()
    {
        $this->config->shouldReceive('get')->with('queue.connections.sync.driver')->andReturn('sync');
        $this->assertSame([], $this->tracker->getSpans());
        $event = Mockery::mock(JobFailed::class);
        $event->job = $this->job;
        $event->connectionName = 'sync';
        $this->tracker->jobFailed($event);
    }
}
