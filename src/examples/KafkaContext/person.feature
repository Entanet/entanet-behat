Feature: Store a person in the database
  In order to manipulate people in this application
  As people are created in other systems
  We will need to listen people events and store them in the database

  Scenario: Listen to person created event and store in the people table
    Given I am running the console commands
      | name |
      | command:test |
    When An event is published to "person_created"
      | name | age |
      | tom  | 20  |
      | mark | 25  |
    Then I should have two rows in the "people" table
      | name | age |
      | tom  | 20  |
      | mark | 25  |