Feature: DELETE Requests

  Scenario: Delete request
    Given I send a delete request to "users/1"
    Then I expect the response to contain
    | Content-Type | Cache-Control |
    | application\/json; charset=utf-8 | no-cache |
    And I expect the status code to be 200




