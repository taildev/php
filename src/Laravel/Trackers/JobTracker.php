<?php

namespace Tail\Laravel\Trackers;

use Tail\Apm;
use Tail\Apm\Span;
use Illuminate\Support\Arr;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Contracts\Foundation\Application;

class JobTracker implements Tracker
{
    protected ?Application $app = null;

    /**
     * Track spans by job id for sync processing
     * ['id' => $span]
     */
    protected array $spans = [];

    public function register(Application $app)
    {
        $this->app = $app;
        $app->make('events')->listen(JobProcessing::class, [$this, 'jobProcessing']);
        $app->make('events')->listen(JobProcessed::class, [$this, 'jobProcessed']);
        $app->make('events')->listen(JobFailed::class, [$this, 'jobFailed']);
    }

    public function jobProcessing(JobProcessing $event)
    {
        $job = $event->job;
        $jobTags = [
            'job_id' => $job->getJobId(),
            'job_status' => 'processing',
            'connection' => $job->getConnectionName(),
            'queue' => $job->getQueue(),
            'attempts' => $job->attempts(),
            'payload' => json_encode($job->payload()),
        ];

        if ($this->isSyncDriver($event->connectionName)) {
            if (!$job->getJobId()) {
                return;
            }
            $span = Apm::newSpan($job->resolveName(), 'job');
            $span->tags()->replaceAll($jobTags);
            $this->spans[$job->getJobId()] = $span;
        } else {
            $transaction = Apm::startJob($job->resolveName());
            $transaction->tags()->replaceAll($jobTags);
        }
    }

    public function jobProcessed(JobProcessed $event)
    {
        $job = $event->job;

        if ($this->isSyncDriver($event->connectionName)) {
            $span = Arr::get($this->getSpans(), $job->getJobId());
            $this->finishSpan($span, 'processed');
        } else {
            $this->finishTransaction('processed');
        }
    }

    public function jobFailed(JobFailed $event)
    {
        $job = $event->job;

        if ($this->isSyncDriver($event->connectionName) && $job->getJobId()) {
            if (!$job->getJobId()) {
                return;
            }
            $span = Arr::get($this->getSpans(), $job->getJobId());
            $this->finishSpan($span, 'failed');
        } else {
            $this->finishTransaction('failed');
        }
    }

    public function getSpans(): array
    {
        return $this->spans;
    }

    protected function isSyncDriver($connection): bool
    {
        $driver = $this->app->make('config')->get('queue.connections.' . $connection . '.driver');
        return $driver === 'sync';
    }

    protected function finishSpan(?Span $span, string $status)
    {
        if (!$span) {
            return;
        }

        $span->tags()->set('job_status', $status);
        $span->finish();
    }

    protected function finishTransaction(string $status)
    {
        Apm::transaction()->tags()->set('job_status', $status);
        Apm::finish();
    }
}
