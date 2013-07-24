<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Curl;



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
interface Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class InvalidArgumentException extends \InvalidArgumentException implements Exception
{

	/**
	 * @param string $expected
	 * @param mixed $given
	 * @return InvalidArgumentException
	 */
	public static function expected($expected, $given)
	{
		return new static(sprintf('Expected %s, but %s given.', $expected, self::analyzeType($given)));
	}



	/**
	 * @param string $expected
	 * @param array $values
	 * @param mixed $given
	 * @return InvalidArgumentException
	 */
	public static function expectedOneOf($expected, array $values, $given)
	{
		return new static(sprintf('Expected %s to be one of %s, but %s given.', $expected, implode(', ', $values), self::analyzeType($given)));
	}



	/**
	 * @param string $expected
	 * @param int $num
	 * @return InvalidArgumentException
	 */
	public static function expectedArgument($expected, $num)
	{
		$trace = PHP_VERSION_ID >= 50400 ? debug_backtrace(NULL, 2) : debug_backtrace(NULL);
		$args = $trace[1]['args'];

		if (!array_key_exists($num - 1, $args)) {
			return new static(sprintf('Expected %s as argument #%d, but nothing given.', $expected, $num - 1));
		}

		return new static(sprintf('Expected %s as argument #%d, but %s given.', $expected, $num, self::analyzeType($args[$num - 1])));
	}



	private static function analyzeType($variable)
	{
		if (is_object($variable) || is_array($variable)) {
			return is_object($variable) ? 'instance of ' . get_class($variable) : 'array(' . count($variable) . ')';

		} elseif (is_scalar($variable)) {
			return gettype($variable) . '(' . (($l = strlen($variable)) < 10 ? $variable : $l) . ')';
		}

		return gettype($variable);
	}

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class InvalidStateException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class InvalidUrlException extends \InvalidArgumentException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class MissingCertificateException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class FileNotWritableException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class DirectoryNotWritableException extends \RuntimeException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class NotSupportedException extends \LogicException implements Exception
{

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class CurlException extends \RuntimeException implements Exception
{

	/**
	 * @var \Kdyby\Curl\Request
	 */
	private $request;

	/**
	 * @var \Kdyby\Curl\Response
	 */
	private $response;



	/**
	 * @param string $message
	 * @param \Kdyby\Curl\Request $request
	 * @param \Kdyby\Curl\Response $response
	 */
	public function __construct($message, Request $request = NULL, Response $response = NULL)
	{
		parent::__construct($message, 0);
		$this->request = $request;
		if ($this->response = $response) {
			$this->code = $response->headers['Status-Code'];
		}
	}



	/**
	 * @return \Kdyby\Curl\Request
	 */
	public function getRequest()
	{
		return $this->request;
	}



	/**
	 * @return \Kdyby\Curl\Response
	 */
	public function getResponse()
	{
		return $this->response;
	}

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class FailedRequestException extends CurlException
{

	/**
	 * @var mixed
	 */
	private $info;



	/**
	 * @param \Kdyby\Curl\CurlWrapper $curl
	 * @param Request $request
	 */
	public function __construct(CurlWrapper $curl, Request $request = NULL)
	{
		parent::__construct($curl->error, $request);
		$this->code = $curl->errorNumber;
		$this->info = $curl->info;
	}



	/**
	 * @see curl_getinfo()
	 * @return mixed
	 */
	public function getInfo()
	{
		return $this->info;
	}

}



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class BadStatusException extends CurlException
{

}
