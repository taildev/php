<?php

namespace Tail;

use Tail\Support\Env;

class Tail
{

    /** @var bool */
    public static $initialized = false;

    /** @var bool */
    protected static $apmEnabled;

    /** @var bool */
    protected static $logsEnabled;

    /** @var string */
    protected static $service;

    /** @var string */
    protected static $environment;

    /** @var TailMeta */
    protected static $meta;

    /** @var Client */
    protected static $client;

    public static function init(array $config = [])
    {
        static::$meta = new TailMeta();
        static::$apmEnabled = static::shouldEnableApm($config);
        static::$logsEnabled = static::shouldEnableLogs($config);
        static::$meta->service()->setName(static::serviceName($config));
        static::$meta->service()->setEnvironment(static::environmentName($config));

        $token = static::clientToken($config);
        static::$client = new Client($token);

        static::$initialized = true;
    }

    protected static function clientToken(array $config)
    {
        $token = null;

        if (Env::get('TAIL_CLIENT_TOKEN')) {
            $token = Env::get('TAIL_CLIENT_TOKEN');
        }

        if (isset($config['client_token'])) {
            $token = $config['client_token'];
        }

        return $token;
    }

    protected static function shouldEnableApm(array $config)
    {
        $enable = true;

        if (Env::get('TAIL_APM_ENABLED') !== null) {
            $enable = Env::get('TAIL_APM_ENABLED');
        }

        if (isset($config['apm_enabled'])) {
            $enable = $config['apm_enabled'];
        }

        return $enable;
    }

    protected static function shouldEnableLogs(array $config)
    {
        $enable = true;

        if (Env::get('TAIL_LOGS_ENABLED') !== null) {
            $enable = Env::get('TAIL_LOGS_ENABLED');
        }

        if (isset($config['logs_enabled'])) {
            $enable = $config['logs_enabled'];
        }

        return $enable;
    }

    protected static function serviceName(array $config)
    {
        $name = Env::get('TAIL_SERVICE') ?? 'Unknown';

        if (isset($config['service'])) {
            $name = $config['service'];
        }

        return $name;
    }

    protected static function environmentName(array $config)
    {
        $name = Env::get('TAIL_ENV') ?? 'Default';

        if (isset($config['environment'])) {
            $name = $config['environment'];
        }

        return $name;
    }

    public static function end()
    {
        Apm::finish();
        Log::flush();
    }

    /**
     * @return Client
     */
    public static function client()
    {
        if (!static::$initialized) {
            static::init();
        }

        return self::$client;
    }

    /**
     * @return bool
     */
    public static function apmEnabled()
    {
        if (!static::$initialized) {
            static::init();
        }

        return static::$apmEnabled;
    }

    /**
     * Set APM to be disabled
     */
    public static function disableApm()
    {
        static::$apmEnabled = false;
    }

    /**
     * Set APM to be enabled
     */
    public static function enableApm()
    {
        static::$apmEnabled = true;
    }

    /**
     * @return bool
     */
    public static function logsEnabled()
    {
        if (!static::$initialized) {
            static::init();
        }

        return static::$logsEnabled;
    }

    /**
     * Set logs to be disabled
     */
    public static function disableLogs()
    {
        static::$logsEnabled = false;
    }

    /**
     * Set logs to be enabled
     */
    public static function enableLogs()
    {
        static::$logsEnabled = true;
    }

    /**
     * @return TailMeta
     */
    public static function meta()
    {
        if (!static::$initialized) {
            static::init();
        }

        return static::$meta;
    }

    public static function setClient(Client $client)
    {
        static::$client = $client;
    }

    /**
     * @return \Tail\Meta\Tags
     */
    public static function tags()
    {
        return static::meta()->tags();
    }

    /**
     * @return \Tail\Meta\User
     */
    public static function user()
    {
        return static::meta()->user();
    }

    /**
     * @return \Tail\Meta\Agent
     */
    public static function agent()
    {
        return static::meta()->agent();
    }

    /**
     * @return \Tail\Meta\System
     */
    public static function system()
    {
        return static::meta()->system();
    }

    /**
     * @return \Tail\Meta\Service
     */
    public static function service()
    {
        return static::meta()->service();
    }
}
