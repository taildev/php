<?php

namespace Tail\Apm;

use Tail\Meta\User;
use Tail\Meta\Tags;
use Tail\Meta\Http;
use Tail\Meta\Agent;
use Tail\Meta\System;
use Tail\Meta\Service;
use Tail\Apm\Support\Id;
use Tail\Apm\Support\Timestamp;

class Transaction
{
    public const TYPE_REQUEST = 'request';
    public const TYPE_JOB = 'job';

    /** Unique ID for transaction */
    protected string $id;

    /** Type of transaction */
    protected string $type;

    /** Name to identify transaction */
    protected ?string $name = null;

    /** Start time as milliseconds since epoch */
    protected int $startTime;

    /** End time as milliseconds since epoch */
    protected ?int $endTime = null;

    /** Duration as milliseconds */
    protected ?int $duration = null;

    /** Meta information for transaction agent */
    protected Agent $agent;

    /** Meta information for request transaction */
    protected ?Http $http = null;

    /** Meta information for transaction service */
    protected ?Service $service = null;

    /** Meta information for transaction system */
    protected System $system;

    /** Custom meta tags */
    protected ?Tags $tags = null;

    /** Meta information for transaction user */
    protected ?User $user = null;

    /** @var Span[] Spans that occur during transaction */
    protected array $spans;

    /**
     * Deserialize formatted properties array into Transaction object
     */
    public static function createFromArray(array $properties): Transaction
    {
        # trace properties
        $trace = $properties['trace'];
        $id = $trace['id'];
        $type = $trace['type'];
        $name = $trace['name'];
        $startTime = $trace['start_time'];
        $endTime = $trace['end_time'] ?? null;
        $duration = $trace['duration'] ?? null;

        # service properties
        $service = $properties['service'];
        $serviceName = $service['name'] ?? null;
        $environment = $service['environment'] ?? null;

        # create transaction
        $transaction = new Transaction($id, $type, $name);
        $transaction->setStartTime($startTime);
        $transaction->setEndTime($endTime);
        $transaction->setDuration($duration);
        $transaction->service()->setName($serviceName);
        $transaction->service()->setEnvironment($environment);

        # agent properties
        $agent = $properties['agent'] ?? [];
        if ($agent !== []) {
            $transaction->agent()->fillFromArray($agent);
        }

        # http properties
        $http = $properties['http'] ?? [];
        if ($http !== []) {
            $transaction->http()->fillFromArray($http);
        }

        # system properties
        $system = $properties['system'] ?? [];
        if ($system !== []) {
            $transaction->system()->fillFromArray($system);
        }

        # tags
        $tags = $properties['tags'] ?? [];
        if ($tags !== []) {
            $transaction->tags()->replaceAll($tags);
        }

        # user properties
        $user = $properties['user'] ?? [];
        if ($user !== []) {
            $transaction->user()->fillFromArray($user);
        }

        # spans
        $spans = $properties['spans'] ?? [];
        foreach ($spans as $spanProperties) {
            $span = Span::createFromArray($transaction, $spanProperties);
            $transaction->pushSpan($span);
        }

        return $transaction;
    }

    public function __construct(
        string $id,
        string $type,
        ?string $name
    ) {
        $this->id = $id;
        $this->type = $type;
        $this->name = $name;
        $this->startTime = Timestamp::nowInMs();

        $this->agent = new Agent();
        $this->system = new System();
        $this->spans = [];
    }

    /**
     * Get unique ID for transaction
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Set unique ID for transaction
     */
    public function setId(string $id): Transaction
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the transactions name
     */
    public function name(): ?string
    {
        return $this->name;
    }

    /**
     * Set the transactions name
     */
    public function setName(?string $name): Transaction
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get the transaction type
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Set the transaction type.
     */
    public function setType(string $type): Transaction
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get the transaction start time, represented as milliseconds since the epoch.
     */
    public function startTime(): int
    {
        return $this->startTime;
    }

    /**
     * Set the transaction start time, represented as milliseconds since the epoch.
     */
    public function setStartTime(int $startTime): Transaction
    {
        $this->startTime = $startTime;
        return $this;
    }

    /**
     * Get the transactions end time, represented as milliseconds since the epoch. If transaction has not ended,
     * this will return null.
     */
    public function endTime(): ?int
    {
        return $this->endTime;
    }

