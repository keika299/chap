<?php

namespace keika299\ConohaAPI\Common\Network;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as Psr7Response;
use keika299\ConohaAPI\Common\Exceptions\Network\ExceptionFactory;

/**
 * Class Request
 *
 * Create API request and exec it.
 *
 * @package keika299\ConohaAPI\Common\Network
 */
class Request
{
    private $baseURI;
    private $method;
    private $uri;
    private $headers;
    private $options;
    private $query;

    /**
     * Request constructor.
     */
    public function __construct()
    {
        $this->baseURI = '';
        $this->method = 'GET';
        $this->uri = '';
        $this->headers = array();
        $this->options = array();
        $this->query = '';
    }

    /**
     * Exec API request.
     *
     * This function have to run after set all params.
     * If $_ENV['IS_TEST'] has value, this function return mock.
     *
     * @return Response
     * @throws \keika299\ConohaAPI\Common\Exceptions\IConohaAPIException
     */
    public function exec()
    {
        if (0 < count($this->headers)) {
            $this->options['headers'] = $this->headers;
        }

        $client = $this->getClient();

        try {
            $response = $client->request($this->method, $this->uri.$this->query, $this->options);
            return new Response($response);
        }
        catch (\Exception $e) {
            throw ExceptionFactory::build($e);
        }
    }

    /**
     * Set request method.
     * $method allow HTTP/1.1 request methods.
     *
     * @param string $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = strtoupper($method);
        return $this;
    }

    /**
     * Set base URI.
     *
     * $baseURI usually begin 'https://' or 'http://'.
     * $baseURI expect last character is not with '/'.
     *
     * @param string $baseURI
     * @return $this
     */
    public function setBaseURI($baseURI)
    {
        $this->baseURI = $baseURI;
        return $this;
    }

    /**
     * Set URI.
     *
     * $uri is NOT completely URI.
     * This param connect with base URI.
     * $uri expect to start with '/'.
     *
     * @param string $uri
     * @return $this
     */
    public function setURI($uri)
    {
        $this->uri = $uri;
        return $this;
    }

    /**
     * Add request header value.
     *
     * $key is parameter, and $value is value of that parameter.
     * You want to add 'X-Auth-Token', you can use setToken func.
     * You want to add 'Accept', you can use setAccept func.
     *
     * @param string $key
     * @param string $value
     * @return $this
     */
    public function addHeader($key, $value)
    {
        $this->headers[$key] = $value;
        return $this;
    }

    /**
     * Set 'Accept' request header.
     *
     * @param string $value
     * @return $this
     */
    public function setAccept($value)
    {
        $this->headers['Accept'] = $value;
        return $this;
    }

    /**
     * Set 'Content-Type' request header
     *
     * @param string $value
     * @return $this
     */
    public function setContentType($value)
    {
        $this->headers['Content-Type'] = $value;
        return $this;
    }

    /**
     * Set request body.
     *
     * You can use this function when request 'POST' or 'PUT' method.
     *
     * @param string $body
     * @return $this
     */
    public function setBody($body)
    {
        $this->options['body'] = $body;
        return $this;
    }

    /**
     * Set request Json.
     *
     * Array transform to json data, and set request body.
     *
     * @param array $array
     * @return $this
     */
    public function setJson(array $array)
    {
        $this->options['json'] = $array;
        return $this;
    }

    /**
     * Set request URI query.
     *
     * $array have to be associative array.
     *
     * @param array $array
     * @return $this
     */
    public function setQuery(array $array)
    {
        if (sizeof($array) === 0) {
            return $this;
        }

        $query = '?';

        foreach ($array as $item => $value) {
            $query .= $item . '=' . $value . '&';
        }

        $this->query = mb_substr($query, 0, -1, "UTF-8");

        return $this;
    }

    /**
     * Set 'X-Auth-Token' request header.
     *
     * @param string $token
     * @return $this
     */
    public function setToken($token)
    {
        $this->headers['X-Auth-Token'] = $token;
        return $this;
    }

    /**
     * @return Client
     */
    private function getClient()
    {
        return getenv('IS_TEST') ? $this->createMockClient() : new Client(['base_uri' => $this->baseURI]);
    }

    /**
     * @return Client
     */
    private function createMockClient()
    {
        $body = '{"checkKey": "checkValue", "access": {"token": {"id": "sample00d88246078f2bexample788f7"}}}';

        $mockResponse = [
            new Psr7Response(200, [], $body)
        ];
        $mockHandler = new MockHandler($mockResponse);
        $handlerStack = HandlerStack::create($mockHandler);
        return new Client(['handler' => $handlerStack]);
    }
}
