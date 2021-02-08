<?php

namespace Tests;

use Mockery;
use Tail\Log;
use Tail\Tail;
use Tail\Client;
use Carbon\Carbon;

class LogTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        Tail::$initialized = false;
        Log::$logs = [];
        Log::resetMeta();
    }

    public function test_log_meta_returns_new_instance_of_meta()
    {
        $meta = Log::meta();
        $this->assertNotNull($meta);
        $this->assertSame($meta, Log::meta());
    }

    public function test_log_messages()
    {
        Log::log('info', 'My info message', ['number' => 1]);
        Log::log('debug', 'My debug message', ['number' => 2]);

        $this->assertCount(2, Log::$logs);
        $expectedTime = Carbon::now()->timestamp;

        $log1 = Log::$logs[0];
        $this->assertSame('info', $log1['level']);
        $this->assertSame('My info message', $log1['message']);
        $this->assertSame(['number' => 1], $log1['context']);
        $this->assertEqualsWithDelta($expectedTime, Carbon::parse($log1['time'])->timestamp, 1);

        $log2 = Log::$logs[1];
        $this->assertSame('debug', $log2['level']);
        $this->assertSame('My debug message', $log2['message']);
        $this->assertSame(['number' => 2], $log2['context']);
        $this->assertEqualsWithDelta($expectedTime, Carbon::parse($log2['time'])->timestamp, 1);
    }

    public function test_flush_logs()
    {
        Log::log('debug', 'my message');

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('sendLogs')->once();
        Tail::init();
        Tail::setClient($client);

        Log::flush();
        $this->assertCount(0, Log::$logs);
    }

    public function test_flush_logs_only_sends_messages_if_present()
    {
        Tail::init();
        $client = Mockery::mock(Client::class);
        Tail::setClient($client);

        $client->shouldNotReceive('sendLogs');
        Log::flush();
    }

    public function test_flush_logs_doesnt_send_logs_if_logging_is_disabled()
    {
        Log::log('debug', 'my message');

        Tail::init(['logs_enabled' => false]);
        $client = Mockery::mock(Client::class);
        Tail::setClient($client);

        $client->shouldNotReceive('sendLogs');
        Log::flush();
    }

    public function test_flush_logs_sets_tail_service_meta()
    {
        Log::log('debug', 'my message');

        Tail::init(['logs_enabled' => false]);
        $client = Mockery::mock(Client::class);
        $client->shouldIgnoreMissing();
        Tail::setClient($client);

        $this->assertNull(Log::meta()->service()->name());
        $this->assertNull(Log::meta()->service()->environment());

        Log::flush();

        $this->assertNotNull(Log::meta()->service()->name());
        $this->assertNotNull(Log::meta()->service()->environment());
    }

    public function test_flush_logs_does_not_change_existing_service_metadata()
    {
        Log::log('debug', 'my message');

        Tail::init(['logs_enabled' => false]);
        $client = Mockery::mock(Client::class);
        $client->shouldIgnoreMissing();
        Tail::setClient($client);

        Log::meta()->service()->setName('my-custom-name');
        Log::meta()->service()->setEnvironment('my-custom-env');

        Log::flush();

        $this->assertSame('my-custom-name', Log::meta()->service()->name());
        $this->assertSame('my-custom-env', Log::meta()->service()->environment());
    }

    public function test_flush_logs_attaches_metadata_to_each_record()
    {
        Log::debug('message 1');
        Log::info('message 2');
        Log::meta()->service()->setName('my-custom-name');
        Log::meta()->service()->setEnvironment('my-custom-env');

        $expected = array_map(function ($log) {
            return array_merge($log, Log::meta()->toArray());
        }, Log::$logs);

        Tail::init();
        $client = Mockery::mock(Client::class);
        $client->shouldReceive('sendLogs')->with($expected)->once();
        Tail::setClient($client);
        
        Log::flush();
    }

    public function test_log_via_level_handlers()
    {
        Log::emergency('em', ['number' => 1]);
        Log::alert('al', ['number' => 2]);
        Log::critical('cr', ['number' => 3]);
        Log::error('er', ['number' => 4]);
        Log::warning('wa', ['number' => 5]);
        Log::notice('no', ['number' => 6]);
        Log::info('in', ['number' => 7]);
        Log::debug('de', ['number' => 8]);

        $this->assertSame('emergency', Log::$logs[0]['level']);
        $this->assertSame('em', Log::$logs[0]['message']);
        $this->assertSame(['number' => 1], Log::$logs[0]['context']);

        $this->assertSame('alert', Log::$logs[1]['level']);
        $this->assertSame('al', Log::$logs[1]['message']);
        $this->assertSame(['number' => 2], Log::$logs[1]['context']);

        $this->assertSame('critical', Log::$logs[2]['level']);
        $this->assertSame('cr', Log::$logs[2]['message']);
        $this->assertSame(['number' => 3], Log::$logs[2]['context']);

        $this->assertSame('error', Log::$logs[3]['level']);
        $this->assertSame('er', Log::$logs[3]['message']);
        $this->assertSame(['number' => 4], Log::$logs[3]['context']);

        $this->assertSame('warning', Log::$logs[4]['level']);
        $this->assertSame('wa', Log::$logs[4]['message']);
        $this->assertSame(['number' => 5], Log::$logs[4]['context']);

        $this->assertSame('notice', Log::$logs[5]['level']);
        $this->assertSame('no', Log::$logs[5]['message']);
        $this->assertSame(['number' => 6], Log::$logs[5]['context']);

        $this->assertSame('info', Log::$logs[6]['level']);
        $this->assertSame('in', Log::$logs[6]['message']);
        $this->assertSame(['number' => 7], Log::$logs[6]['context']);

        $this->assertSame('debug', Log::$logs[7]['level']);
        $this->assertSame('de', Log::$logs[7]['message']);
        $this->assertSame(['number' => 8], Log::$logs[7]['context']);
    }
}
