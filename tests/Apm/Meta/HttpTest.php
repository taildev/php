<?php

namespace Tests\Apm\Meta;

use Tests\TestCase;
use Tail\Apm\Meta\Http;

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
        $http = new Http();
        $this->assertEmpty($http->method());
        $this->assertEmpty($http->url());

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/foo/bar';
        $http = new Http();
        $this->assertSame('POST', $http->method());
        $this->assertSame('/foo/bar', $http->url());
    }

    public function test_fill_from_array()
    {
        $http = new Http();
        $http->fillFromArray([
            'method' => 'custom-method',
            'url' => 'custom-url',
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

    public function test_output_to_array()
    {
        $http = new Http();
        $http->setMethod('get');
        $http->setUrl('/foo');
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
        ];

        $this->assertSame($expect, $http->toArray());
    }
}
