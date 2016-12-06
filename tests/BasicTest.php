<?php
namespace GuzzleHttp\Tests;
require_once '../vendor/autoload.php';

use Blackfire\Bridge\PhpUnit\TestCaseTrait;
use Blackfire\Profile\Configuration;
use Blackfire\Profile\Metric;
use GuzzleHttp\Client;

/**
 * Class BasicTest
 * @package GuzzleHttp\Tests
 */
class BasicTest extends \PHPUnit_Framework_TestCase
{
    use TestCaseTrait;

    /**
     * @test
     */
    public function doesNotCallClient()
    {
        $config = new Configuration();
        $config->assert('metrics.http.curl.requests.count == 0', 'No http calls');

        $this->assertBlackfire($config, function () {
            (new testedClass())->foo(10);
        });
    }

    /**
     * @test
     */
    public function callsCorrectEndpoint()
    {
        $config = new Configuration();
        $config->defineMetric(new Metric('client.call', '=GuzzleHttp\Client::request'));
        $config->assert('metrics.client.call.count == 1', 'No http calls');

        $this->assertBlackfire($config, function () {
            (new testedClass())->foo(3);
        });
    }

    /**
     * @test
     * Przykład uzycia blackfire do stawiania oczekiwań ( spy ) na metodach ( z uwzględnieniem argumentów )
     */
    public function callStatic()
    {
        // oczeekujemy że metodda Foo::var zostanie wywołana raz z argumentem baz ( można tylko po jednym argumencie na raz )
        $config = new Configuration();
        $metric = new Metric('static.call');
        $calle = $metric->addCallee('=GuzzleHttp\Tests\Foo::bar');
        $calle->selectArgument(1, '^baz');
        // można sledzić dowolną ilość argumentów
        $calle2 = $metric->addCallee('=GuzzleHttp\Tests\Foo::bar');
        $calle2->selectArgument(2, '^lala');
        $config->defineMetric($metric);

        $config->assert('metrics.static.call.count == 1', 'static calls');

        $this->assertBlackfire($config, function () {
            (new testedClass())->foo(2);
        });
    }

    //@todo a jak mockować
}

class testedClass{

    public function foo($arg){

        // lots of legacy code

        if($arg < 5){
            $client = new Client();
            $client->request('get', 'http://getresponse.com');
        }else{
            Foo::bar('baz', 'lala');
        }

        // more legacy code
    }
}

class Foo{
    static public function bar($arg1, $arg2){

    }
}