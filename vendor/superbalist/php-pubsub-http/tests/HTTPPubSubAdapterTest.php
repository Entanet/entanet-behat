<?php

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Mockery;
use PHPUnit\Framework\TestCase;
use Superbalist\PubSub\HTTP\HTTPPubSubAdapter;
use Superbalist\PubSub\PubSubAdapterInterface;

class HTTPPubSubAdapterTest extends TestCase
{
    public function testGetClient()
    {
        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter = new HTTPPubSubAdapter($client, 'http://127.0.0.1', $subscribeAdapter);
        $this->assertSame($client, $adapter->getClient());
    }

    public function testSetGetUri()
    {
        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter = new HTTPPubSubAdapter($client, 'http://127.0.0.1', $subscribeAdapter);
        $this->assertEquals('http://127.0.0.1', $adapter->getUri());
        $adapter->setUri('http://bleh');
        $this->assertEquals('http://bleh', $adapter->getUri());
    }

    public function testGetAdapter()
    {
        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter = new HTTPPubSubAdapter($client, 'http://127.0.0.1', $subscribeAdapter);
        $this->assertSame($subscribeAdapter, $adapter->getAdapter());
    }

    public function testSetGetUserAgent()
    {
        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter = new HTTPPubSubAdapter($client, 'http://127.0.0.1', $subscribeAdapter);
        $this->assertEquals('superbalist/php-pubsub-http', $adapter->getUserAgent());
        $adapter->setUserAgent('meh');
        $this->assertEquals('meh', $adapter->getUserAgent());
    }

    public function testGetGlobalHeaders()
    {
        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter = new HTTPPubSubAdapter($client, 'http://127.0.0.1', $subscribeAdapter);
        $adapter->setUserAgent('My UserAgent String');
        $headers = $adapter->getGlobalHeaders();
        $this->assertArrayHasKey('User-Agent', $headers);
        $this->assertEquals('My UserAgent String', $headers['User-Agent']);
    }

    public function testPost()
    {
        $request = new Request(
            'POST',
            'http://127.0.0.1/messages/test',
            [
                'User-Agent' => 'superbalist/php-pubsub-http',
                'Content-Type' => 'application/json',
            ],
            json_encode(['messages' => ['hello', 'world']])
        );

        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter = Mockery::mock(
            '\Superbalist\PubSub\HTTP\HTTPPubSubAdapter[createRequest,sendRequest]',
            [$client, 'http://127.0.0.1', $subscribeAdapter]
        );
        $adapter->shouldAllowMockingProtectedMethods();
        $adapter->shouldReceive('createRequest')
            ->withArgs([
                'POST',
                'messages/test',
                json_encode(['messages' => ['hello', 'world']]),
                ['Content-Type' => 'application/json'],
            ])
            ->once()
            ->andReturn($request);
        $adapter->shouldReceive('sendRequest')
            ->with($request)
            ->once();

        $adapter->post('messages/test', ['messages' => ['hello', 'world']]);
    }

    public function testSubscribe()
    {
        $handler = function ($message) {
        };

        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(PubSubAdapterInterface::class);
        $subscribeAdapter->shouldReceive('subscribe')
            ->withArgs([
                'test',
                $handler,
            ])
            ->once();

        $adapter = new HTTPPubSubAdapter($client, 'http://127.0.0.1', $subscribeAdapter);

        $adapter->subscribe('test', $handler);
    }

    public function testPublish()
    {
        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter = Mockery::mock(
            '\Superbalist\PubSub\HTTP\HTTPPubSubAdapter[post]',
            [$client, 'http://127.0.0.1', $subscribeAdapter]
        );
        $adapter->shouldReceive('post')
            ->withArgs([
                'messages/test',
                [
                    'messages' => [
                        [
                            'hello' => 'world',
                        ],
                    ],
                ],
            ])
            ->once();
        $adapter->publish('test', ['hello' => 'world']);
    }

    public function testPublishBatch()
    {
        $client = Mockery::mock(Client::class);
        $subscribeAdapter = Mockery::mock(PubSubAdapterInterface::class);
        $adapter = Mockery::mock(
            '\Superbalist\PubSub\HTTP\HTTPPubSubAdapter[post]',
            [$client, 'http://127.0.0.1', $subscribeAdapter]
        );
        $adapter->shouldReceive('post')
            ->withArgs([
                'messages/test',
                [
                    'messages' => [
                        'test',
                        [
                            'hello' => 'world',
                        ],
                    ],
                ],
            ])
            ->once();
        $messages = [
            'test',
            [
                'hello' => 'world',
            ],
        ];
        $adapter->publishBatch('test', $messages);
    }
}
