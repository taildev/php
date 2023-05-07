<?php

namespace Tail\Apm;

use Tail\Meta\Tags;
use Tail\Meta\Database;
use Tail\Apm\Support\Timestamp;

class Span
{
    public const TYPE_DATABASE = 'database';
    public const TYPE_CACHE = 'cache';
    public const TYPE_FILESYSTEM = 'filesystem';
    public const TYPE_CUSTOM = 'custom';

    /** Transaction that span belongs to */
    protected Transaction $transaction;

    /** Type of span */
    protected string $type;

    /** Name to identify span */
    protected string $name;

    /** Unique ID for span */
    protected string $id;

    /** ID that span belongs to*/
    protected ?string $parentSpanId = null;

    /** Start time as milliseconds since epoch */
    protected int $startTime;

    /** End time as milliseconds since epoch */
    protected ?int $endTime = null;

    /** Duration as milliseconds */
    protected ?int $duration = null;

    /** Meta information for a span that tracks a  database call */
    protected ?Database $database = null;

    /** Custom meta information for span */
    protected ?Tags $tags = null;

    /**
     * Deserialize formatted properties array into Span object
     */
    public static function createFromArray(Transaction $transaction, array $properties): Span
    {
        # span properties
        $trace = $properties['trace'];
        $type = $trace['type'];
        $name = $trace['name'];
        $id = $trace['id'];
        $parentSpanId = $trace['parent_span_id'] ?? null;
        $startTime = $trace['start_time'];
        $endTime = $trace['end_time'] ?? null;
        $duration = $trace['duration'] ?? null;

        # create span
        $span = new Span($transaction, $type, $name, $id, $parentSpanId);
        $span->setStartTime($startTime);
        $span->setEndTime($endTime);
        $span->setDuration($duration);

        # database properties
        $database = $properties['database'] ?? [];
        if ($database !== []) {
            $span->database()->fillFromArray($database);
        }

        # tags
        $tags = $properties['tags'] ?? [];
        if ($tags !== []) {
            $span->tags()->replaceAll($tags);
        }

        return $span;
    }

    public function __construct(Transaction $transaction, string $type, string $name, string $id, ?string $parentSpanId = null)
    {
        $this->transaction = $transaction;
        $this->type = $type;
        $this->name = $name;
        $this->id = $id;
        $this->parentSpanId = $parentSpanId;

        $this->startTime = Timestamp::nowInMs();
    }

    /**
     * Create a new child span with the given type, name and this span as the parent.
     */
    public function newChildSpan(string $name, string $type = self::TYPE_CUSTOM): Span
    {
        return $this->transaction->newSpan($name, $type, $this->id());
    }

    /**
     * Create a new "custom" type child span
     */
    public function newChildCustomSpan(string $name, string $type = self::TYPE_CUSTOM): Span
    {
        return $this->newChildSpan($name, $type);
    }

    /**
     * Create a new "database" type child span
     */
    public function newChildDatabaseSpan(string $name): Span
    {
        return $this->newChildSpan($name, self::TYPE_DATABASE);
    }

    /**
     * Create a new "cache" type child span
     */
    public function newChildCacheSpan(string $name): Span
    {
        return $this->newChildSpan($name, self::TYPE_CACHE);
    }

    /**
     * Create a new "filesystem" type child span
     */
    public function newChildFilesystemSpan(string $name): Span
    {
        return $this->newChildSpan($name, self::TYPE_FILESYSTEM);
    }

    /**
     * Get transaction this span belongs to
     */
    public function transaction(): Transaction
    {
        return $this->transaction;
    }

    /**
     * Get spans type
     */
    public function type(): string
    {
        return $this->type;
    }

    /**
     * Set the spans type
     */
    public function setType(string $type): Span
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Get spans name
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * Set the spans name
     */
    public function setName(string $name): Span
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get ID of span
     */
    public function id(): string
    {
        return $this->id;
    }

    /**
     * Set unique ID for span
     */
    public function setId(string $id): Span
    {
        $this->id = $id;
        return $this;
    }

    /**
     * Get the parent span ID
     */
    public function parentSpanId(): ?string
    {
        return $this->parentSpanId;
    }

    /**
     * Set parent span ID
     */
    public function setParentSpanId(?string $parentSpanId): Span
    {
        $this->parentSpanId = $parentSpanId;
        return $this;
    }

    /**
     * Get the start time as milliseconds since epoch
     */
    public function startTime(): int
    {
        return $this->startTime;
    }

    /**
     * Set start time for span as milliseconds since epoch
     */
    public function setStartTime(int $startTime): Span
    {
        $this->startTime = $startTime;
        return $this;
    }

    /**
     * Get the end time for the span as milliseconds since epoch
     */
    public function endTime(): ?int
    {
        return $this->endTime;
    }

    /**
     * Set the end time for the span as milliseconds since epoch
     */
    public function setEndTime(?int $endTime): Span
    {
        $this->endTime = $endTime;
        return $this;
    }

    /**
     * Get the duration for the span as milliseconds
     */
    public function duration(): ?int
    {
        return $this->duration;
    }

    /**
     * Set the duration for the span
     */
    public function setDuration(?int $duration): Span
    {
        $this->duration = $duration;
        return $this;
    }

    /**
     * Mark the span as finished now, or with the provided custom time (represented as milliseconds since epoch).
     */
    public function finish(?int $at = null): Span
    {
        $at = $at ?: Timestamp::nowInMs();
        return $this->setEndTime($at);
    }

    /**
     * Database meta information
     */
    public function database(): Database
    {
        if ($this->database === null) {
            $this->database = new Database();
        }

        return $this->database;
    }

    /**
     * Determine if database information is set
     */
    public function hasDatabase(): bool
    {
        return $this->database !== null;
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
     * Determine if tag information is set
     */
    public function hasTags(): bool
    {
        return $this->tags !== null;
    }

    /**
     * Serialize span into an array
     */
    public function serialize(): array
    {
        $data = [
            'trace' => [
                'type' => $this->type(),
                'name' => $this->name(),
                'id' => $this->id,
                'start_time' => $this->startTime(),
                'end_time' => $this->endTime(),
                'duration' => $this->duration(),
            ],
        ];

        if (isset($this->parentSpanId)) {
            $data['trace']['parent_span_id'] = $this->parentSpanId;
        }
        if ($this->hasDatabase()) {
            $data['database'] = $this->database()->serialize();
        }
        if ($this->hasTags()) {
            $data['tags'] = $this->tags()->serialize();
        }

        return $data;
    }
}
