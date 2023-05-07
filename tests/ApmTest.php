<?php

namespace Tests;

use Mockery;
use Tail\Apm;
use Tail\Tail;
use Tail\Client;
use Tail\Apm\Span;
use Tail\Apm\Transaction;

class ApmTest extends TestCase
{
    public function test_getting_a_transaction_starts_a_new_one_if_not_already_started()
    {
        Apm::reset();
        $this->assertNotNull(Apm::transaction());
    }

    public function test_start_a_transaction_starts_a_request_transaction()
    {
        $t = Apm::start();
        $this->assertSame(Transaction::TYPE_REQUEST, $t->type());
    }

    public function test_start_request_transaction()
    {
        $t = Apm::startRequest('get', '/foo');
        $this->assertNotEmpty($t->id());
        $this->assertSame(Transaction::TYPE_REQUEST, $t->type());
        $this->assertSame('GET /foo', $t->name());
        $this->assertSame('GET', $t->http()->method());
        $this->assertSame('/foo', $t->http()->url());
        $this->assertSame($t, Apm::transaction());
    }

    public function test_start_request_uses_fallback_name_if_no_method_or_url_found()
    {
        $_SERVER['REQUEST_URI'] = '';
        $_SERVER['REQUEST_METHOD'] = '';
        $t = Apm::startRequest();
        $this->assertSame('Request', $t->name());
    }

    public function test_start_job_transaction()
    {
        $t = Apm::startJob('my-job');
        $this->assertNotEmpty($t->id());
        $this->assertSame(Transaction::TYPE_JOB, $t->type());
        $this->assertSame('my-job', $t->name());
        $this->assertSame($t, Apm::transaction());
    }

    public function test_start_custom_transaction()
    {
        $t = Apm::startCustom('my-type', 'my-transaction');
        $this->assertNotEmpty($t->id());
        $this->assertSame('my-type', $t->type());
        $this->assertSame('my-transaction', $t->name());
        $this->assertSame($t, Apm::transaction());
    }

    public function test_set_start_time_sets_transactions_start_time()
    {
        Apm::startCustom('foo');
        Apm::setStartTime(123);
        $this->assertSame(123, Apm::transaction()->startTime());
    }

    public function test_set_end_time_sets_transactions_end_time()
    {
        Apm::startCustom('foo');
        Apm::setEndTime(123);
        $this->assertSame(123, Apm::transaction()->endTime());
    }

    public function test_new_span_creates_new_span_for_transaction()
    {
        $t = Apm::startJob('some-job');
        $span = Apm::newSpan('span-name', 'span-type');

        $this->assertSame('span-name', $span->name());
        $this->assertSame('span-type', $span->type());
    }

    public function test_new_database_span()
    {
        Apm::startJob('some-job');
        $span = Apm::newDatabaseSpan('span-name');
        $this->assertSame(Span::TYPE_DATABASE, $span->type());
        $this->assertSame('span-name', $span->name());
    }

    public function test_new_cache_span()
    {
        Apm::startJob('some-job');
        $span = Apm::newCacheSpan('span-name');
        $this->assertSame(Span::TYPE_CACHE, $span->type());
        $this->assertSame('span-name', $span->name());
    }

    public function test_new_filesystem_span()
    {
        Apm::startJob('some-job');
        $span = Apm::newFilesystemSpan('span-name');
        $this->assertSame(Span::TYPE_FILESYSTEM, $span->type());
        $this->assertSame('span-name', $span->name());
    }

    public function test_http_metadata_for_transaction()
    {
        $t = Apm::startJob('foo');
        $http = Apm::http();

        $this->assertSame($t->http(), $http);
    }

    public function test_tag_metadata_for_transaction()
    {
        $t = Apm::startJob('foo');
        $tags = Apm::tags();

        $this->assertSame($t->tags(), $tags);
    }

    public function test_finish_and_send_to_api()
    {
        Tail::init(['apm_enabled' => true]);
        $client = Mockery::mock(Client::class);
        Tail::setClient($client);

        Apm::startRequest('GET', '/foo');

        $t = Apm::transaction();
        $client->shouldReceive('sendApm')->once();
        $t->finish();

        Apm::finish();

        // Transaction should have been cleared
        $this->assertNotSame($t, Apm::transaction());
    }

    public function test_finish_merges_global_metadata()
    {
        Tail::init();
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('sendApm');
        Tail::setClient($client);

        $t = Apm::startJob('some job');
        $t->tags()->set('k1', 'v1');
        $t->user()->setEmail('1@testing.com');
        $t->agent()->setName('testing1');
        $t->system()->setHostname('host1');
        $t->service()->setName('service1');

        Tail::meta()->tags()->set('k1', 'v2');
        Tail::meta()->tags()->set('k2', 'v2again');
        Tail::meta()->user()->setEmail('2@testing.com');
        Tail::meta()->user()->setId('2');
        Tail::meta()->agent()->setName('testing2');
        Tail::meta()->agent()->setVersion('version2');
        Tail::meta()->system()->setHostname('host2');
        Tail::meta()->service()->setName('service2');
        Tail::meta()->service()->setEnvironment('env2');

        Apm::finish();

        $this->assertSame('v2', $t->tags()->get('k1'));
        $this->assertSame('v2again', $t->tags()->get('k2'));
        $this->assertSame('2@testing.com', $t->user()->email());
        $this->assertSame('2', $t->user()->id());
        $this->assertSame('testing2', $t->agent()->name());
        $this->assertSame('version2', $t->agent()->version());
        $this->assertSame('host2', $t->system()->hostname());
        $this->assertSame('service2', $t->service()->name());
        $this->assertSame('env2', $t->service()->environment());
    }

    public function test_finish_sets_the_finish_time_for_transaction_if_not_set()
    {
        Tail::init();
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('sendApm');
        Tail::setClient($client);

        $t = Apm::startRequest('GET', '/foo');
        Apm::finish();

        $this->assertNotNull($t->endTime());
    }

    public function test_finish_and_send_doesnt_change_transactions_finished_time_if_already_set()
    {
        Tail::init();
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('sendApm');
        Tail::setClient($client);

        $t = Apm::startRequest('GET', '/foo');
        $t->finish();
        $doneAt = $t->endTime();

        usleep(10000);
        Apm::finish();

        $this->assertSame($doneAt, $t->endTime());
    }

    public function test_finish_and_send_sets_the_end_time_for_any_unfinished_spans()
    {
        Tail::init();
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('sendApm');
        Tail::setClient($client);

        $t = Apm::startRequest('GET', '/foo');

        $span1 = $t->newSpan('custom', '1');
        $span2 = $t->newSpan('custom', '1')->finish();
        $span3 = $t->newSpan('custom', '1');
        $doneAt = $span2->endTime();
        usleep(10000);

        Apm::finish();

        // 1 and 3 should have been finished, 2 should have stayed the same since it was already finished
        $this->assertNotNull($span1->endTime());
        $this->assertNotNull($span3->endTime());
        $this->assertSame($doneAt, $span2->endTime());
    }
}
