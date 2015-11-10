<?php

/**
 * Test: Kdyby\Curl\HttpCookies::compile().
 *
 * @testCase KdybyTests\Curl\HttpCookiesTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Curl
 */

namespace KdybyTests\Curl;

use Kdyby\Curl\HttpCookies;
use Tester;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class HttpCookiesTest extends Tester\TestCase
{

	/**
	 * @return array
	 */
	public function dataCookies()
	{
		$yesterday = date_create()->modify('-1 day')->format(HttpCookies::COOKIE_DATETIME);
		$tomorrow = date_create()->modify('+1 day')->format(HttpCookies::COOKIE_DATETIME);

		return array(
			'kdyby=is+awesome; expires=' . $tomorrow,
			'nette=is+awesome; expires=' . $tomorrow,
			'array[one]=Lister; expires=' . $tomorrow . '; path=/; secure',
			'array[two]=Rimmer; expires=' . $tomorrow . '; path=/; secure; httponly',
			'symfony=is+ok; expires=' . $yesterday,
		);
	}


	public function testRead()
	{
		$cookies = new HttpCookies($this->dataCookies());
		Tester\Assert::equal(HttpCookies::from(array(
			'kdyby' => 'is awesome',
			'nette' => 'is awesome',
			'array' => array(
				'one' => 'Lister',
				'two' => 'Rimmer'
			),
		), FALSE), $cookies);
	}



	public function testCompile()
	{
		$cookies = new HttpCookies($this->dataCookies());

		$expected = 'kdyby=is+awesome; nette=is+awesome; array[one]=Lister; array[two]=Rimmer';
		Tester\Assert::equal($expected, $cookies->compile());
		Tester\Assert::equal($cookies->compile(), (string)$cookies);
	}

}

\run(new HttpCookiesTest());
