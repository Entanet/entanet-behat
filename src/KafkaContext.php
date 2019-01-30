<?php

namespace Entanet\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

class KafkaContext implements Context
{
    protected $adapter;

    /**
     * @BeforeScenario
     */
    public function setUp()
    {
        $this->adapter = app('pubsub')->connection('local');
    }

    /**
     * @When The following events are published to :topic
     */
    public function theFollowingEventsArePublishedTo($topic, TableNode $table)
    {
        foreach ($table as $row) {
            foreach ($row as $key => $value) {
                if (str_contains($key, '.')) {
                    $row = array_merge($row, $this->convertDotsToArray($key, $value));
                }
            }
            
            $this->adapter->publish($topic, $row);
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
     * @AfterScenario
     */
    public function tearDown()
    {

    }
}