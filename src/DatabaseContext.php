<?php

namespace Entanet\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Illuminate\Support\Facades\DB;
use Laracasts\Behat\Context\DatabaseTransactions;
use Laracasts\Behat\Context\Migrator;
use PHPUnit\Framework\Assert;
use Exception;
use InvalidArgumentException;

/**
 * Class DatabaseContext
 * @package Entanet\Behat
 */
class DatabaseContext implements Context
{

    public $model;
    /**
     * Migrate refresh
     */
    use Migrator, DatabaseTransactions;

    /**
     * @Given I have the following in the :tableName table
     */
    public function iHaveTheFollowingInTheTable($tableName, TableNode $table)
    {
        foreach ($table as $row) {
            // Find the model for the table
            $modelName = 'App\\Models\\' . studly_case(str_singular($tableName));

            if (!class_exists($modelName)) {
                throw new Exception('Model does not exist ' . $modelName);
            }

            // Run factory to generate data
            try {
                factory($modelName)->create($row);
            } catch (InvalidArgumentException $e) {
                throw new Exception('No database factory defined for ' . $tableName);
            }
        }
    }

    /**
     * @Given the :tableName table is empty
     * @param $tableName
     * @throws Exception
     */
    public function theTableIsEmpty($tableName)
    {
        Assert::assertEmpty(DB::table($tableName)->get());
    }

    /**
     * @Then I should have the following in the :tableName table
     */
    public function iShouldHaveTheFollowingInTheTable($tableName, TableNode $table)
    {
        foreach ($table as $row) {
            $found = DB::table($tableName)->where($row)->first();

            if (!$found) {
                throw new Exception('Row not found in ' . $tableName . ' : ' . json_encode($row));
            }
        }
    }

    /**
     * @Then the following records in :tableName should be deleted
     */
    public function assertDeleted($tableName, TableNode $table)
    {
        foreach ($table as $row) {
            $found = DB::table($tableName)->where($row)->pluck('deleted_at');

            Assert::assertNotNull($found);

            if (!$found) {
                throw new Exception('Row not found in ' . $tableName . ' : ' . json_encode($row));
            }
        }
    }

    /**
     * @Then the :tableName table should be empty
     * @param $tableName
     */
    public function theTableShouldBeEmpty($tableName)
    {
        Assert::assertEmpty(DB::table($tableName)->get());
    }


    /**
     * @Then there should be :count records in :table
     */
    public function assertCountOfDatabaseRecords($count, $table)
    {

        $records = DB::table($table)->get()->count();

        Assert::assertCount($count, $records);
    }

}