    /**
     * Set the end time for the transaction represented as milliseconds since the epoch.
     */
    public function setEndTime(?int $endTime): Transaction
    {
        $this->endTime = $endTime;
        return $this;
    }

    /**
     * Get the transactions duration, represented as milliseconds.
     */
    public function duration(): ?int
    {
        return $this->duration;
    }

    /**
     * Set the duration for the transaction represented as milliseconds.
     */
    public function setDuration(?int $duration): Transaction
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * Mark transaction as finished now, or with the optional time provided as milliseconds since epoch.
     */
    public function finish(?int $at = null): Transaction
    {
        $at = $at ?: Timestamp::nowInMs();
        $this->setEndTime($at);
        return $this;
    }

    /**
     * Agent meta information
     */
    public function agent(): Agent
    {
        return $this->agent;
    }

    /**
     * Service meta information
     */
    public function service(): Service
    {
        if ($this->service === null) {
            $this->service = new Service();
        }

        return $this->service;
    }

    /**
     * Determine if service information is present
     */
    public function hasService(): bool
    {
        return $this->service !== null;
    }

    /**
     * HTTP meta information for request transactions
     */
    public function http(): Http
    {
        if ($this->http === null) {
            $this->http = new Http();
        }

        return $this->http;
    }

    /**
     * Determine if HTTP information is present
     */
    public function hasHttp(): bool
    {
        return $this->http !== null;
    }

    /**
     * System meta information
     */
    public function system(): System
    {
        return $this->system;
    }

    /**
     * Custom meta information
     */
    public function tags(): Tags
    {
        if ($this->tags === null) {
            $this->tags = new Tags();
        }

        return $this->tags;
    }

    /**
     * Determine if tag information is present
     */
    public function hasTags(): bool
    {
        return $this->tags !== null;
    }

    /**
     * User meta information
     */
    public function user(): User
    {
        if ($this->user === null) {
            $this->user = new User();
        }

        return $this->user;
    }

    /**
     * Determine if user information is presetn
     */
    public function hasUser(): bool
    {
        return $this->user !== null;
    }

    /**
     * Child spans belonging to the transaction
     */
    public function spans(): array
    {
        return $this->spans;
    }

    /**
     * Add a new child span to the transaction
     */
    public function pushSpan(Span $span): Transaction
    {
        $this->spans[] = $span;
        return $this;
    }

    /**
     * Create a new child span for the transaction.
     */
    public function newSpan($name, $type = Span::TYPE_CUSTOM, $parentSpanId = null): Span
    {
        $id = Id::generate();
        $span = new Span($this, $type, $name, $id, $parentSpanId);
        $this->spans[] = $span;

        return $span;
    }

    /**
     * Create a new "database" type child span for the transaction
     */
    public function newDatabaseSpan(string $name, ?string $parentSpanId = null): Span
    {
        return $this->newSpan($name, Span::TYPE_DATABASE, $parentSpanId);
    }

    /**
     * Create a new "cache" type child span for the transaction
     */
    public function newCacheSpan(string $name, ?string $parentSpanId = null): Span
    {
        return $this->newSpan($name, Span::TYPE_CACHE, $parentSpanId);
    }

    /**
     * Create a new "filesystem" type child span for the transaction
     */
    public function newFilesystemSpan(string $name, ?string $parentSpanId = null): Span
    {
        return $this->newSpan($name, Span::TYPE_FILESYSTEM, $parentSpanId);
    }

    public function serialize(): array
    {
        $spanData = [];
        foreach ($this->spans() as $span) {
            $spanData[] = $span->serialize();
        }

        $data = [
            'trace' => [
                'id' => $this->id(),
                'type' => $this->type(),
                'name' => $this->name(),
                'start_time' => $this->startTime(),
                'end_time' => $this->endTime(),
                'duration' => $this->duration(),
            ],
            'spans' => $spanData,
        ];

        $data['agent'] = $this->agent()->serialize();
        $data['system'] = $this->system()->serialize();
        if ($this->hasHttp()) {
            $data['http'] = $this->http()->serialize();
        }
        if ($this->hasService()) {
            $data['service'] = $this->service()->serialize();
        }
        if ($this->hasTags()) {
            $data['tags'] = $this->tags()->serialize();
        }
        if ($this->hasUser()) {
            $data['user'] = $this->user()->serialize();
        }

        return $data;
    }
}
