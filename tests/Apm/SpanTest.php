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
        $this->span = new Span($this->transaction, 'some-type', 'some-span', 'id-123', 'parent-span-id');
    }

    public function test_create_from_array()
    {
        $span = Span::createFromArray($this->transaction, $data = [
           'trace' => [
               'type' => 'some-type',
               'name' => 'some-span',
               'id' => 'id-123',
               'start_time' => 123,
               'end_time' => 234,
               'duration' => 3,
               'parent_span_id' => 'parent-span-id',
           ],
           'database' => [
               'name' => 'some-database',
               'query' => 'some-query',
           ],
           'tags' => [
               'foo' => 'bar',
           ],
        ]);

        $this->assertSame($data, $span->serialize());
    }

    public function test_construct_with_properties()
    {
        $this->assertSame($this->transaction, $this->span->transaction());
        $this->assertSame('some-type', $this->span->type());
        $this->assertSame('some-span', $this->span->name());
        $this->assertSame('id-123', $this->span->id());
        $this->assertSame('parent-span-id', $this->span->parentSpanId());

        $this->assertNull($this->span->endTime());
        $expectedStart = Timestamp::nowInMs();
        $this->assertEqualsWithDelta($expectedStart, $this->span->startTime(), 5);

        $this->assertNotEmpty($this->span->database());
        $this->assertNotEmpty($this->span->tags());
    }

    public function test_new_child_span()
    {
        $childSpan = Mockery::mock(Span::class);
        $this->transaction->shouldReceive('newSpan')->with('some-name', 'some-type', 'id-123')->andReturn($childSpan);

        $result = $this->span->newChildSpan('some-name', 'some-type');
        $this->assertSame($childSpan, $result);
    }

    public function test_new_child_custom_span()
    {
        $childSpan = Mockery::mock(Span::class);
        $this->transaction->shouldReceive('newSpan')->with('some-name', Span::TYPE_CUSTOM, 'id-123')->andReturn($childSpan);

        $result = $this->span->newChildCustomSpan('some-name');
        $this->assertSame($childSpan, $result);
    }

    public function test_new_child_database_span()
    {
        $childSpan = Mockery::mock(Span::class);
        $this->transaction->shouldReceive('newSpan')->with('some-name', Span::TYPE_DATABASE, 'id-123')->andReturn($childSpan);

        $result = $this->span->newChildDatabaseSpan('some-name');
        $this->assertSame($childSpan, $result);
    }

    public function test_new_child_cache_span()
    {
        $childSpan = Mockery::mock(Span::class);
        $this->transaction->shouldReceive('newSpan')->with('some-name', Span::TYPE_CACHE, 'id-123')->andReturn($childSpan);

        $result = $this->span->newChildCacheSpan('some-name');
        $this->assertSame($childSpan, $result);
    }

    public function test_new_child_filesystem_span()
    {
        $childSpan = Mockery::mock(Span::class);
        $this->transaction->shouldReceive('newSpan')->with('some-name', Span::TYPE_FILESYSTEM, 'id-123')->andReturn($childSpan);

        $result = $this->span->newChildFilesystemSpan('some-name');
        $this->assertSame($childSpan, $result);
    }

    public function test_set_type()
    {
        $result = $this->span->setType('foo-type');
        $this->assertSame($this->span, $result);
        $this->assertSame('foo-type', $this->span->type());
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

    public function test_set_parent_span_id()
    {
        $result = $this->span->setParentSpanId('foo-parent-id');
        $this->assertSame($this->span, $result);
        $this->assertSame('foo-parent-id', $this->span->parentSpanId());
    }

    public function test_set_start_time()
    {
        $result = $this->span->setStartTime(123);
        $this->assertSame($this->span, $result);
        $this->assertSame(123, $this->span->startTime());
    }

    public function test_set_end_time()
    {
        $result = $this->span->setEndTime(234);
        $this->assertSame($this->span, $result);
        $this->assertSame(234, $this->span->endTime());
    }

    public function test_finish()
    {
        $result = $this->span->finish();
        $this->assertSame($this->span, $result);

        $expectedFinishTime = Timestamp::nowInMs();
        $this->assertEqualsWithDelta($expectedFinishTime, $this->span->endTime(), 5);
    }

    public function test_serialize()
    {
        $this->span->setStartTime(123);
        $this->span->setEndTime(234);
        $this->span->setDuration(4);
        $this->span->tags()->set('foo', 'bar');
        $this->span->database()->setName('mysql');
        $this->span->database()->setQuery('select * from foo');

        $expect = [
            'trace' => [
                'type' => 'some-type',
                'name' => 'some-span',
                'id' => 'id-123',
                'start_time' => 123,
                'end_time' => 234,
                'duration' => 4,
                'parent_span_id' => 'parent-span-id',
            ],
            'database' => [
                'name' => 'mysql',
                'query' => 'select * from foo',
            ],
            'tags' => [
                'foo' => 'bar'
            ],
        ];

        $this->assertEquals($expect, $this->span->serialize());
    }

    public function test_serialize_partial()
    {
        $span = new Span($this->transaction, 'some-type', 'some-span', 'id-123');
        $span->setStartTime(123);
        $span->setEndTime(234);

        $expect = [
            'trace' => [
                'type' => 'some-type',
                'name' => 'some-span',
                'id' => 'id-123',
                'start_time' => 123,
                'end_time' => 234,
                'duration' => null,
            ],
        ];

        $this->assertEquals($expect, $span->serialize());
    }
}
