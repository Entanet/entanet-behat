<?php

namespace Entanet\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Laracasts\Behat\Context\DatabaseTransactions;
use Laracasts\Behat\Context\Migrator;
use PHPUnit\Framework\Assert;
use Illuminate\Support\Facades\DB;

class DatabaseContext implements Context
{
    use Migrator, DatabaseTransactions;

    /**
     * @BeforeScenario
     */
    public function setUp()
    {

    }

    /**
     * @Then I should have two rows in the :tableName table
     */
    public function iShouldHaveTwoRowsInTheTable($tableName, TableNode $table)
    {
        foreach ($table as $row) {
            Assert::assertNotEquals(false, DB::table($tableName)->where($row)->first());
        }
    }

    /**
     * @AfterScenario
     */
    public function tearDown()
    {

    }
}