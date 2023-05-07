<?php

namespace Tests\Apm;

use Mockery;
use Tail\Apm\Span;
use Tail\Apm\Support\Timestamp;
use Tail\Apm\Transaction;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    /** @var Transaction */
    protected $transaction;

    public function setUp(): void
    {
        parent::setUp();
        $this->transaction = new Transaction(
            'id-123',
            Transaction::TYPE_REQUEST,
            'some-transaction',
        );
    }

    public function test_create_from_array()
    {
        $transaction = Transaction::createFromArray($data = [
            'trace' => [
                'id' => 'custom-transaction-id',
                'type' => Transaction::TYPE_JOB,
                'name' => 'custom-name',
                'start_time' => 123,
                'end_time' => 456,
                'duration' => 4,
            ],
            'spans' => [
                [
                    'trace' => [
                        'type' => 'some-type',
                        'name' => 'some-span',
                        'id' => 'span-id',
                        'start_time' => 123,
                        'end_time' => 234,
                        'duration' => 3,
                        'parent_span_id' => 'span-parent-id',
                    ],
                    'database' => [
                        'name' => 'custom-db-name',
                        'query' => 'custom-db-query',
                    ],
                    'tags' => [
                        'span-foo' => 'span-bar',
                    ],
                ],
            ],
            'agent' => [
                'name' => 'custom-agent-name',
                'type' => 'custom-agent-type',
                'version' => 'custom-agent-version',
            ],
            'http' => [
                'method' => 'CUSTOM-HTTP-METHOD',
                'url' => 'custom-url',
                'url_params' => ['foo' => 'bar'],
                'request_headers' => ['auth' => '123'],
                'response_headers' => ['type' => 'json'],
                'response_status' => 200,
                'remote_address' => '127.0.0.1',
            ],
            'service' => [
                'name' => 'custom-service',
                'environment' => 'custom-env',
            ],
            'system' => [
                'hostname' => 'custom-hostname',
            ],
            'tags' => [
                'custom' => 'tag',
            ],
            'user' => [
                'id' => 'custom-id',
                'email' => 'custom-email',
            ],
        ]);

        $this->assertSame($data, $transaction->serialize());
    }

    public function test_construct_with_properties()
    {
        $this->assertSame('id-123', $this->transaction->id());
        $this->assertSame(Transaction::TYPE_REQUEST, $this->transaction->type());
        $this->assertSame('some-transaction', $this->transaction->name());

        $this->assertNull($this->transaction->endTime());
        $expectedStart = Timestamp::nowInMs();
        $this->assertEqualsWithDelta($expectedStart, $this->transaction->startTime(), 5);

        $this->assertNotEmpty($this->transaction->service());
        $this->assertNotEmpty($this->transaction->agent());
        $this->assertNotEmpty($this->transaction->http());
        $this->assertNotEmpty($this->transaction->system());
        $this->assertNotEmpty($this->transaction->tags());
        $this->assertNotEmpty($this->transaction->user());
        $this->assertSame([], $this->transaction->spans());
    }

    public function test_set_id()
    {
        $result = $this->transaction->setId('foo-id');
        $this->assertSame($this->transaction, $result);
        $this->assertSame('foo-id', $this->transaction->id());
    }

    public function test_set_type()
    {
        $result = $this->transaction->setType(Transaction::TYPE_JOB);
        $this->assertSame($this->transaction, $result);
        $this->assertSame(Transaction::TYPE_JOB, $this->transaction->type());
    }

    public function test_set_name()
    {
        $result = $this->transaction->setName('foo');
        $this->assertSame($this->transaction, $result);
        $this->assertSame('foo', $this->transaction->name());
    }

    public function test_set_start_time()
    {
        $result = $this->transaction->setStartTime(123);
        $this->assertSame($this->transaction, $result);
        $this->assertSame(123, $this->transaction->startTime());
    }

    public function test_set_end_time()
    {
        $result = $this->transaction->setEndTime(234);
        $this->assertSame($this->transaction, $result);
        $this->assertSame(234, $this->transaction->endTime());
    }

    public function test_finish()
    {
        $result = $this->transaction->finish();
        $this->assertSame($this->transaction, $result);

        $expectedFinishTime = Timestamp::nowInMs();
        $this->assertEqualsWithDelta($expectedFinishTime, $this->transaction->endTime(), 10);
    }

    public function test_push_spans()
    {
        $span1 = Mockery::mock(Span::class);
        $span2 = Mockery::mock(Span::class);
        $this->transaction->pushSpan($span1);
        $this->transaction->pushSpan($span2);
        $this->assertSame([$span1, $span2], $this->transaction->spans());
    }

    public function test_new_span()
    {
        $span = $this->transaction->newSpan('some-name', 'some-type');
        $this->assertSame('some-name', $span->name());
        $this->assertSame('some-type', $span->type());
        $this->assertNotEmpty($span->id());
        $this->assertSame(null, $span->parentSpanId());
        $this->assertSame('id-123', $span->transaction()->id());

        $spanCustomParent = $this->transaction->newSpan('some-name', 'some-type', 'custom-id');
        $this->assertSame('custom-id', $spanCustomParent->parentSpanId());
        $this->assertSame('id-123', $spanCustomParent->transaction()->id());

        $this->assertSame([$span, $spanCustomParent], $this->transaction->spans());
    }

    public function test_new_database_span()
    {
        $span = $this->transaction->newDatabaseSpan('some-name');
        $this->assertSame(Span::TYPE_DATABASE, $span->type());
        $this->assertSame('some-name', $span->name());
        $this->assertNotEmpty($span->id());
    }

    public function test_new_cache_span()
    {
        $span = $this->transaction->newCacheSpan('some-name');
        $this->assertSame(Span::TYPE_CACHE, $span->type());
        $this->assertSame('some-name', $span->name());
        $this->assertNotEmpty($span->id());
    }

    public function test_new_filesystem_span()
    {
        $span = $this->transaction->newFilesystemSpan('some-name');
        $this->assertSame(Span::TYPE_FILESYSTEM, $span->type());
        $this->assertSame('some-name', $span->name());
        $this->assertNotEmpty($span->id());
    }

    public function test_serialize()
    {
        $this->transaction->setStartTime(123);
        $this->transaction->setEndTime(234);
        $this->transaction->setDuration(4);

        $this->transaction->agent()->setName('php-testing');
        $this->transaction->http()->setMethod('get');
        $this->transaction->tags()->set('foo', 'bar');
        $this->transaction->service()->setEnvironment('testing');
        $this->transaction->system()->setHostname('foo-host');
        $this->transaction->user()->setId('123');

        $span1 = $this->transaction->newSpan('custom', '1')->setStartTime(2)->setEndTime(4);
        $span2 = $this->transaction->newSpan('custom', '2')->setStartTime(2)->setEndTime(4);

        $expect = [
            'trace' => [
                'id' => 'id-123',
                'type' => Transaction::TYPE_REQUEST,
                'name' => 'some-transaction',
                'start_time' => 123,
                'end_time' => 234,
                'duration' => 4,
            ],
            'spans' => [
                $span1->serialize(),
                $span2->serialize(),
            ],
            'agent' => $this->transaction->agent()->serialize(),
            'http' => $this->transaction->http()->serialize(),
            'service' => $this->transaction->service()->serialize(),
            'system' => $this->transaction->system()->serialize(),
            'tags' => ['foo' => 'bar'],
            'user' => $this->transaction->user()->serialize(),
        ];

        $this->assertEquals($expect, $this->transaction->serialize());
    }

    public function test_serialize_partial()
    {
        $transaction = new Transaction('id-123', Transaction::TYPE_REQUEST, 'some-transaction');
        $transaction->setStartTime(123);
        $transaction->setEndTime(234);

        $expect = [
            'trace' => [
                'id' => 'id-123',
                'type' => Transaction::TYPE_REQUEST,
                'name' => 'some-transaction',
                'start_time' => 123,
                'end_time' => 234,
                'duration' => null,
            ],
            'spans' => [],
            'agent' => $transaction->agent()->serialize(),
            'system' => $transaction->system()->serialize(),
        ];

        $this->assertSame($expect, $transaction->serialize());
    }
}
