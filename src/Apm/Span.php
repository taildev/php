<?php

namespace Tail\Apm;

use Tail\Apm\Meta\Tags;
use Tail\Apm\Meta\Database;
use Tail\Apm\Support\Timestamp;

class Span
{

    /** @var Transaction Transaction that span belongs to */
    protected $transaction;

    /** @var string Name to identify span */
    protected $name;

    /** @var string Unique ID for span */
    protected $id;

    /** @var string ID that span belongs to, may be another span or the transaction ID */
    protected $parentId;

    /** @var float Start time as milliseconds since epoch */
    protected $startTime;

    /** @var float End time as milliseconds since epoch */
    protected $endTime;

    /** @var Database Meta information for a span that tracks a  database call */
    protected $database;

    /** @var Tags Custom meta information for span */
    protected $tags;

    /**
     * Deserialize formatted properties array into Span object
     */
    public static function createFromArray(Transaction $transaction, array $properties): Span
    {
        # span properties
        $trace = $properties['trace'];
        $name = $trace['name'];
        $id = $trace['id'];
        $parentId = $trace['parent_id'];
        $startTime = $trace['start_time'];
        $endTime = array_key_exists('end_time', $trace) ? $trace['end_time'] : null;

        # create span
        $span = new Span($transaction, $name, $id, $parentId);
        $span->setStartTime($startTime);
        $span->setEndTime($endTime);

        # database properties
        $database = array_key_exists('database', $properties) ? $properties['database'] : [];
        $span->database()->fillFromArray($database);

        # tags
        $tags = array_key_exists('tags', $properties) ? $properties['tags'] : [];
        $span->tags()->replaceAll($tags);

        return $span;
    }

    public function __construct(Transaction $transaction, string $name, string $id, string $parentId)
    {
        $this->transaction = $transaction;
        $this->name = $name;
        $this->id = $id;
        $this->parentId = $parentId;

        $this->startTime = Timestamp::nowInMs();

        $this->database = new Database();
        $this->tags = new Tags();
    }

    /**
     * Create a new child span with the given name and this span as the parent.
     */
    public function newChildSpan(string $name): Span
    {
        return $this->transaction->newSpan($name, $this->id());
    }

    /**
     * Get transaction this span belongs to
     */
    public function transaction(): Transaction
    {
        return $this->transaction;
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
     * Get the parent ID of this span
     */
    public function parentId(): string
    {
        return $this->parentId;
    }

    /**
     * Set parent ID for span. If not direct parent span exists, the transaction ID should be used.
     */
    public function setParentId(string $parentId): Span
    {
        $this->parentId = $parentId;
        return $this;
    }

    /**
     * Get the start time as milliseconds since epoch
     */
    public function startTime(): float
    {
        return $this->startTime;
    }

    /**
     * Set start time for span as milliseconds since epoch
     */
    public function setStartTime(float $startTime): Span
    {
        $this->startTime = $startTime;
        return $this;
    }

    /**
     * Get the end time for the span as milliseconds since epoch
     */
    public function endTime(): ?float
    {
        return $this->endTime;
    }

    /**
     * Set the end time for the span as milliseconds since epoch
     */
    public function setEndTime(?float $endTime): Span
    {
        $this->endTime = $endTime;
        return $this;
    }

    /**
     * Get the duration of the span in milliseconds. If end_time is not specified,
     * duration will be the start_time until now.
     */
    public function duration(): float
    {
        $end = $this->endTime() ?: Timestamp::nowInMs();
        return $end - $this->startTime();
    }

    /**
     * Mark the span as finished now, or with the provided custom time (represented as milliseconds since epoch).
     */
    public function finish(?float $at = null): Span
    {
        $at = $at ?: Timestamp::nowInMs();
        return $this->setEndTime($at);
    }

    /**
     * Database meta information
     */
    public function database(): Database
    {
        return $this->database;
    }

    /**
     * Custom meta information
     */
    public function tags(): Tags
    {
        return $this->tags;
    }

    /**
     * Serialize span into an array
     */
    public function toArray(): array
    {
        return [
            'trace' => [
                'name' => $this->name(),
                'id' => $this->id,
                'parent_id' => $this->parentId(),
                'transaction_id' => $this->transaction()->id(),
                'start_time' => $this->startTime(),
                'end_time' => $this->endTime(),
                'duration' => $this->duration(),
            ],
            'database' => $this->database()->toArray(),
            'tags' => $this->tags()->toArray(),
        ];
    }
}
