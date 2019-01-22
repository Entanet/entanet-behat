Feature: GET Requests

  Scenario: GET body and headers of html
    Given I request the api path "users/1"
    Then I expect the response to contain
    | name           | Connection | email             |
    | Leanne Graham  | keep-alive  | Sincere@april.biz  |
    And the response should be JSON
    And I expect the status code to be 200





