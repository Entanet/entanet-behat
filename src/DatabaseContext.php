<?php

namespace Entanet\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Illuminate\Support\Facades\DB;
use Laracasts\Behat\Context\DatabaseTransactions;
use Laracasts\Behat\Context\Migrator;
use PHPUnit\Framework\Assert;

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
     * @Given I have the following in the :tableName table
     */
    public function iHaveTheFollowingInTheTable($tableName, TableNode $table)
    {
        foreach ($table as $row) {
            $modelName = 'App\\Models\\' . studly_case(str_singular($tableName));
            factory($modelName)->create($row);
        }
    }

    /**
     * @Then I should have the following in the :tableName table
     */
    public function iShouldHaveTheFollowingInTheTable($tableName, TableNode $table)
    {
        foreach ($table as $row) {
            Assert::assertNotEquals(false, DB::table($tableName)->where($row)->first());
        }
    }

    /**
     * @Given I assert the object is stored in the database successfully in :table, with :value for :column
     */
    public function iAssertThisIsStoredInTheDatabaseSuccessfully($model, $column, $value)
    {
        $stored = DB::table($model)->where($column, $value)->count();

        if ($stored) {
            return true;
        }
        return false;
    }

    /**
     * @AfterScenario
     */
    public function tearDown()
    {

    }
}
