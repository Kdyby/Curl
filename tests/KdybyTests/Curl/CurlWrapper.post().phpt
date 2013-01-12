<?php

/**
 * Test: Kdyby\Curl\CurlWrapper::post().
 *
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Curl
 */

use Kdyby\Curl;

require_once __DIR__ . '/../bootstrap.php';

if ('pong' !== @file_get_contents('http://www.kdyby.org/ping')) {
	Tester\Helpers::skip("No internet connection");
}


function dumpVar($variable)
{
	ob_start();
	print_r($variable);

	return ob_get_clean();
}



$curl = new Curl\CurlWrapper('http://www.kdyby.org/curl-test/post.php', Curl\Request::POST);
$curl->setPost($post = array('hi' => 'hello'));

Tester\Assert::true($curl->execute());
Tester\Assert::equal(dumpVar($post) . dumpVar(array()), $curl->response);
