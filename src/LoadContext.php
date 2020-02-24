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
 * Class LoadContext
 * @package Entanet\Behat
 */
class LoadContext extends TestCase implements Context
{

    use CreatesApplication;
    use MakesHttpRequests;

    /**
     * @var $path
     */
    public $path;
    /**
     * @var $requestCount
     */
    public $requestCount;
    /**
     * @var $result
     */
    public $result;
    /**
     * @var $statusCode
     */
    public $statusCode;
    /**
     * @var $payload
     */
    public $payload;
    /**
     * @var $url
     */
    public $url;
    /**
     * @var $exception
     */
    public $exception;
    /**
     * @var $returnedCode
     */
    public $returnedCode;
    /**
     * @var $request
     */
    public $request;
    /**
     * @var $request
     */
    public $expectedException;



    /**
     * LoadContext constructor.
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
     * @param $path
     * @return mixed
     * @Given I set the request path to :path
     */
    public function setPath($path)
    {
        $this->url = $path;

        return $this->url;
    }

    /**
     * @param $code
     * @return mixed
     * @Given I set the expected status code to :code
     */
    public function setExpectedStatusCode($code)
    {
        $this->statusCode = $code;

        return $this->statusCode;
    }

    /**
     * @param $exceptionMessage
     * @return mixed
     * @Given I set the expected exception message to :exceptionMessage
     */
    public function setExpectedException($exceptionMessage)
    {
        $this->expectedException = $exceptionMessage;

        return $this->expectedException;
    }

    /**
     * @param $table
     * @return mixed
     * @Given I have a payload of:
     */
    public function setPayload(TableNode $table)
    {
        $this->payload = $this->tableToArray($table);

        return $this->payload;
    }

    /**
     * @param $count
     * @return mixed
     * @Given I set the request count to :count
     */
    public function setRequestCount($count)
    {
        $this->requestCount = $count;

        return $this->requestCount;
    }

    /**
     *@When I make the looped GET request
     */
    public function getRequest()
    {
        for ($i = 0; $i < $this->requestCount; $i++) {
            $this->result = $this->get($this->url);
        }
    }

    /**
     *@When I make the looped POST request
     */
    public function postRequest()
    {
        for ($i = 0; $i < $this->requestCount; $i++) {
            $this->result = $this->post($this->url, $this->payload);
        }
    }

    /**
     * @return array
     * @When I catch the exception
     */
    public function getException()
    {
        $responses = $this->result;
        foreach ($responses as $result) {
            $this->exception = $result->exception;

            $this->exception = (array)$this->exception;

            return $this->exception;
        }
    }


    /**
     * @return mixed
     * @When I get the returned status code
     */
    public function getStatusCodes()
    {
        $responses = $this->result;
        foreach ($responses as $result) {
            $this->returnedCode = $result->getStatusCode();


            return $this->returnedCode;
        }
    }

    /**
     * @return mixed
     * @When I catch the exception message
     */
    public function findExceptionMessage()
    {
        $responses = $this->result;
        foreach ($responses as $result) {
            $this->exception = $result->exception->getMessage();

            return $this->exception;
        }
    }

    /**
     * @return mixed
     * @When I catch the exception message and status code
     */
    public function findExceptionMessageAndCode()
    {
        $responses = $this->result;
        foreach ($responses as $result) {

            $this->exception = [];
            $this->exception['message'] = $result->exception->getMessage();
            $this->exception['statusCode'] = $result->getStatusCode();

            return $this->exception;
        }
    }

    /**
     *@Then I expect the status code matches the expected
     */
    public function assertStatusCodeReturnedMeetsExpected()
    {
        $this->assertEquals($this->statusCode, $this->returnedCode);
    }

    /**
     *@Then I expect the exception matches the expected
     */
    public function assertCorrectException()
    {
        $this->assertEquals($this->exception, $this->expectedException);
    }


    /**
     *@Then I expect the exception to contain the following:
     */
    public function assertExceptionMessageAndStatusCode(TableNode $table)
    {
        $table = $this->tableToArray($table);

        foreach($table as $key => $val) {
            $key = (string) $key;
            $val = (string) $val;
            $this->assertContains($key, $this->exception);
            $this->assertContains($val, $this->exception);
        }
    }


    public function tableToArray(TableNode $table)
    {
        $keys = $table->getRow(0);

        $values = $table->getRow(1);

        $array = array_combine($keys, $values);

        return $array;
    }

}