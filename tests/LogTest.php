<?php

namespace Tests;

use Mockery;
use Tail\Client;
use Tail\Log;
use Tail\Tail;

class LogTest extends TestCase
{

    public function setUp(): void
    {
        parent::setUp();
        Tail::$initialized = false;
        Log::$logs = [];
    }

    public function test_log_messages()
    {
        Log::log('info', 'My info message', ['number' => 1]);
        Log::log('debug', 'My debug message', ['number' => 2]);

        $this->assertCount(2, Log::$logs);
        $expectedTime = time();

        $log1 = Log::$logs[0];
        $this->assertSame('info', $log1['level']);
        $this->assertSame('My info message', $log1['message']);
        $this->assertSame(['number' => 1], $log1['tags']);
        $this->assertEqualsWithDelta($expectedTime, strtotime($log1['@timestamp']), 1);

        $log2 = Log::$logs[1];
        $this->assertSame('debug', $log2['level']);
        $this->assertSame('My debug message', $log2['message']);
        $this->assertSame(['number' => 2], $log2['tags']);
        $this->assertEqualsWithDelta($expectedTime, strtotime($log2['@timestamp']), 1);
    }

    public function test_flush_logs()
    {
        Log::log('debug', 'my message');

        $client = Mockery::mock(Client::class);
        $client->shouldReceive('sendLogs')->once();
        Tail::init(['logs_enabled' => true]);
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

    public function test_flush_logs_attaches_metadata_to_each_record()
    {
        Tail::init(['logs_enabled' => true]);
        $client = Mockery::mock(Client::class);
        Tail::setClient($client);

        Log::$logs = [];
        Log::debug('message 1', ['tag1' => 1, 'tag2' => 2]);
        Log::info('message 2');

        Tail::meta()->tags()->replaceAll(['tag1' => 'different']);
        Tail::meta()->service()->merge(['name' => 'service_name', 'environment' => 'testing']);
        Tail::meta()->system()->merge(['hostname' => 'foo-host']);

        $expectLog1 = array_merge(Log::$logs[0], [
            'tags' => ['tag1' => 1, 'tag2' => 2],
            'service' => ['name' => 'service_name', 'environment' => 'testing'],
            'system' => ['hostname' => 'foo-host'],
        ]);
        $expectLog2 = array_merge(Log::$logs[1], [
            'tags' => ['tag1' => 'different'],
            'service' => ['name' => 'service_name', 'environment' => 'testing'],
            'system' => ['hostname' => 'foo-host'],
        ]);

        $client->shouldReceive('sendLogs')->with([$expectLog1, $expectLog2])->once();

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
        $this->assertSame(['number' => 1], Log::$logs[0]['tags']);

        $this->assertSame('alert', Log::$logs[1]['level']);
        $this->assertSame('al', Log::$logs[1]['message']);
        $this->assertSame(['number' => 2], Log::$logs[1]['tags']);

        $this->assertSame('critical', Log::$logs[2]['level']);
        $this->assertSame('cr', Log::$logs[2]['message']);
        $this->assertSame(['number' => 3], Log::$logs[2]['tags']);

        $this->assertSame('error', Log::$logs[3]['level']);
        $this->assertSame('er', Log::$logs[3]['message']);
        $this->assertSame(['number' => 4], Log::$logs[3]['tags']);

        $this->assertSame('warning', Log::$logs[4]['level']);
        $this->assertSame('wa', Log::$logs[4]['message']);
        $this->assertSame(['number' => 5], Log::$logs[4]['tags']);

        $this->assertSame('notice', Log::$logs[5]['level']);
        $this->assertSame('no', Log::$logs[5]['message']);
        $this->assertSame(['number' => 6], Log::$logs[5]['tags']);

        $this->assertSame('info', Log::$logs[6]['level']);
        $this->assertSame('in', Log::$logs[6]['message']);
        $this->assertSame(['number' => 7], Log::$logs[6]['tags']);

        $this->assertSame('debug', Log::$logs[7]['level']);
        $this->assertSame('de', Log::$logs[7]['message']);
        $this->assertSame(['number' => 8], Log::$logs[7]['tags']);
    }
}
