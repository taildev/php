<?php

namespace Tail;

use Tail\Apm\Span;
use Tail\Meta\Tags;
use Tail\Meta\Http;
use Tail\Apm\Support\Id;
use Tail\Apm\Transaction;

class Apm
{
    protected static ?Transaction $t = null;

    /**
     * Remove the current transaction
     */
    public static function reset()
    {
        static::$t = null;
    }

    /**
     * Get the root transaction. A new transaction will be created if one has not been started yet.
     */
    public static function transaction(): Transaction
    {
        if (!static::$t) {
            static::start();
        }

        return static::$t;
    }

    /**
     * Start a new transaction (tracing a request). WARNING, this will overwrite an existing transaction.
     */
    public static function start(): Transaction
    {
        return static::startRequest();
    }

    /**
     * Start a new transaction that traces a request. WARNING, this will overwrite an existing transaction.
     */
    public static function startRequest(?string $method = null, ?string $url = null): Transaction
    {
        $id = Id::generate();
        $type = Transaction::TYPE_REQUEST;
        $t = new Transaction($id, $type, null);

        if ($method) {
            $t->http()->setMethod($method);
        }
        if ($url) {
            $t->http()->setUrl($url);
        }

        $name = $t->http()->method() . " " . $t->http()->url();
        if (trim($name) === "") {
            $name = "Request";
        }
        $t->setName($name);
        static::$t = $t;

        return static::$t;
    }

    /**
     * Start a new transaction that traces a job. WARNING, this will overwrite an existing transaction.
     */
    public static function startJob(string $name): Transaction
    {
        $id = Id::generate();
        $type = Transaction::TYPE_JOB;

        $t = new Transaction($id, $type, $name);
        static::$t = $t;

        return static::$t;
    }

    /**
     * Start a new transaction that traces a custom type. WARNING, this will overwrite an existing transaction.
     */
    public static function startCustom(string $type, ?string $name = null): Transaction
    {
        $id = Id::generate();

        $t = new Transaction($id, $type, $name);
        static::$t = $t;

        return static::$t;
    }

    /**
     * Set start time for transaction. If a transaction has not started yet a new one will be created.
     *
     * @param float $time Unix timestamp in milliseconds
     */
    public static function setStartTime(float $time)
    {
        static::transaction()->setStartTime($time);
    }

    /**
     * Set end time for transaction. If a transaction has not started yet a new one will be created.
     *
     * @param float|null $time Unix timestamp in milliseconds
     */
    public static function setEndTime(?float $time)
    {
        static::transaction()->setEndTime($time);
    }

    /**
     * Create new span for current transaction. If a transaction has not started yet a new one will be created.
     */
    public static function newSpan($name, $type = Span::TYPE_CUSTOM): Span
    {
        return static::transaction()->newSpan($name, $type);
    }

    /**
     * Create new "database" type span for current transaction. If a transaction has not started yet a new one will be created.
     */
    public static function newDatabaseSpan(string $name): Span
    {
        return static::transaction()->newDatabaseSpan($name);
    }

    /**
     * Create new "cache" type span for current transaction. If a transaction has not started yet a new one will be created.
     */
    public static function newCacheSpan(string $name): Span
    {
        return static::transaction()->newCacheSpan($name);
    }

    /**
     * Create new "filesystem" type span for current transaction. If a transaction has not started yet a new one will be created.
     */
    public static function newFilesystemSpan(string $name): Span
    {
        return static::transaction()->newFilesystemSpan($name);
    }

    /**
     * HTTP metadata for transaction. If a transaction has not started yet a new one will be created.
     */
    public static function http(): Http
    {
        return static::transaction()->http();
    }

    /**
     * Custom metadata for transaction. If a transaction has not started yet a new one will be created.
     */
    public static function tags(): Tags
    {
        return static::transaction()->tags();
    }

    /**
     * Finish transaction and spans that have not been marked as finished yet, and send all to the tracing service API.
     * After sending the existing transaction will be cleared. If no trasnaction has started yet this method will simply return.
     */
    public static function finish()
    {
        $t = static::transaction();
        static::mergeTransactionMetadata($t);

        if ($t->endTime() === null) {
            $t->finish();
        }

        foreach ($t->spans() as $span) {
            if ($span->endTime() === null) {
                $span->finish();
            }
        }

        if (Tail::apmEnabled()) {
            Tail::client()->sendApm($t->serialize());
        }

        static::$t = null;
    }

    protected static function mergeTransactionMetadata(Transaction $t)
    {
        if (Tail::meta()->hasTags()) {
            $tags = Tail::tags()->serialize();
            if (is_array($tags)) {
                $t->tags()->merge($tags);
            }
        }

        if (Tail::meta()->hasUser()) {
            $user = Tail::user()->serialize();
            if (is_array($user)) {
                $t->user()->merge($user);
            }
        }

        if (Tail::meta()->hasAgent()) {
            $agent = Tail::agent()->serialize();
            if (is_array($agent)) {
                $t->agent()->merge($agent);
            }
        }

        if (Tail::meta()->hasSystem()) {
            $system = Tail::system()->serialize();
            if (is_array($system)) {
                $t->system()->merge($system);
            }
        }

        if (Tail::meta()->hasService()) {
            $service = Tail::service()->serialize();
            if (is_array($service)) {
                $t->service()->merge($service);
            }
        }
    }
}
