<?php

namespace Tests;

use Mockery;
use Tail\Apm;
use GuzzleHttp\Client;
use Tail\Apm\Transaction;
use Tail\Apm\Exceptions\ApmConfigException;

class ApmTest extends TestCase
{

    public function test_constructs_with_properties()
    {
        $apm = new Apm('some-token', 'my-service', 'production');
        $this->assertSame('some-token', $apm->token());
        $this->assertNotEmpty($apm->client());
    }

    public function test_apm_init()
    {
        $apm = Apm::init('some-token', 'my-service', 'production');
        $this->assertSame($apm, Apm::get());
    }

    public function test_apm_get_throws_error_if_uninitialized()
    {
        Apm::reset();
        $this->expectException(ApmConfigException::class);
        $this->expectExceptionMessage('Apm has not been initialized, try calling Apm::init first');
        Apm::get();
    }

    public function test_set_token()
    {
        $apm = new Apm('some-token', 'my-service', 'production');
        $apm->setToken('new-token');
        $this->assertSame('new-token', $apm->token());
    }

    public function test_transaction_thats_not_started_throws_an_exception()
    {
        $this->expectException(ApmConfigException::class);
        $this->expectExceptionMessage('Transaction has not been started yet');
        $apm = new Apm('some-token', 'my-service', 'production');
        $apm->transaction();
    }

    public function test_start_request_transaction()
    {
        Apm::init('some-token', 'my-service', 'production');
        $t = Apm::startRequest('get', '/foo');
        $this->assertNotEmpty($t->id());
        $this->assertSame(Transaction::TYPE_REQUEST, $t->type());
        $this->assertSame('GET /foo', $t->name());
        $this->assertSame('GET', $t->http()->method());
        $this->assertSame('/foo', $t->http()->url());
        $this->assertSame('my-service', $t->service()->name());
        $this->assertSame('production', $t->service()->environment());
        $this->assertSame($t, Apm::get()->transaction());
    }

    public function test_start_request_uses_fallback_name_if_no_method_or_url_found()
    {
        Apm::init('some-token', 'my-service', 'production');
        $_SERVER['REQUEST_URI'] = '';
        $_SERVER['REQUEST_METHOD'] = '';
        $t = Apm::startRequest();
        $this->assertSame('Request', $t->name());
    }

    public function test_start_job_transaction()
    {
        Apm::init('some-token', 'my-service', 'production');
        $t = Apm::startJob('my-job');
        $this->assertNotEmpty($t->id());
        $this->assertSame(Transaction::TYPE_JOB, $t->type());
        $this->assertSame('my-job', $t->name());
        $this->assertSame('my-service', $t->service()->name());
        $this->assertSame('production', $t->service()->environment());
        $this->assertSame($t, Apm::get()->transaction());
    }

    public function test_start_custom_transaction()
    {
        Apm::init('some-token', 'my-service', 'production');
        $t = Apm::startCustom('my-transaction');
        $this->assertNotEmpty($t->id());
        $this->assertSame(Transaction::TYPE_CUSTOM, $t->type());
        $this->assertSame('my-transaction', $t->name());
        $this->assertSame('my-service', $t->service()->name());
        $this->assertSame('production', $t->service()->environment());
        $this->assertSame($t, Apm::get()->transaction());
    }

    public function test_set_start_time_sets_transactions_start_time()
    {
        Apm::init('some-token', 'my-service', 'production');
        Apm::startCustom('foo');
        Apm::setStartTime(123.456);
        $this->assertSame(123.456, Apm::get()->transaction()->startTime());
    }

    public function test_set_end_time_sets_transactions_end_time()
    {
        Apm::init('some-token', 'my-service', 'production');
        Apm::startCustom('foo');
        Apm::setEndTime(123.456);
        $this->assertSame(123.456, Apm::get()->transaction()->endTime());
    }

    public function test_new_span_creates_new_span_for_transaction()
    {
        Apm::init('some-token', 'my-service');

        $t = Apm::startJob('some-job');
        $span = Apm::newSpan('span-name');

        $this->assertSame($t->id(), $span->parentId());
    }

    public function test_service_metadata_for_transaction()
    {
        Apm::init('some-token', 'my-service');

        $t = Apm::startJob('foo');
        $service = Apm::service();

        $this->assertSame($t->service(), $service);
    }

    public function test_http_metadata_for_transaction()
    {
        Apm::init('some-token', 'my-service');

        $t = Apm::startJob('foo');
        $http = Apm::http();

        $this->assertSame($t->http(), $http);
    }

    public function test_system_metadata_for_transaction()
    {
        Apm::init('some-token', 'my-service');

        $t = Apm::startJob('foo');
        $system = Apm::system();

        $this->assertSame($t->system(), $system);
    }

    public function test_tag_metadata_for_transaction()
    {
        Apm::init('some-token', 'my-service');

        $t = Apm::startJob('foo');
        $tags = Apm::tags();

        $this->assertSame($t->tags(), $tags);
    }

    public function test_user_metadata_for_transaction()
    {
        Apm::init('some-token', 'my-service');

        $t = Apm::startJob('foo');
        $user = Apm::user();

        $this->assertSame($t->user(), $user);
    }

    public function test_finish_and_send_to_api()
    {
        $client = Mockery::mock(Client::class);
        $apm = Apm::init('some-token', 'my-service', 'production');
        $apm->setClient($client);

        Apm::startRequest('GET', '/foo');

        $client->shouldReceive('post')->withArgs(function ($url, $options) {
            if ($url !== 'https://api.tail.dev/ingest/transactions') {
                return false;
            }

            if ($options['json'] !== Apm::get()->transaction()->toArray()) {
                return false;
            }

            return true;
        });

        Apm::finish();

        // transaction should be cleared
        $this->expectException(ApmConfigException::class);
        Apm::get()->transaction();
    }

    public function test_finish_and_send_sets_the_finish_time_for_transaction_if_not_set()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('post');

        $apm = Apm::init('some-token', 'my-service', 'production');
        $apm->setClient($client);

        $t = Apm::startRequest('GET', '/foo');
        Apm::finish();

        $this->assertNotNull($t->endTime());
    }

    public function test_finish_and_send_doesnt_change_transactions_finished_time_if_already_set()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('post');

        $apm = Apm::init('some-token', 'my-service', 'production');
        $apm->setClient($client);

        $t = Apm::startRequest('GET', '/foo');
        $t->finish();
        $doneAt = $t->endTime();

        usleep(10000);
        Apm::finish();

        $this->assertSame($doneAt, $t->endTime());
    }

    public function test_finish_and_spend_sets_the_end_time_for_any_unfinished_spans()
    {
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('post');

        $apm = Apm::init('some-token', 'my-service', 'production');
        $apm->setClient($client);

        $t = Apm::startRequest('GET', '/foo');

        $span1 = $t->newSpan('1');
        $span2 = $t->newSpan('1')->finish();
        $span3 = $t->newSpan('1');
        $doneAt = $span2->endTime();
        usleep(10000);

        Apm::finish();

        // 1 and 3 should have been finished, 2 should have stayed the same since it was already finished
        $this->assertNotNull($span1->endTime());
        $this->assertNotNull($span3->endTime());
        $this->assertSame($doneAt, $span2->endTime());
    }
}
