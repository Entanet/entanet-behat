Feature: POST Requests

  Scenario: POST to end point and get result back
    Given I post the following payload to "posts"
    | title | body | user_id |
    | TestTitle | TestBody | TestId |
    Then I expect the response to contain
    | id  | Connection | Server |
    | 101 | keep-alive | cloudflare |
    And I expect the status code to be 200

