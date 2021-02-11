<?php

namespace Tests;

use Tail\Tail;

class TailTest extends TestCase
{

    public function test_init_with_no_config()
    {
        Tail::$initialized = false;
        Tail::init();

        $this->assertNotNull(Tail::client());
        $this->assertNull(Tail::client()->token());
        $this->assertTrue(Tail::$initialized);
        $this->assertNotEmpty(Tail::meta());
        $this->assertSame('Unknown', Tail::meta()->service()->name());
        $this->assertSame('Default', Tail::meta()->service()->environment());
    }

    public function test_init_with_token_env_variable()
    {
        putenv("TAIL_CLIENT_TOKEN=env-token");
        Tail::init();
        $this->assertSame('env-token', Tail::client()->token());
    }

    public function test_init_with_config_token_overwrites_env()
    {
        putenv("TAIL_CLIENT_TOKEN=env-token");
        Tail::init(['client_token' => 'config-token']);
        $this->assertSame('config-token', Tail::client()->token());
    }

    public function test_init_with_default_services_enabled()
    {
        Tail::init();
        $this->assertTrue(Tail::apmEnabled());
        $this->assertTrue(Tail::logsEnabled());
    }

    public function test_init_with_env_to_disable_services()
    {
        putenv('TAIL_APM_ENABLED=false');
        putenv('TAIL_LOGS_ENABLED=false');
        Tail::init();
        $this->assertFalse(Tail::apmEnabled());
        $this->assertFalse(Tail::logsEnabled());
    }

    public function test_init_with_config_overwrites_env()
    {
        putenv('TAIL_APM_ENABLED=false');
        putenv('TAIL_LOGS_ENABLED=false');
        Tail::init(['apm_enabled' => true, 'logs_enabled' => true]);
        $this->assertTrue(Tail::apmEnabled());
        $this->assertTrue(Tail::logsEnabled());
    }

    public function test_init_with_service_env()
    {
        putenv('TAIL_SERVICE=my app');
        Tail::init();
        $this->assertSame('my app', Tail::meta()->service()->name());
    }

    public function test_init_with_service_config_overwrites_env()
    {
        putenv('TAIL_SERVICE=my app');
        Tail::init(['service' => 'customized']);
        $this->assertSame('customized', Tail::meta()->service()->name());
    }

    public function test_init_with_environmanet_env()
    {
        putenv('TAIL_ENV=staging');
        Tail::init();
        $this->assertSame('staging', Tail::meta()->service()->environment());
    }

    public function test_init_with_environmanet_config_overwrites_env()
    {
        putenv('TAIL_ENV=staging');
        Tail::init(['environment' => 'customized']);
        $this->assertSame('customized', Tail::meta()->service()->environment());
    }

    public function test_getting_client_will_auto_init_if_not_already()
    {
        Tail::$initialized = false;
        Tail::client();
        $this->assertTrue(Tail::$initialized);
    }

    public function test_getting_meta_will_auto_init_if_not_already()
    {
        Tail::$initialized = false;
        Tail::meta();
        $this->assertTrue(Tail::$initialized);
    }

    public function test_getting_apm_enabled_will_auto_init_if_not_already()
    {
        Tail::$initialized = false;
        Tail::apmEnabled();
        $this->assertTrue(Tail::$initialized);
    }

    public function test_getting_logs_enabled_will_auto_init_if_not_already()
    {
        Tail::$initialized = false;
        Tail::logsEnabled();
        $this->assertTrue(Tail::$initialized);
    }

    public function test_tags_meta_shortcut()
    {
        $this->assertSame(Tail::meta()->tags(), Tail::tags());
    }

    public function test_user_meta_shortcut()
    {
        $this->assertSame(Tail::meta()->user(), Tail::user());
    }

    public function test_agent_meta_shortcut()
    {
        $this->assertSame(Tail::meta()->agent(), Tail::agent());
    }

    public function test_system_meta_shortcut()
    {
        $this->assertSame(Tail::meta()->system(), Tail::system());
    }

    public function test_service_meta_shortcut()
    {
        $this->assertSame(Tail::meta()->service(), Tail::service());
    }
}
