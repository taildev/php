<?php

namespace Tests\Apm;

use Mockery;
use Tail\Apm\Span;
use Tests\TestCase;
use Tail\Apm\Transaction;
use Tail\Apm\Support\Timestamp;

class SpanTest extends TestCase
{

    /** @var Span */
    protected $span;

    /** @var Transaction */
    protected $transaction;

    public function setUp(): void
    {
        parent::setUp();
        $this->transaction = Mockery::mock(Transaction::class);
        $this->span = new Span($this->transaction, 'some-span', 'id-123', 'parent-id', 'transaction-id');
    }

    public function test_create_from_array()
    {
        $this->transaction->shouldReceive('id')->andReturn('transaction-id');
        $span = Span::createFromArray($this->transaction, $data = [
           'trace' => [
               'name' => 'some-span',
               'id' => 'id-123',
               'parent_id' => 'parent-id',
               'transaction_id' => 'transaction-id',
               'start_time' => 123.1,
               'end_time' => 234.2,
               'duration' => 111.1,
           ],
           'database' => [
               'name' => 'some-database',
               'operation' => 'some-operation',
               'query' => 'some-query',
           ],
           'tags' => [
               'foo' => 'bar',
           ],
        ]);

        $this->assertSame($data, $span->toArray());
    }

    public function test_construct_with_properties()
    {
        $this->assertSame($this->transaction, $this->span->transaction());
        $this->assertSame('some-span', $this->span->name());
        $this->assertSame('id-123', $this->span->id());
        $this->assertSame('parent-id', $this->span->parentId());

        $this->assertNull($this->span->endTime());
        $expectedStart = Timestamp::nowInMs();
        $this->assertEqualsWithDelta($expectedStart, $this->span->startTime(), 50);

        $this->assertNotEmpty($this->span->database());
        $this->assertNotEmpty($this->span->tags());
    }

    public function test_new_child_span()
    {
        $childSpan = Mockery::mock(Span::class);
        $this->transaction->shouldReceive('newSpan')->with('some-name', 'id-123')->andReturn($childSpan);

        $result = $this->span->newChildSpan('some-name');
        $this->assertSame($childSpan, $result);
    }

    public function test_set_name()
    {
        $result = $this->span->setName('foo');
        $this->assertSame($this->span, $result);
        $this->assertSame('foo', $this->span->name());
    }

    public function test_set_id()
    {
        $result = $this->span->setId('foo-id');
        $this->assertSame($this->span, $result);
        $this->assertSame('foo-id', $this->span->id());
    }

    public function test_set_parent_id()
    {
        $result = $this->span->setParentId('foo-parent-id');
        $this->assertSame($this->span, $result);
        $this->assertSame('foo-parent-id', $this->span->parentId());
    }

    public function test_set_start_time()
    {
        $result = $this->span->setStartTime(123.4);
        $this->assertSame($this->span, $result);
        $this->assertSame(123.4, $this->span->startTime());
    }

    public function test_set_end_time()
    {
        $result = $this->span->setEndTime(234.5);
        $this->assertSame($this->span, $result);
        $this->assertSame(234.5, $this->span->endTime());
    }

    public function test_get_duration()
    {
        $now = Timestamp::nowInMs();
        $start = $now - 24;
        $this->span->setStartTime($start);
        $this->span->setEndTime($now);

        $this->assertSame(24.0, $this->span->duration());
    }

    public function test_get_duration_defaults_to_using_now_as_end_time()
    {
        $start = Timestamp::nowInMs() - 24;
        $this->span->setStartTime($start);
        $this->span->setEndTime(null);
        $this->assertEqualsWithDelta(24.0, $this->span->duration(), 5);
    }

    public function test_finish()
    {
        $result = $this->span->finish();
        $this->assertSame($this->span, $result);

        $expectedFinishTime = Timestamp::nowInMs();
        $this->assertEqualsWithDelta($expectedFinishTime, $this->span->endTime(), 10);
    }

    public function test_output_to_array()
    {
        $this->span->setStartTime(123.1);
        $this->span->setEndTime(234.2);
        $this->transaction->shouldReceive('id')->andReturn('transaction-id');

        $expect = [
            'trace' => [
                'name' => 'some-span',
                'id' => 'id-123',
                'parent_id' => 'parent-id',
                'transaction_id' => 'transaction-id',
                'start_time' => 123.1,
                'end_time' => 234.2,
                'duration' => 111.1,
            ],
            'database' => $this->span->database()->toArray(),
            'tags' => $this->span->tags()->toArray(),
        ];

        $this->assertSame($expect, $this->span->toArray());
    }
}
