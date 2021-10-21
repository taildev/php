<?php

namespace Tail\Apm;

use stdClass;
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

    /** @var string Unique ID for transaction */
    protected $id;

    /** @var string Type of transaction */
    protected $type;

    /** @var string|null Name to identify transaction */
    protected $name;

    /** @var int Start time as milliseconds since epoch */
    protected $startTime;

    /** @var int End time as milliseconds since epoch */
    protected $endTime;

    /** @var Agent Meta information for transaction agent */
    protected $agent;

    /** @var Http Meta information for request transaction */
    protected $http;

    /** @var Service Meta information for transaction service */
    protected $service;

    /** @var System Meta information for transaction system */
    protected $system;

    /** @var Tags Custom meta tags */
    protected $tags;

    /** @var User Meta information for transaction user */
    protected $user;

    /** @var Span[] Spans that occur during transaction */
    protected $spans;

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
        $endTime = array_key_exists('end_time', $trace) ? $trace['end_time'] : null;

        # service properties
        $service = $properties['service'];
        $serviceName = array_key_exists('name', $service) ? $service['name'] : null;
        $environment = array_key_exists('environment', $service) ? $service['environment'] : null;

        # create transaction
        $transaction = new Transaction($id, $type, $name);
        $transaction->setStartTime($startTime);
        $transaction->setEndTime($endTime);
        $transaction->service()->setName($serviceName);
        $transaction->service()->setEnvironment($environment);

        # agent properties
        $agent = array_key_exists('agent', $properties) ? $properties['agent'] : [];
        $transaction->agent()->fillFromArray($agent);

        # http properties
        $http = array_key_exists('http', $properties) ? $properties['http'] : [];
        $transaction->http()->fillFromArray($http);

        # system properties
        $system = array_key_exists('system', $properties) ? $properties['system'] : [];
        $transaction->system()->fillFromArray($system);

        # tags
        $tags = array_key_exists('tags', $properties) ? $properties['tags'] : [];
        $transaction->tags()->replaceAll($tags);

        # user properties
        $user = array_key_exists('user', $properties) ? $properties['user'] : [];
        $transaction->user()->fillFromArray($user);

        # spans
        $spans = array_key_exists('spans', $properties) ? $properties['spans'] : [];
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
        $this->http = new Http();
        $this->service = new Service();
        $this->system = new System();
        $this->tags = new Tags();
        $this->user = new User();
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
        return $this->service;
    }

    /**
     * HTTP meta information for request transactions
     */
    public function http(): Http
    {
        return $this->http;
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
        return $this->tags;
    }

    /**
     * User meta information
     */
    public function user(): User
    {
        return $this->user;
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

    /**
     * Serialize transaction as an array
     */
    public function toArray(): array
    {
        $spanData = [];
        foreach ($this->spans() as $span) {
            $spanData[] = $span->toArray();
        }

        $tags = $this->tags()->toArray();
        if (count($tags) === 0) {
            $tags = new stdClass();
        }

        return [
            'trace' => [
                'id' => $this->id(),
                'type' => $this->type(),
                'name' => $this->name(),
                'start_time' => $this->startTime(),
                'end_time' => $this->endTime(),
            ],
            'agent' => $this->agent()->toArray(),
            'http' => $this->http()->toArray(),
            'service' => $this->service()->toArray(),
            'system' => $this->system()->toArray(),
            'tags' => $tags,
            'user' => $this->user()->toArray(),
            'spans' => $spanData,
        ];
    }
}
