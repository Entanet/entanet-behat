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
     * @When An event is published to :topic
     */
    public function anEventIsPublishedTo($topic, TableNode $table)
    {
        foreach ($table as $row) {
            $this->adapter->publish($topic, $row);
        }
    }

    /**
     * @AfterScenario
     */
    public function tearDown()
    {

    }
}