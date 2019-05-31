Feature: PUT Requests

  Scenario: PUT request
    Given I send a put request to "posts/1"
    | id | title | body | userId |
    | 1  | Foo   | Bar  | 1      |
    Then I expect the response to contain
    | id |
    | 1  |
    And I expect the status code to be 200



