<?php

/**
 * Test: Kdyby\Curl\CurlSender.
 *
 * @phpversion 5.4 due to usage of php build-in webserver
 * @testCase KdybyTests\Curl\CurlSenderTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Curl
 */

namespace KdybyTests\Curl;

use Kdyby;
use Kdyby\Curl\Request;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class CurlSenderTest extends Tester\TestCase
{

	/**
	 * @var Kdyby\Curl\CurlSender
	 */
	private $sender;



	protected function setUp()
	{
		$this->sender = new Kdyby\Curl\CurlSender();
		$this->sender->setConnectTimeout(2);
		$this->sender->setTimeout(5);
	}



	public function testRequest_Get_noQuery()
	{
		$httpServer = new \HttpServer(__DIR__ . '/routers/get.php');
		$url = $httpServer->start();

		$response = $this->sender->send(new Request($url));
		Assert::same("Array\n(\n)\n", $response->getResponse());
	}



	public function testRequest_Get_Query()
	{
		$httpServer = new \HttpServer(__DIR__ . '/routers/get.php');
		$url = $httpServer->start();

		$response = $this->sender->send(new Request($url . '?kdyby=awesome&nette[]=best'));
		Assert::same("Array\n(
    [kdyby] => awesome
    [nette] => Array\n        (\n            [0] => best\n        )\n\n)
", $response->getResponse());
	}

}

\run(new CurlSenderTest());
