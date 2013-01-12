<?php

/**
 * Test: Kdyby\Curl\CurlWrapper::post() with files.
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

file_put_contents($tempFile = TMP_DIR . '/curl-test.txt', 'ping');

$curl = new Curl\CurlWrapper('http://www.kdyby.org/curl-test/post.php', Curl\Request::POST);
$curl->setPost($post = array('hi' => 'hello'), array('txt' => $tempFile));

Tester\Assert::true($curl->execute());
Tester\Assert::match(dumpVar($post) . dumpVar(array('txt' => array(
	'name' => basename($tempFile),
	'type' => '%a%',
	'tmp_name' => '%a%',
	'error' => '0',
	'size' => filesize($tempFile),
))), $curl->response);
