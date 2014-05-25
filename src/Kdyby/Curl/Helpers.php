<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.txt that was distributed with this source code.
 */

namespace Kdyby\Curl;

use Kdyby;
use Nette;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class Helpers extends Nette\Object
{


	/**
	 * @param array|\Traversable $array
	 * @param callable $callback
	 * @return array
	 */
	public static function flatMapAssoc($array, $callback)
	{
		$callback = callback($callback);
		$result = array();
		$walker = function ($array, $keys = array()) use (&$walker, &$result, $callback) {
			foreach ($array as $key => $value) {
				$currentKeys = $keys + array(count($keys) => $key);
				if (is_array($value)) {
					$walker($value, $currentKeys);
					continue;
				}
				$result[] = $callback($value, $currentKeys);
			}

			return $result;
		};

		return $walker($array);
	}



	public static function flattenArray($array, $prefix = NULL)
	{
		$res = array();

		foreach ($array as $key => $value) {
			$k = (isset($prefix)) ? $prefix . "[$key]" : "$key";

			if (is_array($value)) {
				$res += self::flattenArray($value, $k);
			} else {
				$res[$k] = $value;
			}
		}

		return $res;
	}

}
