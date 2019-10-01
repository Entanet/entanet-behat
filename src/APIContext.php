<?php

namespace Entanet\Behat;

use PHPUnit\Framework\Assert;
use Behat\Behat\Context\Context;
use Illuminate\Support\Facades\Facade;
use Illuminate\Database\Eloquent\Model;
use Behat\Gherkin\Node\TableNode;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;
use Illuminate\Foundation\Testing\TestCase;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Tests\CreatesApplication;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\App;
use Mockery;
use ReflectionClass;
use Exception;

/**
 * Defines application features from the specific context.
 */
class APIContext extends TestCase implements Context
{
    use CreatesApplication;
    use MakesHttpRequests;

    public $response;
    public $_client;
    public $model;
    protected $request;
    protected $app;
    protected $mockResult;
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
        parent::__construct();

        if (!$this->app) {
            $this->refreshApplication();
        }

        $this->setUpTraits();

        foreach ($this->afterApplicationCreatedCallbacks as $callback) {
            call_user_func($callback);
        }

        Facade::clearResolvedInstances();

        Model::setEventDispatcher($this->app['events']);

        Artisan::call('migrate:fresh');

        $mock = Mockery::mock(\GuzzleHttp\Client::class);
        $mock->shouldReceive('post')->andReturn([]);
        $this->_client = App::instance(\GuzzleHttp\Client::class, $mock);
        App::instance(\GuzzleHttp\Client::class, $mock);

        $this->setUpHasRun = true;
    }

    /**
     * @Then /^the response should be UTF-8 encoded/
     * @throws
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
     * @throws
     */
    public function theResponseShouldBeHTML()
    {
        $data = html_entity_decode($this->response);
        if (empty($data)) {
            throw new Exception("Response was not HTML\n" . $this->response);
        }
    }



    /**
     * @Given I have a basic model with the following keys and values:
     */
    public function iHaveAnObject(TableNode $table)
    {
        $this->model = (object) new StdClass();

        $values = $this->tableToArray($table);

        foreach($values as $key => $val) {
            $this->model->$key = $val;
        }

        return $this->model;
    }


    /**
     * @And I store the object in the :tableName database table
     * @param $tableName
     */
    public function insertObject($tableName)
    {
        $this->model = (array) $this->model;

        DB::table($tableName)->insert($this->model);
    }

    /**
     * @Then I catch the API request sent
     */
    public function makeMockRequestToGuzzle()
    {
        $reflection = new ReflectionClass($this->_client);
        $property = $reflection->getProperty('_mockery_receivedMethodCalls');
        $property->setAccessible(true);
        $methodCalls = $property->getValue($this->_client);

        $methodCallsReflection = new ReflectionClass($methodCalls);
        $property = $methodCallsReflection->getProperty('methodCalls');
        $property->setAccessible(true);

        $methodCalls = $property->getValue($methodCalls);

        $calls = array();
        foreach ($methodCalls as $methodCall) {
            $reflection = new ReflectionClass($methodCall);
            $property = $reflection->getProperty('args');
            $property->setAccessible(true);
            $call = $property->getValue($methodCall);
            $calls[] = $call;
            $results = [];

            array_push($results, $call[0]);
            array_push($results, $call[1]['json'][0]);


            $this->mockResult = $results;

            $logging = new Logger('behat');
            $logging->pushHandler(new StreamHandler(storage_path('logs/behat.log')), Logger::INFO);
            $logging->info('Behat Tests:', $this->mockResult);
        }
    }

    /**
     * @Then the url visited should be :url
     */
    public function theMockResultDestination($url)
    {
        assertEquals($url, $this->mockResult[0]);
    }

    /**
     * @Then the request should contain:
     */
    public function theMockResultContains(TableNode $table)
    {
        $this->assertLogContains($table);
    }

    private function assertLogContains(TableNode $table)
    {

        $data = $this->tableToArray($table);

        $information = file_get_contents(storage_path('logs/behat.log'));

        foreach ($data as $key => $val) {
            assertContains($key, $information);
            assertContains($val, $information);
        }
        $this->destroyLog();
    }

    public function destroyLog()
    {
        $filesystem = new \Illuminate\Filesystem\Filesystem;
        $filesystem->delete(storage_path('logs/behat.log'));
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

        $this->response = json_encode($this->response);


        foreach ($this->payload as $key => $val) {

            Assert::assertContains($key, $this->response);
            Assert::assertContains($val, $this->response);
        }

        $this->response = json_encode($this->response);
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
        $this->request = $this->get($path, [
            'track_redirects' => true
        ]);

        $this->response = $this->request->content();


        return $this->response;
    }

    /**
     * @Given I request the body of api path :path
     * @param $path
     */
    public function requestAPIBody($path)
    {
        $this->request = $this->get($path);

        $bod = json_decode($this->request->getBody(), true);

        $this->response = json_encode($bod);
    }

    /**
     * @Given I request the headers of :path
     * @param $path
     */
    public function iRequestTheHeadersOf($path)
    {
        $this->request = $this->get($path);

        $headers = $this->request->headers;

        $this->response = (array)$headers;
    }


    /**
     * @When I expect the status code to be :code
     * @param $code
     */
    public function iRequestStatusCodeOf($code)
    {
        $statusCode = $this->request->getStatusCode();

        Assert::assertEquals($code, $statusCode);
    }

    /**
     * @Given I assert :table has a record where :column is :value
     * @param $table
     * @param $column
     * @param $value
     */
    public function dataStoredInTable($table, $column, $value)
    {
        $this->assertDatabaseHas($table, [$column => $value]);
    }

    /**
     * @Given I assert :table does not have a record where :column is :value
     * @param $table
     * @param $column
     * @param $value
     */
    public function dataNotStoredInTable($table, $column, $value)
    {
        $this->assertDatabaseMissing($table, [$column => $value]);
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
        $this->request = $this->post($path, $payload);

        $this->response = $this->request;

        return $this->response;
    }

    /**
     * @Given I send a delete request to :path
     * @param $path
     */
    public function deleteRequest($path)
    {
        $this->request = $this->delete($path);

        $code = $this->request->getStatusCode();
        $headers = $this->request->headers;

        $headers = (array)$headers;

        $response = array_add($headers, 'Status-Code', $code);

        $this->response = $response;
    }

    /**
     * @Given I send a patch request to :path
     * @param $path
     * @param TableNode $table
     */
    public function patchRequest($path, TableNode $table)
    {
        $this->payload = $this->tableToArray($table);
        $this->request = $this->patch($path, $this->payload)->header('Content-type', "application/json; charset=UTF-8");

        $this->response = json_encode($this->request);
    }

    /**
     * @Given I send a put request to :path
     * @param $path
     * @param TableNode $table
     */
    public function putRequest($path, TableNode $table)
    {
        $this->payload = $this->tableToArray($table);
        $this->request = $this->put($path, $this->payload);

        $this->response = json_encode($this->request);
    }
}
