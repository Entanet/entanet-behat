<?php

namespace Entanet\Behat;

require_once __DIR__ . '/../../vendor/phpunit/phpunit/src/Framework/Assert/Functions.php';

use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Imbo\BehatApiExtension\Context\ApiContext as BaseContext;
use GuzzleHttp\Client;

/**
 * Defines application features from the specific context.
 */
class APIContext extends BaseContext implements Context
{

    public $response;
    public $_client;
    protected $payload;
    protected $requestPath;
    private $auth;

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->requestPath = 'https://jsonplaceholder.typicode.com/';
        $this->_client = new Client(['base_uri' => $this->requestPath]);

        $this->auth = ['username' => 'placeholder', 'password' => 'placeholder'];
    }

    /**
     * @Then /^the response should be UTF-8 encoded/
     */
    public function theResponseShouldBeUTF8Encoded()
    {
        $data = utf8_decode($this->response);
        if (empty($data)) {
            throw new Exception("Response was not UTF-8 encoded\n" . $this->response);
        }
    }

    /**
     * @Then /^the response should be HTML/
     */
    public function theResponseShouldBeHTML()
    {
        $data = html_entity_decode($this->response);
        if (empty($data)) {
            throw new Exception("Response was not HTML\n" . $this->response);
        }
    }

    /**
     * @Then /^the response should be JSON$/
     */
    public function theResponseShouldBeJson()
    {
        $data = json_decode($this->response);
        if (empty($data)) {
            throw new Exception("Response was not JSON\n" . $this->response);
        }
    }

    /**
     * @Given the following request exists
     * @param TableNode $table
     * @return mixed
     */
    public function setPayload(TableNode $table)
    {
        $keys = $table->getRow(0);

        $values = $table->getRow(1);

        $request = array_combine($keys, $values);

        $this->payload = json_encode($request);
    }

    /**
     * @Then I expect the response to contain
     * @param TableNode $table
     * @throws
     */
    public function iExpectTheFollowingHeadersToBePresent(TableNode $table)
    {
        $this->assertPayload($table);
    }

    public function assertPayload(TableNode $table)
    {
        $this->payload = $this->tableToArray($table);

        $body = $this->response;

        foreach ($this->payload as $key => $val) {
            assertContains($key, $body);
            assertContains($val, $body);
        }
    }


    public function tableToArray(TableNode $table)
    {
        $keys = $table->getRow(0);

        $values = $table->getRow(1);

        $array = array_combine($keys, $values);

        return $array;
    }

    public function tableToJson(TableNode $table)
    {
        $keys = $table->getRow(0);

        $values = $table->getRow(1);

        $array = array_combine($keys, $values);

        $array = json_encode($array);

        return $array;
    }

    /**
     * @Given I request the api path :path
     * @param $path
     * @return mixed
     */
    public function getRequestAPI($path)
    {
        $this->request = $this->_client->get($path, [
            'track_redirects' => true
        ]);

        $this->response = $this->combineResponses();

        return $this->response;
    }

    public function combineResponses()
    {
        $this->response = [];

        $body = json_decode($this->request->getBody());
        $headers = json_encode($this->request->getHeaders());

        $this->response = array_add($this->response, 'Body', $body);
        $this->response = array_add($this->response, 'Headers', $headers);
        $this->response = json_encode($this->response);

        return $this->response;
    }

    /**
     * @Given I request the body of api path :path
     * @param $path
     */
    public function requestAPIBody($path)
    {
        $this->request = $this->_client->get($path);

        $bod = json_decode($this->request->getBody(), true);

        $this->response = json_encode($bod);
    }

    /**
     * @Given I request the headers of :path
     * @param $path
     */
    public function iRequestTheHeadersOf($path)
    {
        $this->request = $this->_client->get($path);

        $headers = $this->request->getHeaders();

        $this->response = json_encode($headers);
    }

    /**
     * @When I expect the status code to be :code
     * @param $code
     */
    public function iRequestStatusCodeOf($code)
    {
        $statusCode = $this->request->getStatusCode();

        assertEquals($code, $statusCode);
    }

    /**
     * @Given I post the following payload to :path
     * @param $path
     * @param TableNode $table
     */
    public function iPostTheFollowingPayloadTo($path, TableNode $table)
    {
        $this->payload = $this->tableToArray($table);

        $this->request = $this->postRequestAPI($path, $this->payload);

        $this->response = json_encode($this->request);
    }


    public function postRequestAPI($path, $payload)
    {
        $this->request = $this->_client->post($path, $payload);

        $this->response = $this->combineResponses();

        return $this->response;
    }

    /**
     * @Given I send a delete request to :path
     * @param $path
     */
    public function deleteRequest($path)
    {
        $this->request = $this->_client->delete($path);

        $code = $this->request->getStatusCode();
        $headers = $this->request->getHeaders();

        $response = array_add($headers, 'Status-Code', $code);

        $this->response = json_encode($response);
    }

    /**
     * @Given I send a patch request to :path
     * @param $path
     * @param TableNode $table
     */
    public function patchRequest($path, TableNode $table)
    {
        $this->payload = $this->tableToArray($table);
        $this->request = $this->_client->patch($path, $this->payload)->withHeader('Content-type', "application/json; charset=UTF-8");

        $this->response = $this->combineResponses();
    }

    /**
     * @Given I send a put request to :path
     * @param $path
     * @param TableNode $table
     */
    public function putRequest($path, TableNode $table)
    {
        $this->payload = $this->tableToArray($table);
        $this->request = $this->_client->put($path, $this->payload);

        $this->response = $this->combineResponses();
    }
}