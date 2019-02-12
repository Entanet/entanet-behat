Feature: PATCH Requests

  Scenario: Patch request
    Given I send a patch request to "posts/1"
      | title |
      | Foo   |
    Then I expect the response to contain
    | userId | id | Connection | Cache-Control|
    | 1      | 1  | keep-alive | no-cache     |



