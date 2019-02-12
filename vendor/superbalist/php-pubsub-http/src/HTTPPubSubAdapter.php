<?php

namespace Superbalist\PubSub\HTTP;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Superbalist\PubSub\PubSubAdapterInterface;

class HTTPPubSubAdapter implements PubSubAdapterInterface
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var PubSubAdapterInterface
     */
    protected $adapter;

    /**
     * @var string
     */
    protected $userAgent = 'superbalist/php-pubsub-http';

    /**
     * @param Client $client
     * @param string $uri
     * @param PubSubAdapterInterface $adapter
     */
    public function __construct(Client $client, $uri, PubSubAdapterInterface $adapter)
    {
        $this->client = $client;
        $this->uri = $uri;
        $this->adapter = $adapter;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set the uri of the service where messages will be published to.
     *
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    /**
     * Return the uri of the service where messages will be published to.
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Return the adapter through which subscribes will be proxied to.
     *
     * @return PubSubAdapterInterface
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Set the user-agent HTTP header.
     *
     * @param string $userAgent
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;
    }

    /**
     * Return the user-agent HTTP header.
     *
     * @return string
     */
    public function getUserAgent()
    {
        return $this->userAgent;
    }

    /**
     * Return an array of headers to send with every HTTP request.
     *
     * @return array
     */
    public function getGlobalHeaders()
    {
        return [
            'User-Agent' => $this->userAgent,
        ];
    }

    /**
     * Create an HTTP request object.
     *
     * @param string $method
     * @param string $endpoint
     * @param mixed $body
     * @param array $headers
     *
     * @return Request
     */
    protected function createRequest($method, $endpoint, $body = null, array $headers = [])
    {
        $uri = rtrim($this->uri, '/') . '/' . trim($endpoint, '/');
        $headers = array_merge($this->getGlobalHeaders(), $headers);
        return new Request($method, $uri, $headers, $body);
    }

    /**
     * Send an HTTP request.
     *
     * @param RequestInterface $request
     *
     * @return array
     */
    protected function sendRequest(RequestInterface $request)
    {
        $response = $this->client->send($request);
        /* @var \Psr\Http\Message\ResponseInterface $response */

        return json_decode($response->getBody(), true);
    }

    /**
     * Make an HTTP POST request.
     *
     * @param string $endpoint
     * @param array $data
     *
     * @return mixed
     */
    public function post($endpoint, array $data = [])
    {
        $body = json_encode($data);
        $request = $this->createRequest('POST', $endpoint, $body, ['Content-Type' => 'application/json']);
        return $this->sendRequest($request);
    }

    /**
     * Subscribe a handler to a channel.
     *
     * @param string $channel
     * @param callable $handler
     */
    public function subscribe($channel, callable $handler)
    {
        $this->adapter->subscribe($channel, $handler);
    }

    /**
     * Publish a message to a channel.
     *
     * @param string $channel
     * @param mixed $message
     */
    public function publish($channel, $message)
    {
        $this->publishBatch($channel, [$message]);
    }

    /**
     * Publish multiple messages to a channel.
     *
     * @param string $channel
     * @param array $messages
     */
    public function publishBatch($channel, array $messages)
    {
        $this->post(sprintf('messages/%s', $channel), ['messages' => $messages]);
    }
}
