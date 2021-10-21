<?php

namespace Tail\Meta;

use stdClass;

class Http
{

    /** @var string HTTP method */
    protected $method;

    /** @var string URL for request */
    protected $url;

    /** @var array Parameter values used in URL */
    protected $urlParams = [];

    /** @var array Headers for request */
    protected $requestHeaders = [];

    /** @var array Headers for response */
    protected $responseHeaders = [];

    /** @var int Response status */
    protected $responseStatus;

    /** @var string Remote address */
    protected $remoteAddress;

    public function __construct()
    {
        $this->setMethod($this->getServerVar('REQUEST_METHOD'));
        $this->setUrl($this->getServerVar('REQUEST_URI'));
        $this->setRemoteAddress($this->getServerVar('REMOTE_ADDR'));
        $this->setRequestHeaders(array_filter([
            'host' => $this->getServerVar('HTTP_HOST'),
            'connection' => $this->getServerVar('HTTP_CONNECTION'),
            'user_agent' => $this->getServerVar('HTTP_USER_AGENT'),
            'cache_control' => $this->getServerVar('HTTP_CACHE_CONTROL'),
            'sec_ch_ua' => $this->getServerVar('HTTP_SEC_CH_UA'),
            'sec_ch_ua_mobile' => $this->getServerVar('HTTP_SEC_CH_UA_MOBILE'),
            'upgrade_insecure_requests' => $this->getServerVar('HTTP_UPGRADE_INSECURE_REQUESTS'),
            'accept' => $this->getServerVar('HTTP_ACCEPT'),
            'sec_fetch_site' => $this->getServerVar('HTTP_SEC_FETCH_SITE'),
            'sec_fetch_mode' => $this->getServerVar('HTTP_SEC_FETCH_MODE'),
            'sec_fetch_user' => $this->getServerVar('HTTP_SEC_FETCH_USER'),
            'sec_fetch_dest' => $this->getServerVar('HTTP_SEC_FETCH_DEST'),
            'accept_encoding' => $this->getServerVar('HTTP_ACCEPT_ENCODING'),
            'accept_language' => $this->getServerVar('HTTP_ACCEPT_LANGUAGE'),
        ]));
    }

    /**
     * Deserialize properties into Http
     */
    public function fillFromArray(array $properties): Http
    {
        if (isset($properties['method'])) {
            $this->setMethod($properties['method']);
        };

        if (isset($properties['url'])) {
            $this->setUrl($properties['url']);
        }

        if (isset($properties['remote_address'])) {
            $this->setRemoteAddress($properties['remote_address']);
        }

        if (isset($properties['url_params'])) {
            $this->setUrlParams($properties['url_params']);
        }

        if (isset($properties['request_headers'])) {
            $this->setRequestHeaders($properties['request_headers']);
        }

        if (isset($properties['response_headers'])) {
            $this->setResponseHeaders($properties['response_headers']);
        }

        if (isset($properties['response_status'])) {
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
     * Set the remote address.
     */
    public function setRemoteAddress(?string $address): Http
    {
        if ($address !== null) {
            $this->remoteAddress = $address;
        }

        return $this;
    }

    /**
     * Get the remote address
     */
    public function remoteAddress(): ?string
    {
        return $this->remoteAddress;
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
        $urlParams = $this->urlParams();
        if (count($urlParams) === 0) {
            $urlParams = new stdClass();
        }

        $requestHeaders = $this->requestHeaders();
        if (count($requestHeaders) === 0) {
            $requestHeaders = new stdClass();
        }

        $responseHeaders = $this->responseHeaders();
        if (count($responseHeaders) === 0) {
            $responseHeaders = new stdClass();
        }

        return [
            'method' => $this->method(),
            'url' => $this->url(),
            'url_params' => $urlParams,
            'request_headers' => $requestHeaders,
            'response_headers' => $responseHeaders,
            'response_status' => $this->responseStatus(),
            'remote_address' => $this->remoteAddress(),
        ];
    }
    /**
     * Get $_SERVER variables if available
     */
    private function getServerVar($name): ?string
    {
        if (!isset($_SERVER[$name])) {
            return null;
        }
        return $_SERVER[$name];
    }
}
