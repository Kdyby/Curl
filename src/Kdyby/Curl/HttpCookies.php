<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Curl;

use Kdyby;
use Nette;
use Nette\Utils\Arrays;
use Nette\Utils\Strings;
use Nette\Utils\ArrayHash;



if (!class_exists('Nette\Utils\ArrayHash')) {
	class_alias('Nette\ArrayHash', 'Nette\Utils\ArrayHash');
}

/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class HttpCookies extends ArrayHash
{
	const COOKIE_DATETIME = 'D, d-M-Y H:i:s e';



	/**
	 * @param array|string $setCookies
	 */
	public function __construct($setCookies = NULL, $encodeCallback = 'urlencode', $decodeCallback = 'urldecode')
	{
		$this->encodeCallback = $encodeCallback;
		$this->decodeCallback = $decodeCallback;
		if (Nette\Utils\Validators::isList($setCookies) || is_scalar($setCookies)) {
			$this->parse(is_array($setCookies) ? $setCookies : (array)$setCookies);

		} else {
			foreach ((array)$setCookies as $name => $value) {
				$this->$name = $value;
			}
		}
	}



	public function getIterator() {
		return new \CallbackFilterIterator(parent::getIterator(), function($value, $key) {
				return !in_array($key, array('encodeCallback', 'decodeCallback'));
			});
	}

	/**
	 * @return string
	 */
	public function compile()
	{
		$cookies = Helpers::flatMapAssoc($this, function ($value, $keys) {
			$name = implode('][', $this->encodeCallback ? array_map($this->encodeCallback, $keys) : $keys);
			$name = count($keys) > 1 ? (substr_replace($name, '', strpos($name, ']'), 1) . ']') : $name;
			return $name . '=' . ($this->encodeCallback ? call_user_func($this->encodeCallback, $value) : $value);
		});
		return implode('; ', $cookies);
	}



	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->compile();
	}



	/**
	 * @param array $cookies
	 */
	private function parse(array $cookies)
	{
		foreach ($cookies as $raw) {
			if (!$cookie = static::readCookie($raw, $this->decodeCallback)) {
				continue;
			}

			if (isset($cookie['expires']) && \DateTime::createFromFormat(static::COOKIE_DATETIME, $cookie['expires']) < date_create()) {
				continue; // cookie already expired
			}

			if (strpos($name = $cookie['name'], '[') === FALSE) {
				$this->$name = $cookie['value'];

			} else {
				$keys = explode('[', str_replace(']', '', $name));
				$arr =& $this->{array_shift($keys)};
				if(is_null($arr)) {
					$arr = array();
				}
				$cookieValue =& Arrays::getRef($arr, $keys);
				$cookieValue = $cookie['value'];
				unset($cookieValue);
			}
		}
	}



	/**
	 * Expands cookie header "Set-Cookie"
	 *   user_time=1327581075; expires=Sat, 25-Feb-2012 12:31:15 GMT; path=/
	 * to array
	 *
	 * @param string $cookie
	 *
	 * @return array|NULL
	 */
	public static function readCookie($cookie, $decodeCallback = 'urldecode')
	{
		if (!$m = Strings::matchAll($cookie, '~(?P<name>[^;=\s]+)(?:=(?P<value>[^;]*))?~i')) {
			return NULL;
		}

		$first = array_shift($m);
		$cookie = array(
			'name' => $decodeCallback ? call_user_func($decodeCallback, $first['name']) : $first['name'],
			'value' => $decodeCallback ? call_user_func($decodeCallback, $first['value']) : $first['value'],
		);

		foreach ($m as $found) {
			$cookie[$found['name']] = !empty($found['value']) ? $found['value'] : TRUE;
		}

		return $cookie;
	}

}
