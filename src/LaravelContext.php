<?php

namespace Entanet\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Illuminate\Support\Facades\Artisan;

class LaravelContext implements Context
{
    /**
     * @BeforeSuite
     */
    public static function prepare()
    {
        $commands = Artisan::all();
        foreach ($commands as $command) {
            if (property_exists($command, 'pubsub')) {
                Artisan::call($command->getName());
            }
        }
    }

    /**
     * @BeforeScenario
     */
    public function setUp()
    {

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