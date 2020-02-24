# **Entanet Behat**
This package is designed specifically for Entanet's Software QA department to aid with automation of features.

# Installing

Require the entanet behat package
```
composer require entanet/entanet-behat --dev
```

Add the following to the config/app.php providers array
```
Superbalist\LaravelPubSub\PubSubServiceProvider::class
```

Publish the behat environment and YAML file
```
php artisan vendor:publish --provider="Entanet\Behat\BehatServiceProvider"
```

Check the commands available
```
vendor/bin/behat -dl
```

# Usage

## Table of Contents

### Running
- [Pipeline Suite](#pipeline-suite)
- [UI Suite](#ui-suite)

### Database
- [Insert a row](#insert-a-row)
- [Check a row exists](#check-a-row-exists)

### Kafka
- [Publish an event](#publish-an-event)
- [Check an event](#check-an-event)

#### Pipeline Suite
This includes Kafka, Database and API testing. To be run as part of the deployment pipeline. 
A failed test will break a build. All artisan commands with pubsub will be run automatically 
for each scenario.
```
vendor/bin/behat --suite=pipeline
```

#### UI Suite
This includes UI testing. To be run locally and not within a pipeline.
```
vendor/bin/behat --suite=ui
```

#### Insert a row
Seed the database with rows
```gherkin
Given I have the following in the "users" table
| name | email                |
| Tom  | tomos.lloyd@cityfibre.com |
| Ryan | ryan.ralphs@cityfibre.com |
```

#### Check a row exists
Check a row exists after running some code
```gherkin
Then I should have the following in the "users" table
| name | email                |
| Tom  | tomos.lloyd@cityfibre.com |
| Ryan | ryan.ralphs@cityfibre.com |
```

#### Publish an event
Publish an event to a topic
```gherkin
When The following events are published to "user-created"
| name | email                |
| Tom  | tomos.lloyd@cityfibre.com |
| Ryan | ryan.ralphs@cityfibre.com |
```

#### Check an event
Check an event has been created
```gherkin
Then The following events should be published to "user-created"
| name | email                |
| Tom  | tomos.lloyd@cityfibre.com |
| Ryan | ryan.ralphs@cityfibre.com |
```

# Examples
Consume an event and check that a row exists in the database
```gherkin
Feature: Store a person in the database
  In order to manipulate people in this application
  As people are created in other systems
  We will need to listen people events and store them in the database

  Scenario: Listen to person created event and store in the people table
    When The following events are published to "user-created"
      | name | email                 |
      | Tom  | tomos.lloyd@enta.net  |
      | Ryan | ryan.ralphs@enta.net  |
    Then I should have the following in the "users" table
      | name | email                 |
      | Tom  | tomos.lloyd@enta.net  |
      | Ryan | ryan.ralphs@enta.net  |
```

Consume an event and check that another event was published
```gherkin
Feature: Store a person in the database
  In order to manipulate people in this application
  As people are created in other systems
  We will need to listen people events and store them in the database

  Scenario: Listen to person created event and store in the people table
    When The following events are published to "user-created"
      | name | email                 |
      | Tom  | tomos.lloyd@cityfibre.com  |
      | Ryan | ryan.ralphs@cityfibre.com  |
    Then I should have the following in the "users" table
      | name | email                 |
      | Tom  | tomos.lloyd@cityfibre.com  |
      | Ryan | ryan.ralphs@cityfibre.com  |
```

