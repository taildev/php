<h1><img src="icon.svg" width="18px"> PHP Integration for tail.dev</h1>

![PHP tests](https://github.com/taildev/php/workflows/PHP%20tests/badge.svg)

<img src="php-logo.svg" width="100px">

For Laravel integration, see [taildev/laravel](https://github.com/taildev/laravel)

## Docs
See full documentation at [tail.dev/documentation/php/get-started](https://tail.dev/documentation/php/get-started)

## Quickstart

Install the `taildev/php` package using Composer
```sh
composer require taildev/php
```

Make sure that the composer autoloader is being required somewhere in your project:
```php
require __DIR__ . '/vendor/autoload.php';
```

#### APM

Initialize APM with your [auth token](https://tail.dev/documentation/quickstart) and service name (any name you'd like to identify this service)
```php
use Tail\Apm;

Apm::init('secret_token', 'service-name');
Apm::startRequest(); 

register_shutdown_function(function () {
    Apm::finish();
});
```

Alternatively you can use `startJob($name)` for background jobs, CLI commands, etc. or `startCustom($name)`

To add custom spans to the transaction
```php
$span = Apm::newSpan('fetch-config');
// ... code fetching config
$span->finish();
```

#### Logs
Initialize logging with your [auth token](https://tail.dev/documentation/quickstart). You may optionally provide a service name and environment if you wish
```php
use Tail\Log;

Log::init('secret_token', 'optional-service-name', 'optional-environment');
```

You can now use the logger anywhere to log a message
```php
use Tail\Log;

Log::get()->debug('my debug message');

Log::get()->alert('something went wrong', ['custom_tag' => 'some-value']);
```


#### More

For full documentation see [tail.dev/documentation/php/get-started](https://tail.dev/documentation/php/get-started)


