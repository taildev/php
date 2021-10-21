<?php

namespace Tail\Apm;

use stdClass;
use Tail\Meta\Tags;
use Tail\Meta\Database;
use Tail\Apm\Support\Timestamp;

class Span
{

    public const TYPE_DATABASE = 'database';
    public const TYPE_CACHE = 'cache';
    public const TYPE_FILESYSTEM = 'filesystem';
    public const TYPE_CUSTOM = 'custom';

    /** @var Transaction Transaction that span belongs to */
    protected $transaction;

    /** @var string Type of span */
    protected $type;

    /** @var string Name to identify span */
    protected $name;

    /** @var string Unique ID for span */
    protected $id;

    /** @var string ID that span belongs to, may be another span or the transaction ID */
    protected $parentId;

    /** @var int Start time as milliseconds since epoch */
    protected $startTime;

    /** @var int End time as milliseconds since epoch */
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
        $type = $trace['type'];
        $name = $trace['name'];
        $id = $trace['id'];
        $parentId = $trace['parent_id'];
        $startTime = $trace['start_time'];
        $endTime = array_key_exists('end_time', $trace) ? $trace['end_time'] : null;

        # create span
        $span = new Span($transaction, $type, $name, $id, $parentId);
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

    public function __construct(Transaction $transaction, string $type, string $name, string $id, string $parentId)
    {
        $this->transaction = $transaction;
        $this->type = $type;
        $this->name = $name;
        $this->id = $id;
        $this->parentId = $parentId;

        $this->startTime = Timestamp::nowInMs();

        $this->database = new Database();
        $this->tags = new Tags();
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
    public function newChildCustomSpan(string $name): Span
    {
        return $this->newChildSpan($name, self::TYPE_CUSTOM);
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
        $tags = $this->tags()->toArray();
        if (count($tags) === 0) {
            $tags = new stdClass();
        }

        return [
            'trace' => [
                'type' => $this->type(),
                'name' => $this->name(),
                'id' => $this->id,
                'parent_id' => $this->parentId(),
                'transaction_id' => $this->transaction()->id(),
                'start_time' => $this->startTime(),
                'end_time' => $this->endTime(),
            ],
            'database' => $this->database()->toArray(),
            'tags' => $tags,
        ];
    }
}
