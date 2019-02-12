# php-pubsub-http

An HTTP adapter for the [php-pubsub](https://github.com/Superbalist/php-pubsub) package.

[![Author](http://img.shields.io/badge/author-@superbalist-blue.svg?style=flat-square)](https://twitter.com/superbalist)
[![Build Status](https://img.shields.io/travis/Superbalist/php-pubsub-http/master.svg?style=flat-square)](https://travis-ci.org/Superbalist/php-pubsub-http)
[![StyleCI](https://styleci.io/repos/67334430/shield?branch=master)](https://styleci.io/repos/67334430)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Packagist Version](https://img.shields.io/packagist/v/superbalist/php-pubsub-http.svg?style=flat-square)](https://packagist.org/packages/superbalist/php-pubsub-http)
[![Total Downloads](https://img.shields.io/packagist/dt/superbalist/php-pubsub-http.svg?style=flat-square)](https://packagist.org/packages/superbalist/php-pubsub-http)

This adapter assumes that you have a HTTP service which accepts an array of messages POSTed to a /messages/(channel) end-point.

## Installation

```bash
composer require superbalist/php-pubsub-http
```

## Usage

```php
putenv('GOOGLE_APPLICATION_CREDENTIALS=' . __DIR__ . '/../your-gcloud-key.json');

// create the underlying adapter which is going to be decorated
$pubSubClient = new \Google\Cloud\PubSub\PubSubClient([
    'projectId' => 'your-project-id-here',
]);

$subscribeAdapter = new \Superbalist\PubSub\GoogleCloud\GoogleCloudPubSubAdapter($pubSubClient);

// now create our decorator
// the decorator will proxy subscribe calls straight to the $subscribeAdapter
// publish calls will be POSTed to the service uri
$client = new \GuzzleHttp\Client();

$adapter = new \Superbalist\PubSub\HTTP\HTTPPubSubAdapter($client, 'https://127.0.0.1', $subscribeAdapter);

// consume messages
$adapter->subscribe('my_channel', function ($message) {
    var_dump($message);
});

// publish messages
$adapter->publish('my_channel', 'HELLO WORLD');
$adapter->publish('my_channel', ['hello' => 'world']);

// publish multiple messages
$messages = [
    'Hello World!',
    ['hello' => 'world'],
];
$adapter->publishBatch('my_channel', $messages);
```

## Examples

The library comes with [examples](examples) for the adapter and a [Dockerfile](Dockerfile) for
running the example scripts.

Run `make up`.

You will start at a `bash` prompt in the `/opt/php-pubsub` directory.

If you need another shell to publish a message to a blocking consumer, you can run `docker-compose run php-pubsub-http /bin/bash`

To run the examples:
```bash
$ ./run_consumer.sh
$ ./run_publisher.sh (in a separate shell)
```
