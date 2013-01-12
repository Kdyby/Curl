<?php

/**
 * Test: Kdyby\Curl\HttpCookies::read().
 *
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Curl
 */

use Kdyby\Curl\HttpCookies;

require_once __DIR__ . '/../bootstrap.php';

$yesterday = date_create()->modify('-1 day')->format(HttpCookies::COOKIE_DATETIME);
$tomorrow = date_create()->modify('+1 day')->format(HttpCookies::COOKIE_DATETIME);

$cookies = new HttpCookies(array(
	'kdyby=is+awesome; expires=' . $tomorrow,
	'nette=is+awesome; expires=' . $tomorrow,
	'array[one]=Lister; expires=' . $tomorrow . '; path=/; secure',
	'array[two]=Rimmer; expires=' . $tomorrow . '; path=/; secure; httponly',
	'symfony=is+ok; expires=' . $yesterday,
));

Tester\Assert::equal(HttpCookies::from(array(
	'kdyby' => 'is awesome',
	'nette' => 'is awesome',
	'array' => array('one' => 'Lister', 'two' => 'Rimmer'),
), FALSE), $cookies);
