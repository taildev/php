<?php

namespace Tests;

use Mockery;
use Tail\Log;
use Tail\Client;
use Carbon\Carbon;

class LogTest extends TestCase
{

    /** @var Log */
    protected $log;

    /** @var Client|Mockery\Mock */
    protected $client;

    public function setUp(): void
    {
        parent::setUp();
        $this->client = Mockery::mock(Client::class);
        $this->log = new Log($this->client);
    }

    public function test_init()
    {
        $logger = Log::init('secret_token', 'my_app', 'prod');
        $this->assertSame($logger, Log::get());
        $this->assertNotNull($logger->getClient());
        $this->assertSame('secret_token', $logger->getClient()->getToken());
        $this->assertSame('my_app', $logger->getServiceName());
        $this->assertSame('prod', $logger->getServiceEnvironment());
    }

    public function test_log_messages()
    {
        $this->log->log('info', 'My info message', ['number' => 1]);
        $this->log->log('debug', 'My debug message', ['number' => 2]);

        $this->assertCount(2, $this->log->getLogs());
        $expectedTime = Carbon::now()->timestamp;

        $log1 = $this->log->getLogs()[0];
        $this->assertSame('info', $log1['level']);
        $this->assertSame('My info message', $log1['message']);
        $this->assertSame(['number' => 1], $log1['context']);
        $this->assertEqualsWithDelta($expectedTime, Carbon::parse($log1['time'])->timestamp, 1);

        $log2 = $this->log->getLogs()[1];
        $this->assertSame('debug', $log2['level']);
        $this->assertSame('My debug message', $log2['message']);
        $this->assertSame(['number' => 2], $log2['context']);
        $this->assertEqualsWithDelta($expectedTime, Carbon::parse($log2['time'])->timestamp, 1);
    }

    public function test_log_message_appends_service_name_and_environment_when_present()
    {
        $this->log->setServiceName('my_app');
        $this->log->setServiceEnvironment('dev');
        $this->log->log('debug', 'some message');

        $log = $this->log->getLogs()[0];
        $this->assertSame('my_app', $log['service_name']);
        $this->assertSame('dev', $log['service_environment']);
    }

    public function test_flush_logs()
    {
        $this->log->log('debug', 'my message');

        $content = $this->log->getLogs();
        $this->client->shouldReceive('sendLogs')->with($content)->once();

        $this->log->flush();
        $this->assertCount(0, $this->log->getLogs());
    }

    public function test_flush_logs_only_sends_messages_if_present()
    {
        $this->client->shouldNotReceive('sendLogs');
        $this->log->flush();
    }

    public function test_log_via_level_handlers()
    {
        $this->log->emergency('em', ['number' => 1]);
        $this->log->alert('al', ['number' => 2]);
        $this->log->critical('cr', ['number' => 3]);
        $this->log->error('er', ['number' => 4]);
        $this->log->warning('wa', ['number' => 5]);
        $this->log->notice('no', ['number' => 6]);
        $this->log->info('in', ['number' => 7]);
        $this->log->debug('de', ['number' => 8]);

        $this->assertSame('emergency', $this->log->getLogs()[0]['level']);
        $this->assertSame('em', $this->log->getLogs()[0]['message']);
        $this->assertSame(['number' => 1], $this->log->getLogs()[0]['context']);

        $this->assertSame('alert', $this->log->getLogs()[1]['level']);
        $this->assertSame('al', $this->log->getLogs()[1]['message']);
        $this->assertSame(['number' => 2], $this->log->getLogs()[1]['context']);

        $this->assertSame('critical', $this->log->getLogs()[2]['level']);
        $this->assertSame('cr', $this->log->getLogs()[2]['message']);
        $this->assertSame(['number' => 3], $this->log->getLogs()[2]['context']);

        $this->assertSame('error', $this->log->getLogs()[3]['level']);
        $this->assertSame('er', $this->log->getLogs()[3]['message']);
        $this->assertSame(['number' => 4], $this->log->getLogs()[3]['context']);

        $this->assertSame('warning', $this->log->getLogs()[4]['level']);
        $this->assertSame('wa', $this->log->getLogs()[4]['message']);
        $this->assertSame(['number' => 5], $this->log->getLogs()[4]['context']);

        $this->assertSame('notice', $this->log->getLogs()[5]['level']);
        $this->assertSame('no', $this->log->getLogs()[5]['message']);
        $this->assertSame(['number' => 6], $this->log->getLogs()[5]['context']);

        $this->assertSame('info', $this->log->getLogs()[6]['level']);
        $this->assertSame('in', $this->log->getLogs()[6]['message']);
        $this->assertSame(['number' => 7], $this->log->getLogs()[6]['context']);

        $this->assertSame('debug', $this->log->getLogs()[7]['level']);
        $this->assertSame('de', $this->log->getLogs()[7]['message']);
        $this->assertSame(['number' => 8], $this->log->getLogs()[7]['context']);
    }
}
