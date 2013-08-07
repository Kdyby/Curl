<?php

/**
 * Test: Kdyby\Curl\MultiSender.
 *
 * @testCase KdybyTests\Curl\MultiSenderTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Curl
 */

namespace KdybyTests\Curl;

use Kdyby;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class MultiSenderTest extends Tester\TestCase
{

	/**
	 * @var Kdyby\Curl\MultiSender
	 */
	private $sender;

	/**
	 * @var \HttpServer
	 */
	private $httpServer;



	protected function setUp()
	{
		$this->httpServer = new \HttpServer();

		$this->sender = new Kdyby\Curl\MultiSender();
		$this->sender->setConnectTimeout(2);
		$this->sender->setTimeout(5);

		Nette\Diagnostics\Debugger::$productionMode = FALSE;
	}



	public function testFunctional()
	{
		$serverUrl = $this->httpServer->start(__DIR__ . '/routers/all.php');

		$time = time();

		$req1 = new Kdyby\Curl\Request($serverUrl);
		$req1->getUrl()->appendQuery(array('delay' => 10));
		$this->sender->startRequest($req1);

		$req2 = new Kdyby\Curl\Request($serverUrl);
		$req2->getUrl()->appendQuery(array('delay' => 3));
		$this->sender->startRequest($req2);

		$req3 = new Kdyby\Curl\Request($serverUrl);
		$req3->getUrl()->appendQuery(array('delay' => 6));
		$this->sender->startRequest($req3);

		$responses = array();
		foreach ($this->sender as $response) {
			$responses[] = $response;
		}
		/** @var Kdyby\Curl\Response[] $responses */

		Assert::same($responses[0], $this->sender->getResponse($req2));
		Assert::same($responses[1], $this->sender->getResponse($req3));
		Assert::same($responses[2], $this->sender->getResponse($req1));

		Assert::true(time() - $time < 19); // it must took less than sum of all delays
	}

}

\run(new MultiSenderTest());
