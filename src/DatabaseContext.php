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
}
