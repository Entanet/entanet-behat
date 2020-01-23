<?php

namespace Entanet\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use App;
use Mockery;
use Superbalist\PubSub\Adapters\LocalPubSubAdapter;
use Exception;
use Superbalist\PubSub\PubSubAdapterInterface;
use ReflectionClass;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

/**
 * Class KafkaContext
 * @package Entanet\Behat
 */
class KafkaContext implements Context
{
    /**
     * @var PubSubAdapterInterface
     */
    public static $adapter;

    /**
     * @var Keep hold of published events
     */
    public static $events;

    /**
     * @BeforeSuite
     */
    public static function prepare()
    {
        $mock = Mockery::mock(LocalPubSubAdapter::class)->makePartial();
        App::instance(PubSubAdapterInterface::class, $mock);
        KafkaContext::$adapter = app(PubSubAdapterInterface::class);
    }

    /**
     * @BeforeScenario
     * @throws
     */
    public function setUp()
    {
        //$this->adapter = app(PubSubAdapterInterface::class);

        try {
            $this->setupFakeSubscribers();
        } catch (\ReflectionException $e) {
            throw new \ReflectionException('Could not set up subscribers.');
        }
    }

    /**
     * @Given I am running the :subscriber Kafka subscriber
     * @param $subscriber
     */
    public function iAmRunningTheKafkaSubscriber($subscriber)
    {
        Artisan::call('kafka:subscribe', ['alias' => $subscriber]);
    }

    /**
     * @When the following events are published to :topic
     * @param $topic
     * @param TableNode $table
     */
    public function theFollowingEventsArePublishedTo($topic, TableNode $table)
    {
        foreach ($table as $row) {
            foreach ($row as $key => $value) {
                if ($value == 'true') {
                    $row[$key] = true;
                } else if ($value == 'false') {
                    $row[$key] = false;
                }
                if (Str::contains($key, '.')) {
                    $row = array_merge_recursive($row, $this->convertDotsToArray($key, $value));
                }
            }

            KafkaContext::$adapter->publish($topic, $row);
        }
    }

    /**
     * @Then the following events should be published to :topic
     * @param $topic
     * @param TableNode $table
     * @throws
     */
    public function theFollowingEventsShouldBePublishedTo($topic, TableNode $table)
    {
        // Get events that have been published
        $events = KafkaContext::$events;

        // Foreach expected published event
        foreach ($table as $row) {
            $found = false;

            if (array_key_exists($topic, $events)) {
                $eventsPublished = $events[$topic];
                foreach ($eventsPublished as $key => $event) {
                    if (count(array_intersect_assoc($row, $event)) == count($row)) {
                        $found = true;

                        // Count each row once
                        unset($events[$topic][$key]);
                    }
                }
            }

            // Event not found
            if ($found == false) {
                throw new Exception('Event not published - ' . $topic . ' : ' . json_encode($row));
            }
        }
    }
    /**
     * Helper for turning dots into array
     * @param $key
     * @param $value
     * @return array
     */
    private function convertDotsToArray($key, $value)
    {
        if (!str_contains($key, ".")) {
            return [
                $key => $value
            ];
        }

        $segments = explode(".", $key);
        $key = $segments[0];
        array_shift($segments);

        return [
            $key => $this->convertDotsToArray(implode(".", $segments), $value)
        ];
    }

    /**
     * Setup fake subscribers for events that come in
     * @throws \ReflectionException
     */
    private function setupFakeSubscribers()
    {
        // Reset events
        KafkaContext::$events = array();
        $mock = KafkaContext::$adapter;

        // Make the subscribers property visible
        $reflect = new ReflectionClass($mock);
        $property = $reflect->getProperty('subscribers');
        $property->setAccessible(true);

        // Mock the get subscribers method to include a fake counter
        $mock->allows('getSubscribersForChannel')->andReturnUsing(
            function($topic) use ($property, $mock) {
                $subscribers = $property->getValue($mock);

                $existing = array();
                if (array_key_exists($topic, $subscribers)) {
                    $existing = $subscribers[$topic];
                }

                $fake = function($message) use ($topic) {
                    KafkaContext::$events[$topic][] = $message;
                };

                $existing[] = $fake;

                return $existing;
            }
        );
    }

    /**
     * @AfterScenario
     */
    public function tearDown()
    {

    }
}