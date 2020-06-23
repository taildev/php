<?php

namespace Tests\Apm;

use Mockery;
use Tail\Apm\Span;
use Tests\TestCase;
use Tail\Apm\Transaction;
use Tail\Apm\Support\Timestamp;
use Tail\Apm\Exceptions\TransactionConfigException;

class TransactionTest extends TestCase
{

    /** @var Transaction */
    protected $transaction;

    public function setUp(): void
    {
        parent::setUp();
        $this->transaction = new Transaction(
            'id-123',
            'some-transaction',
            Transaction::TYPE_REQUEST,
            'my-service',
            'prod'
        );
    }

    public function test_create_from_array()
    {
        $transaction = Transaction::createFromArray($data = [
           'trace' => [
               'id' => 'custom-transaction-id',
               'name' => 'custom-name',
               'type' => Transaction::TYPE_CUSTOM,
               'start_time' => 123.1,
               'end_time' => 456.2,
               'duration' => 333.1,
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
               'headers' => ['auth' => '123'],
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
           'spans' => [
               [
                   'trace' => [
                       'name' => 'some-span',
                       'id' => 'span-id',
                       'parent_id' => 'span-parent-id',
                       'transaction_id' => 'custom-transaction-id',
                       'start_time' => 123.4,
                       'end_time' => 234.5,
                       'duration' => 111.1,
                   ],
                   'database' => [
                       'name' => 'custom-db-name',
                       'operation' => 'custom-db-operation',
                       'query' => 'custom-db-query',
                   ],
                   'tags' => [
                       'span-foo' => 'span-bar',
                   ],
               ]
           ],
        ]);

        $this->assertSame($data, $transaction->toArray());
    }

    public function test_construct_with_properties()
    {
        $this->assertSame('id-123', $this->transaction->id());
        $this->assertSame('some-transaction', $this->transaction->name());
        $this->assertSame(Transaction::TYPE_REQUEST, $this->transaction->type());
        $this->assertSame('my-service', $this->transaction->service()->name());
        $this->assertSame('prod', $this->transaction->service()->environment());

        $this->assertNull($this->transaction->endTime());
        $expectedStart = Timestamp::nowInMs();
        $this->assertEqualsWithDelta($expectedStart, $this->transaction->startTime(), 50);

        $this->assertNotEmpty($this->transaction->agent());
        $this->assertNotEmpty($this->transaction->http());
        $this->assertNotEmpty($this->transaction->system());
        $this->assertNotEmpty($this->transaction->tags());
        $this->assertNotEmpty($this->transaction->user());
        $this->assertSame([], $this->transaction->spans());
    }

    public function test_create_with_invalid_type()
    {
        $this->expectException(TransactionConfigException::class);
        $this->expectExceptionMessage('Transaction type must be one of request, job, or custom');
        new Transaction('id-123', 'some-transaction', '!!!INVALID!!!', 'my-service', 'prod');
    }

    public function test_set_id()
    {
        $result = $this->transaction->setId('foo-id');
        $this->assertSame($this->transaction, $result);
        $this->assertSame('foo-id', $this->transaction->id());
    }

    public function test_set_name()
    {
        $result = $this->transaction->setName('foo');
        $this->assertSame($this->transaction, $result);
        $this->assertSame('foo', $this->transaction->name());
    }

    public function test_set_type()
    {
        $result = $this->transaction->setType(Transaction::TYPE_JOB);
        $this->assertSame($this->transaction, $result);
        $this->assertSame(Transaction::TYPE_JOB, $this->transaction->type());
    }

    public function test_set_type_with_invalid_type()
    {
        $this->expectException(TransactionConfigException::class);
        $this->expectExceptionMessage('Transaction type must be one of request, job, or custom');
        $this->transaction->setType('nope');
    }

    public function test_set_start_time()
    {
        $result = $this->transaction->setStartTime(123.4);
        $this->assertSame($this->transaction, $result);
        $this->assertSame(123.4, $this->transaction->startTime());
    }

    public function test_set_end_time()
    {
        $result = $this->transaction->setEndTime(234.5);
        $this->assertSame($this->transaction, $result);
        $this->assertSame(234.5, $this->transaction->endTime());
    }

    public function test_get_duration()
    {
        $this->transaction->setStartTime(22.4);
        $this->transaction->setEndTime(28.2);
        $this->assertSame(5.8, $this->transaction->duration());
    }

    public function test_get_duration_uses_current_time_if_end_is_not_specified()
    {
        $start = Timestamp::nowInMs() - 24;
        $this->transaction->setStartTime($start);
        $this->transaction->setEndTime(null);
        $this->assertEqualsWithDelta(24, $this->transaction->duration(), 5);
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
        $span = $this->transaction->newSpan('some-name');
        $this->assertSame('some-name', $span->name());
        $this->assertNotEmpty($span->id());
        $this->assertSame('id-123', $span->parentId());
        $this->assertSame('id-123', $span->transaction()->id());

        $spanCustomParent = $this->transaction->newSpan('some-name', 'custom-id');
        $this->assertSame('custom-id', $spanCustomParent->parentId());
        $this->assertSame('id-123', $spanCustomParent->transaction()->id());

        $this->assertSame([$span, $spanCustomParent], $this->transaction->spans());
    }

    public function test_output_to_array()
    {
        $this->transaction->setStartTime(123.1);
        $this->transaction->setEndTime(234.2);

        $span1 = $this->transaction->newSpan('1')->setStartTime(2)->setEndTime(4);
        $span2 = $this->transaction->newSpan('2')->setStartTime(2)->setEndTime(4);

        $expect = [
            'trace' => [
                'id' => 'id-123',
                'name' => 'some-transaction',
                'type' => Transaction::TYPE_REQUEST,
                'start_time' => 123.1,
                'end_time' => 234.2,
                'duration' => 111.1,
            ],
            'agent' => $this->transaction->agent()->toArray(),
            'http' => $this->transaction->http()->toArray(),
            'service' => $this->transaction->service()->toArray(),
            'system' => $this->transaction->system()->toArray(),
            'tags' => $this->transaction->tags()->toArray(),
            'user' => $this->transaction->user()->toArray(),
            'spans' => [
                $span1->toArray(),
                $span2->toArray(),
            ],
        ];

        $this->assertSame($expect, $this->transaction->toArray());
    }
}
