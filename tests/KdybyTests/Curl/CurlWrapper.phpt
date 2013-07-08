<?php

/**
 * Test: Kdyby\Curl\CurlWrapper parse cookies.
 *
 * @testCase KdybyTests\Curl\CurlWrapperTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Curl
 */

namespace KdybyTests\Curl;

use Kdyby\Curl;
use Tester;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class CurlWrapperTest extends Tester\TestCase
{

	public function testGet()
	{
		$httpServer = new \HttpServer(__DIR__ . '/routers/get.php');
		$url = $httpServer->start();

		$curl = new Curl\CurlWrapper($url . '/?var=foo&foo[]=lol');

		Tester\Assert::true($curl->execute());
		Tester\Assert::equal(print_r(array('var' => 'foo', 'foo' => array('lol')), TRUE), $curl->response);
	}



	public function testPost()
	{
		$httpServer = new \HttpServer(__DIR__ . '/routers/post.php');
		$url = $httpServer->start();

		$curl = new Curl\CurlWrapper($url, Curl\Request::POST);
		$curl->setPost($post = array('hi' => 'hello'));

		Tester\Assert::true($curl->execute());
		Tester\Assert::equal(print_r($post, TRUE) . print_r(array(), TRUE), $curl->response);
	}



	public function testPostFiles()
	{
		$httpServer = new \HttpServer(__DIR__ . '/routers/post.php');
		$url = $httpServer->start();

		file_put_contents($tempFile = TEMP_DIR . '/curl-test.txt', 'ping');

		$curl = new Curl\CurlWrapper($url, Curl\Request::POST);
		$curl->setPost($post = array('hi' => 'hello'), array('txt' => $tempFile));

		Tester\Assert::true($curl->execute());
		Tester\Assert::match(print_r($post, TRUE) . print_r(array('txt' => array(
			'name' => basename($tempFile),
			'type' => '%a%',
			'tmp_name' => '%a%',
			'error' => '0',
			'size' => filesize($tempFile),
		)), TRUE), $curl->response);
	}



	public function testGet_Cookies()
	{
		$httpServer = new \HttpServer(__DIR__ . '/routers/cookies.php');
		$url = $httpServer->start();

		$curl = new Curl\CurlWrapper($url);
		$curl->setOption('header', TRUE);
		Tester\Assert::true($curl->execute());

		$headers = Curl\Response::stripHeaders($curl);
		Tester\Assert::equal(Curl\HttpCookies::from(array(
			'kdyby' => 'is awesome',
			'nette' => 'is awesome',
			'array' => array(
				'one' => 'Lister',
				'two' => 'Rimmer'
			),
		), FALSE), $headers['Set-Cookie']);
	}



	public function testParseHeaders_AmbiguousStatus()
	{
		$headers = <<<HEAD
HTTP/1.1 100 Continue

HTTP/1.1 100 Continue

HTTP/1.1 500 Internal Server Error
Date: Thu, 07 Mar 2013 08:31:35 GMT
Server: Apache
X-Rack-Cache: invalidate, pass
Content-Length: 1440
Status: 500
Vary: Accept-Encoding
Content-Type: text/html; charset=utf-8
Connection: close
HEAD;

		Tester\Assert::same(array(
		   "Http-Version" => "1.1",
		   "Status-Code" => "500",
		   'Status' => "500 Internal Server Error",
		   'Date' => "Thu, 07 Mar 2013 08:31:35 GMT",
		   'Server' => "Apache",
		   "X-Rack-Cache" => "invalidate, pass",
		   "Content-Length" => "1440",
		   'Vary' => "Accept-Encoding",
		   "Content-Type" => "text/html; charset=utf-8",
		   'Connection' => "close",
		), Curl\CurlWrapper::parseHeaders($headers));
	}

}

\run(new CurlWrapperTest());
