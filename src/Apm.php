<?php

namespace Tail;

use Tail\Apm\Span;
use GuzzleHttp\Client;
use Tail\Apm\Meta\User;
use Tail\Apm\Meta\Tags;
use Tail\Apm\Meta\Http;
use Tail\Apm\Support\Id;
use Tail\Apm\Meta\System;
use Tail\Apm\Transaction;
use Tail\Apm\Meta\Service;
use Tail\Apm\Exceptions\ApmConfigException;

class Apm
{

    /** @var string Auth token for tracing service */
    protected $token;

    /** @var string Name to identify service */
    protected $serviceName;

    /** @var string Name of environment service is running in */
    protected $environment;

    /** @var Transaction */
    protected $transaction;

    /** @var Client */
    protected $client;

    /** @var Apm */
    protected static $instance;

    /**
     * Initialize tracer, should be called at the very beginning
     */
    public static function init(string $token, string $serviceName, ?string $environment = null): Apm
    {
        self::$instance = new Apm($token, $serviceName, $environment);
        return self::$instance;
    }

    /**
     * Get initialized Apm instance. Will throw an error if the tracer has not been initialized first.
     */
    public static function get(): Apm
    {
        if (!self::$instance) {
            throw new ApmConfigException('Apm has not been initialized, try calling Apm::init first');
        }

        return self::$instance;
    }

    /**
     * Manually set the APM instance
     */
    public static function replaceInstance(Apm $apm)
    {
        self::$instance = $apm;
    }

    /**
     * Determine if APM is both initialized AND started a transaction
     */
    public static function running(): bool
    {
        try {
            self::get()->transaction();
            return true;
        } catch (ApmConfigException $e) {
            return false;
        }
    }

    /**
     * Start a new transaction that traces a request. WARNING, this will overwrite an existing transaction.
     */
    public static function startRequest(?string $method = null, ?string $url = null): Transaction
    {
        return self::get()->startRequestTransaction($method, $url);
    }

    /**
     * Start a new transaction that traces a job. WARNING, this will overwrite an existing transaction.
     */
    public static function startJob(string $name): Transaction
    {
        return self::get()->startJobTransaction($name);
    }

    /**
     * Start a new transaction that traces a custom type. WARNING, this will overwrite an existing transaction.
     */
    public static function startCustom(string $type, ?string $name = null): Transaction
    {
        return self::get()->startCustomTransaction($type, $name);
    }

    /**
     * Set start time for transaction.
     *
     * @param float $time Unix timestamp in milliseconds
     */
    public static function setStartTime(float $time)
    {
        self::get()->transaction()->setStartTime($time);
    }

    /**
     * Set end time for transaction.
     *
     * @param float $time Unix timestamp in milliseconds
     */
    public static function setEndTime(?float $time)
    {
        self::get()->transaction()->setEndTime($time);
    }

    /**
     * Create new span for current transaction
     */
    public static function newSpan(string $name): Span
    {
        return self::get()->transaction()->newSpan($name);
    }

    /**
     * Service metadata for transaction
     */
    public static function service(): Service
    {
        return self::get()->transaction()->service();
    }

    /**
     * System metadata for transaction
     */
    public static function system(): System
    {
        return self::get()->transaction()->system();
    }

    /**
     * HTTP metadata for transaction
     */
    public static function http(): Http
    {
        return self::get()->transaction()->http();
    }

    /**
     * User metadata for transaction
     */
    public static function user(): User
    {
        return self::get()->transaction()->user();
    }

    /**
     * Custom metadata for transaction
     */
    public static function tags(): Tags
    {
        return self::get()->transaction()->tags();
    }

    /**
     * Finish transaction and spans that have not been marked as finished yet, and send all to the tracing service API.
     */
    public static function finish(): Apm
    {
        return self::get()->finishAndSend();
    }

    /**
     * Remove the initialized tracing instance
     */
    public static function reset()
    {
        self::$instance = null;
    }

    public function __construct(string $token, string $serviceName, ?string $environment = null, ?Client $client = null)
    {
        $this->token = $token;
        $this->serviceName = $serviceName;
        $this->environment = $environment;

        $this->client = $client ?: new Client();
    }

    /**
     * Get the transaction for the tracer. If no transaction has been started yet, an error is thrown.
     */
    public function transaction(): Transaction
    {
        if (!$this->transaction) {
            throw new ApmConfigException('Transaction has not been started yet');
        }

        return $this->transaction;
    }

    /**
     * Get auth token for tracing service
     */
    public function token(): ?string
    {
        return $this->token;
    }

    /**
     * Set the auth token for tracing service
     */
    public function setToken(string $token): Apm
    {
        $this->token = $token;
        return $this;
    }

    /**
     * Set the HTTP client
     */
    public function setClient(Client $client): Apm
    {
        $this->client= $client;
        return $this;
    }

    /**
     * Get the HTTP client
     */
    public function client(): Client
    {
        return $this->client;
    }

    public function startRequestTransaction(?string $method = null, ?string $url = null): Transaction
    {
        $id = Id::generate();
        $type = Transaction::TYPE_REQUEST;
        $t = new Transaction($id, "", $type, $this->serviceName, $this->environment);

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

        return $this->transaction = $t;
    }

    public function startJobTransaction(string $name): Transaction
    {
        $id = Id::generate();
        $type = Transaction::TYPE_JOB;

        $t = new Transaction($id, $name, $type, $this->serviceName, $this->environment);
        $this->transaction = $t;

        return $t;
    }

    public function startCustomTransaction(string $type, ?string $name = null): Transaction
    {
        $id = Id::generate();

        $t = new Transaction($id, $name, $type, $this->serviceName, $this->environment);
        $this->transaction = $t;

        return $t;
    }

    public function finishAndSend(): Apm
    {
        $t = $this->transaction();

        if ($t->endTime() === null) {
            $t->finish();
        }

        foreach ($t->spans() as $span) {
            if ($span->endTime() === null) {
                $span->finish();
            }
        }

        $url = getenv('TAIL_TRACE_ENDPOINT') ?: 'https://api.tail.dev/ingest/transactions';
        $this->client()->post($url, [
            'json' => $this->transaction()->toArray(),
            'headers' => [
                'Authorization' => 'Bearer '.$this->token(),
            ],
        ]);

        $this->transaction = null;

        return $this;
    }
}
