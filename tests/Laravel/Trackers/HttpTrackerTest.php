<?php

namespace Tests\Laravel\Trackers;

use Mockery;
use Tail\Apm;
use Tests\TestCase;
use Tail\Apm\Transaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Tail\Laravel\Trackers\HttpTracker;
use Symfony\Component\HttpFoundation\HeaderBag;
use Illuminate\Foundation\Http\Events\RequestHandled;

class HttpTrackerTest extends TestCase
{

    public function test_track_http_request()
    {
        Apm::startCustom('whatever');

        $request = $this->fakeRequest();
        $response = new Response();
        $response->headers->set('Content-Type', ['text/html', 'ignored']);
        $response->setStatusCode(201);
        $event = new RequestHandled($request, $response);

        $tracker = new HttpTracker();
        $tracker->requestHandled($event);

        $transaction = Apm::transaction();
        $this->assertSame(Transaction::TYPE_REQUEST, $transaction->type());
        $this->assertSame('POST /foo/bar/:id', $transaction->name());
        $this->assertSame('POST', $transaction->http()->method());
        $this->assertSame('/foo/bar/:id', $transaction->http()->url());
        $this->assertSame(['param1' => 'foo1', 'param2' => 'foo2'], $transaction->http()->urlParams());
        $this->assertSame(['x-foo-header' => 'foobar', 'x-bar-header' => '1'], $transaction->http()->requestHeaders());
        $this->assertSame('text/html', $transaction->http()->responseHeaders()['content-type']);
        $this->assertSame(201, $transaction->http()->responseStatus());
    }

    public function test_handles_json_response()
    {
        Apm::startCustom('whatever');

        $request = $this->fakeRequest();
        $response = new JsonResponse();
        $response->headers->set('Content-Type', ['application/json', 'ignored']);
        $response->setStatusCode(201);
        $event = new RequestHandled($request, $response);

        $tracker = new HttpTracker();
        $tracker->requestHandled($event);

        $transaction = Apm::transaction();
        $this->assertSame(Transaction::TYPE_REQUEST, $transaction->type());
        $this->assertSame('POST /foo/bar/:id', $transaction->name());
        $this->assertSame('POST', $transaction->http()->method());
        $this->assertSame('/foo/bar/:id', $transaction->http()->url());
        $this->assertSame(['param1' => 'foo1', 'param2' => 'foo2'], $transaction->http()->urlParams());
        $this->assertSame(['x-foo-header' => 'foobar', 'x-bar-header' => '1'], $transaction->http()->requestHeaders());
        $this->assertSame('application/json', $transaction->http()->responseHeaders()['content-type']);
        $this->assertSame(201, $transaction->http()->responseStatus());
    }

    public function test_track_http_request_when_route_is_undefined()
    {
        Apm::startCustom('whatever');

        $request = $this->fakeRequest($returnsRoute = false);
        $response = new Response();
        $response->headers->set('Content-Type', ['text/html', 'ignored']);
        $response->setStatusCode(404);
        $event = new RequestHandled($request, $response);

        $tracker = new HttpTracker();
        $tracker->requestHandled($event);

        $transaction = Apm::transaction();
        $this->assertSame(Transaction::TYPE_REQUEST, $transaction->type());
        $this->assertSame('POST /non/route/path', $transaction->name());
        $this->assertSame('POST', $transaction->http()->method());
        $this->assertSame('/non/route/path', $transaction->http()->url());
        $this->assertSame([], $transaction->http()->urlParams());
        $this->assertSame(['x-foo-header' => 'foobar', 'x-bar-header' => '1'], $transaction->http()->requestHeaders());
        $this->assertSame('text/html', $transaction->http()->responseHeaders()['content-type']);
        $this->assertSame(404, $transaction->http()->responseStatus());
    }

    protected function fakeRequest($returnsRoute = true)
    {
        $route = Mockery::mock(Route::class);
        $route->shouldReceive('parameters')->andReturn(['param1' => 'foo1', 'param2' => 'foo2']);
        $route->shouldReceive('uri')->andReturn('/foo/bar/:id');

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('method')->andReturn('POST');
        $request->shouldReceive('route')->andReturn($returnsRoute ? $route : null);
        $request->shouldReceive('path')->andReturn('/non/route/path');

        $headers = new HeaderBag();
        $headers->set('x-foo-header', 'foobar');
        $headers->set('x-bar-header', ['1', '2']);
        $headers->set('Authorization', 'should-be-stripped');
        $request->headers = $headers;

        return $request;
    }
}
