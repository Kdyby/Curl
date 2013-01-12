<?php

/**
 * Test: Kdyby\Curl\CurlWrapper parse cookies.
 *
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Curl
 */

use Kdyby\Curl;

require_once __DIR__ . '/../bootstrap.php';

if ('pong' !== @file_get_contents('http://www.kdyby.org/ping')) {
	Tester\Helpers::skip("No internet connection");
}


$curl = new Curl\CurlWrapper('http://www.kdyby.org/curl-test/cookies.php');
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
