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

	/**
	 * @var \HttpServer
	 */
	private $httpServer;



	protected function setUp()
	{
		$this->httpServer = new \HttpServer();

		$this->sender = new Kdyby\Curl\CurlSender();
		$this->sender->setConnectTimeout(2);
		$this->sender->setTimeout(5);
	}



	protected function tearDown()
	{
		$this->httpServer->slaughter();
	}



	public function testRequest_Get_noQuery()
	{
		$url = $this->httpServer->start(__DIR__ . '/routers/all.php');

		$response = $this->sender->send(new Request($url));
		Assert::same("GET\n", $response->getResponse());
	}



	public function testRequest_Get_Query()
	{
		$url = $this->httpServer->start(__DIR__ . '/routers/all.php');

		$response = $this->sender->send(new Request($url . '/?kdyby=awesome&nette[]=best'));
		Assert::same("GET\nArray\n(
    [kdyby] => awesome
    [nette] => Array\n        (\n            [0] => best\n        )\n\n)
", $response->getResponse());
	}



	public function testPost()
	{
		$url = $this->httpServer->start(__DIR__ . '/routers/all.php');

		$request = new Request($url);
		$response = $request->setSender($this->sender)->post(array('hi' => 'hello', 'foo' => array('bar' => 'baz')));

		Tester\Assert::equal("POST\n" . print_r($request->post, TRUE), $response->getResponse());
	}



	public function testPostFiles()
	{
		$url = $this->httpServer->start(__DIR__ . '/routers/all.php');

		file_put_contents($tempFile = TEMP_DIR . '/curl-test.txt', 'ping');

		$request = new Request($url);
		$response = $request->setSender($this->sender)->post(array('hi' => 'hello'), array('txt' => $tempFile));

		Tester\Assert::match("POST\n" . print_r($request->post, TRUE) . print_r(array('txt' => array(
			'name' => basename($tempFile),
			'type' => '%a%',
			'tmp_name' => '%a%',
			'error' => '0',
			'size' => filesize($tempFile),
		)), TRUE), $response->getResponse());
	}



	public function testGet_Cookies()
	{
		$url = $this->httpServer->start(__DIR__ . '/routers/cookies.php');

		$response = $this->sender->send(new Request($url));

		Tester\Assert::equal(array(
			'kdyby' => 'is awesome',
			'nette' => 'is awesome',
			'array' => array(
				'one' => 'Lister',
				'two' => 'Rimmer'
			),
		), $response->cookies);
	}



	public function testDownload()
	{
		$sender = clone $this->sender;

		$url = $this->httpServer->start(__DIR__ . '/routers/download.php');
		$sender->setDownloadDir(TEMP_DIR);

		$request = new Request($url);
		$response = $request->setSender($sender)->download();

		// file was downloaded
		Assert::true($response instanceof Kdyby\Curl\FileResponse);
		Assert::true(file_exists($response->getTemporaryFile()));

		// was moved
		$response->move(TEMP_DIR . '/downloaded-conventions.txt');
		Assert::same(TEMP_DIR . '/downloaded-conventions.txt', $response->getTemporaryFile());
		Assert::true(file_exists(TEMP_DIR . '/downloaded-conventions.txt'));

		// headers were separated
		Assert::same(file_get_contents(__DIR__ . '/../../conventions.txt'), $response->getContents());
	}

}

\run(new CurlSenderTest());
