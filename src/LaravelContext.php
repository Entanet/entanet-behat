<?php

namespace Entanet\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Illuminate\Support\Facades\Artisan;

class LaravelContext implements Context
{
    protected $adapter;

    /**
     * @BeforeScenario
     */
    public function setUp()
    {

    }

    /**
     * @Given I am running the console commands
     */
    public function iAmRunningTheConsoleCommands(TableNode $table)
    {
        foreach ($table as $command) {
            Artisan::call($command['name']);
        }
    }

    /**
     * @AfterScenario
     */
    public function tearDown()
    {

    }
}