<?php

namespace Entanet\Behat;

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\Queue;
use Illuminate\Support\Facades\Artisan;

class LaravelContext implements Context
{
    /**
     * @BeforeSuite
     */
    public static function prepare()
    {
        Notification::fake();
        Queue::fake();

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
     * @Then I assert nothing was pushed to the queue
     */
    public function assertNothingPushedToQueue()
    {
        Queue::assertNothingPushed();
    }

    /**
     * @Then I assert job :job was pushed to the queue :queue
     *
     * Note: Syntax for Job is JobName::class.
     */
    public function assertJobPushedToQueue($job, $queue)
    {
        Queue::assertPushedOn($queue, $job);
    }



    /**
     * @Then I assert job :job was pushed :count times
     *
     * Note: Syntax for Job is JobName::class.
     */
    public function assertJobPushedCount($job, $count)
    {
        Queue::assertPushed($job, $count);
    }

    /**
     * @Then I assert job :job was not pushed
     *
     * Note: Syntax for Job is JobName::class.
     */
    public function assertJobNotPushed($job)
    {
        Queue::assertNotPushed($job);
    }


    /**
     * @Then I assert no notifications were sent
     */
    public function assertNoNotificationSent()
    {
        Notification::assertNothingSent();
    }

    /**
     * @Then I assert a :class notification was sent to :user
     *
     * Note: Syntax for Notification is ClassName::class.
     */
    public function assertNotificationSent($notification, $user)
    {
        Notification::assertSentTo([$user], $notification);
    }

    /**
     * @Then I assert a :class notification was not sent to :user
     *
     * Note: Syntax for Notification is ClassName::class.
     */
    public function assertNotificationNotSent($notification, $user)
    {
        Notification::assertNotSentTo([$user], $notification);
    }


    /**
     * @Then the log file named :file should contain :text
     */
    public function assertLogContains($file, $text)
    {
        $log = storage_path("logs/$file.log");

        $contents = file_get_contents($log);

        assertContains($text, $contents);
    }

    /**
     * @AfterScenario
     */
    public function tearDown()
    {

    }
}