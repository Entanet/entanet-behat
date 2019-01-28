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
     * @Given I am running the Seeders
     */
    public function iAmSeedingTheDatabase(TableNode $table)
    {
        foreach ($table as $seeder) {
            Artisan::call('db:seed --class='.$seeder);
        }
    }

    /**
     * @AfterScenario
     */
    public function tearDown()
    {

    }
}