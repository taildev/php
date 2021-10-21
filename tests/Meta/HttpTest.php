<?php

namespace Tests\Meta;

use stdClass;
use Tail\Meta\Http;
use Tests\TestCase;

class HttpTest extends TestCase
{

    protected $http;

    public function setUp(): void
    {
        parent::setUp();
        $this->http = new Http();
    }

    public function test_constructed_with_defaults_from_server_if_exists()
    {
        unset($_SERVER['REQUEST_METHOD']);
        unset($_SERVER['REQUEST_URI']);
        unset($_SERVER['REMOTE_ADDR']);
        $http = new Http();
        $this->assertEmpty($http->method());
        $this->assertEmpty($http->url());

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

        $_SERVER['HTTP_HOST'] = 'localhost:9001';
        $_SERVER['HTTP_CONNECTION'] = 'keep-alive';
        $_SERVER['HTTP_USER_AGENT'] = 'Mozilla/5.0 (Macintosh;';
        $_SERVER['HTTP_CACHE_CONTROL'] = 'max-age=0';
        $_SERVER['HTTP_SEC_CH_UA'] = '\"Chromium\";v=\"91\"';
        $_SERVER['HTTP_SEC_CH_UA_MOBILE'] = '?0';
        $_SERVER['HTTP_UPGRADE_INSECURE_REQUESTS'] = '1';
        $_SERVER['HTTP_ACCEPT'] = 'text/html';
        $_SERVER['HTTP_SEC_FETCH_SITE'] = 'none';
        $_SERVER['HTTP_SEC_FETCH_MODE'] = 'navigate';
        $_SERVER['HTTP_SEC_FETCH_USER'] = '?1';
        $_SERVER['HTTP_SEC_FETCH_DEST'] = 'document';
        $_SERVER['HTTP_ACCEPT_ENCODING'] = 'gzip, deflate, br';
        $_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-US,en';

        $http = new Http();
        $this->assertSame('POST', $http->method());
        $this->assertSame('/foo/bar', $http->url());
        $this->assertSame('127.0.0.1', $http->remoteAddress());
        $this->assertSame('localhost:9001', $http->requestHeaders()['host']);
        $this->assertSame('keep-alive', $http->requestHeaders()['connection']);
        $this->assertSame('Mozilla/5.0 (Macintosh;', $http->requestHeaders()['user_agent']);
        $this->assertSame('max-age=0', $http->requestHeaders()['cache_control']);
        $this->assertSame('\"Chromium\";v=\"91\"', $http->requestHeaders()['sec_ch_ua']);
        $this->assertSame('?0', $http->requestHeaders()['sec_ch_ua_mobile']);
        $this->assertSame('1', $http->requestHeaders()['upgrade_insecure_requests']);
        $this->assertSame('text/html', $http->requestHeaders()['accept']);
        $this->assertSame('none', $http->requestHeaders()['sec_fetch_site']);
        $this->assertSame('navigate', $http->requestHeaders()['sec_fetch_mode']);
        $this->assertSame('?1', $http->requestHeaders()['sec_fetch_user']);
        $this->assertSame('document', $http->requestHeaders()['sec_fetch_dest']);
        $this->assertSame('gzip, deflate, br', $http->requestHeaders()['accept_encoding']);
        $this->assertSame('en-US,en', $http->requestHeaders()['accept_language']);
    }

    public function test_fill_from_array()
    {
        $http = new Http();
        $http->fillFromArray([
            'method' => 'custom-method',
            'url' => 'custom-url',
            'remote_address' => '127.0.0.1',
            'url_params' => ['custom' => 'params'],
            'request_headers' => ['custom' => 'request-headers'],
            'response_headers' => ['custom' => 'response-headers'],
            'response_status' => 200,
        ]);

        $this->assertSame('CUSTOM-METHOD', $http->method());
        $this->assertSame('custom-url', $http->url());
        $this->assertSame(['custom' => 'params'], $http->urlParams());
        $this->assertSame(['custom' => 'request-headers'], $http->requestHeaders());
        $this->assertSame(['custom' => 'response-headers'], $http->responseHeaders());
        $this->assertSame(200, $http->responseStatus());
    }

    public function test_set_method()
    {
        $result = $this->http->setMethod('get');
        $this->assertSame($this->http, $result);
        $this->assertSame('GET', $this->http->method());
    }

    public function test_set_url()
    {
        $result = $this->http->setUrl('/foo');
        $this->assertSame($this->http, $result);
        $this->assertSame('/foo', $this->http->url());
    }

    public function test_set_url_params()
    {
        $result = $this->http->setUrlParams(['foo' => 'bar']);
        $this->assertSame($this->http, $result);
        $this->assertSame(['foo' => 'bar'], $this->http->urlParams());
    }

    public function test_set_request_headers()
    {
        $result = $this->http->setRequestHeaders(['authorization' => 'bearer123']);
        $this->assertSame($this->http, $result);
        $this->assertSame(['authorization' => 'bearer123'], $this->http->requestHeaders());
    }

    public function test_set_response_headers()
    {
        $result = $this->http->setResponseHeaders(['content-type' => 'application/json']);
        $this->assertSame($this->http, $result);
        $this->assertSame(['content-type' => 'application/json'], $this->http->responseHeaders());
    }

    public function test_set_response_status()
    {
        $result = $this->http->setResponseStatus(200);
        $this->assertSame($this->http, $result);
        $this->assertSame(200, $this->http->responseStatus());
    }

    public function test_set_remote_address()
    {
        $result = $this->http->setRemoteAddress('127.0.0.1');
        $this->assertSame($this->http, $result);
        $this->assertSame('127.0.0.1', $this->http->remoteAddress());
    }

    public function test_output_to_array()
    {
        $http = new Http();
        $http->setMethod('get');
        $http->setUrl('/foo');
        $http->setRemoteAddress('127.0.0.1');
        $http->setUrlParams(['foo' => 'bar']);
        $http->setRequestHeaders(['authorization' => 'bearer123']);
        $http->setResponseHeaders(['content-type' => 'application/json']);
        $http->setResponseStatus(200);

        $expect = [
            'method' => 'GET',
            'url' => '/foo',
            'url_params' => ['foo' => 'bar'],
            'request_headers' => ['authorization' => 'bearer123'],
            'response_headers' => ['content-type' => 'application/json'],
            'response_status' => 200,
            'remote_address' => '127.0.0.1',
        ];

        $this->assertSame($expect, $http->toArray());
    }

    public function test_output_to_array_with_empty_objects()
    {
        $http = new Http();
        $http->setUrlParams([]);
        $http->setRequestHeaders([]);
        $http->setResponseHeaders([]);

        $this->assertEquals(new stdClass(), $http->toArray()['url_params']);
        $this->assertEquals(new stdClass(), $http->toArray()['request_headers']);
        $this->assertEquals(new stdClass(), $http->toArray()['response_headers']);
    }
}
