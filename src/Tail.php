<?php

namespace Tail;

use Tail\Support\Env;

class Tail
{
    public static bool $initialized = false;

    protected static bool $apmEnabled = true;

    protected static bool $logsEnabled = true;

    protected static ?TailMeta $meta = null;

    protected static ?Client $client = null;

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

    public static function client(): Client
    {
        if (!static::$initialized) {
            static::init();
        }

        return self::$client;
    }

    public static function apmEnabled(): bool
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

    public static function logsEnabled(): bool
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

    public static function meta(): TailMeta
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

    public static function tags(): \Tail\Meta\Tags
    {
        return static::meta()->tags();
    }

    public static function user(): \Tail\Meta\User
    {
        return static::meta()->user();
    }

    public static function agent(): \Tail\Meta\Agent
    {
        return static::meta()->agent();
    }

    public static function system(): \Tail\Meta\System
    {
        return static::meta()->system();
    }

    public static function service(): \Tail\Meta\Service
    {
        return static::meta()->service();
    }
}
