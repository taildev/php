<?php

namespace Tail\Meta;

class Http
{

    /** @var string HTTP method */
    protected $method;

    /** @var string URL for request */
    protected $url;

    /** @var array Parameter values used in URL */
    protected $urlParams;

    /** @var array Headers for request */
    protected $requestHeaders;

    /** @var array Headers for response */
    protected $responseHeaders;

    /** @var int Response status */
    protected $responseStatus;

    public function __construct()
    {
        if (isset($_SERVER['REQUEST_METHOD'])) {
            $this->setMethod($_SERVER['REQUEST_METHOD']);
        }

        if (isset($_SERVER['REQUEST_URI'])) {
            $this->setUrl($_SERVER['REQUEST_URI']);
        }
    }

    /**
     * Deserialize properties into Http
     */
    public function fillFromArray(array $properties): Http
    {
        if (array_key_exists('method', $properties)) {
            $this->setMethod($properties['method']);
        };

        if (array_key_exists('url', $properties)) {
            $this->setUrl($properties['url']);
        }

        if (array_key_exists('url_params', $properties)) {
            $this->setUrlParams($properties['url_params']);
        }

        if (array_key_exists('request_headers', $properties)) {
            $this->setRequestHeaders($properties['request_headers']);
        }

        if (array_key_exists('response_headers', $properties)) {
            $this->setResponseHeaders($properties['response_headers']);
        }

        if (array_key_exists('response_status', $properties)) {
            $this->setResponseStatus($properties['response_status']);
        }

        return $this;
    }

    /**
     * Get the HTTP method
     */
    public function method(): ?string
    {
        return $this->method;
    }

    /**
     * Set the HTTP method for the request. All method names are converted to uppercase.
     */
    public function setMethod(?string $method): Http
    {
        if ($method !== null) {
            $this->method = strtoupper($method);
        }

        return $this;
    }

    /**
     * Get the URL for the request
     */
    public function url(): ?string
    {
        return $this->url;
    }

    /**
     * Set the URL for the request.
     *
     * It's best to use a generic path. For example, use
     *      /api/posts/:id
     * As opposed to
     *      https://example.com/api/posts/123
     *
     * URL params can be used to provide additional context.
     */
    public function setUrl(?string $url): Http
    {
        $this->url = $url;
        return $this;
    }

    /**
     * Get the URL parameter values
     */
    public function urlParams(): ?array
    {
        return $this->urlParams;
    }

    /**
     * Set the URL parameter values.
     *
     * Should be a key => value array, for example:
     *      [
     *        'id' => 123,
     *      ]
     */
    public function setUrlParams(?array $urlParams): Http
    {
        $this->urlParams = $urlParams;
        return $this;
    }

    /**
     * Get the request headers
     */
    public function requestHeaders(): ?array
    {
        return $this->requestHeaders;
    }

    /**
     * Set the request headers.
     *
     * Should be a key => value array, for example:
     *      [
     *        'Accept' => 'application/json'
     *      ]
     */
    public function setRequestHeaders(?array $headers): Http
    {
        $this->requestHeaders = $headers;
        return $this;
    }

    /**
     * Get the response headers
     */
    public function responseHeaders(): ?array
    {
        return $this->responseHeaders;
    }

    /**
     * Set the response headers.
     *
     * Should be a key => value array, for example:
     *      [
     *        'Content-Type' => 'application/json'
     *      ]
     */
    public function setResponseHeaders(?array $headers): Http
    {
        $this->responseHeaders = $headers;
        return $this;
    }

    /**
     * Get the response status
     */
    public function responseStatus(): ?int
    {
        return $this->responseStatus;
    }

    /**
     * Set the response status code.
     */
    public function setResponseStatus(?int $status): Http
    {
        $this->responseStatus = $status;
        return $this;
    }

    /**
     * Searialize meta information into array
     */
    public function toArray(): array
    {
        return [
            'method' => $this->method(),
            'url' => $this->url(),
            'url_params' => $this->urlParams(),
            'request_headers' => $this->requestHeaders(),
            'response_headers' => $this->responseHeaders(),
            'response_status' => $this->responseStatus(),
        ];
    }
}
