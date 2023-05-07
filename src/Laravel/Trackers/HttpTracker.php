<?php

namespace Tail\Laravel\Trackers;

use Tail\Apm;
use Tail\Apm\Transaction;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Foundation\Http\Events\RequestHandled;

class HttpTracker implements Tracker
{
    /**
     * @param Application $app
     */
    public function register($app)
    {
        $app['events']->listen(RequestHandled::class, [$this, 'requestHandled']);
    }

    public function requestHandled(RequestHandled $event)
    {
        /** @var Route $route */
        $route = $event->request->route();
        $uri = $route ? $route->uri() : $event->request->path();
        $method = $event->request->method();
        $name = $method . ' ' . $uri;
        $params = $route ? $route->parameters() : [];
        $requestHeaders = $this->requestHeaders($event->request);
        $responseHeaders = $this->responseHeaders($event->response);

        $status = null;
        if (method_exists($event->response, 'status')) {
            $status = $event->response->status();
        }
        if (method_exists($event->response, 'getStatusCode')) {
            $status = $event->response->getStatusCode();
        }

        Apm::transaction()
            ->setType(Transaction::TYPE_REQUEST)
            ->setName($name)
            ->http()
            ->setMethod($method)
            ->setUrl($uri)
            ->setUrlParams($params)
            ->setRequestHeaders($requestHeaders)
            ->setResponseHeaders($responseHeaders)
            ->setResponseStatus($status);
    }

    protected function requestHeaders(Request $request): array
    {
        $headers = [];
        foreach ($request->headers->all() as $name => $h) {
            $headers[strtolower($name)] = $h[0];
        }

        return array_filter($headers, function ($key) {
            return array_search($key, config('tail.drop_request_headers')) === false;
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function responseHeaders(Response $response): array
    {
        $headers = [];
        foreach ($response->headers->all() as $name => $h) {
            $headers[strtolower($name)] = $h[0];
        }

        return $headers;
    }
}
