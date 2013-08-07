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



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class MultiSender extends CurlSender implements \ArrayAccess, \Iterator
{

	/**
	 * @var resource
	 */
	private $multiHandle;

	/**
	 * @var int
	 */
	private $running;

	/**
	 * @var int
	 */
	private $execStatus;

	/**
	 * @var array|Request[]
	 */
	private $requests = array();

	/**
	 * @var array|CurlWrapper[]
	 */
	private $handles = array();

	/**
	 * @var array|resource[]
	 */
	private $handleResource = array();

	/**
	 * @var array|Response[]
	 */
	private $responses = array();

	/**
	 * @var int
	 */
	private $finished = array();

	/**
	 * @var int
	 */
	private $counter = 0;



	public function __destruct()
	{
		$this->end();
	}



	public function startRequest(Request $request)
	{
		if (isset($this->requests[$id = spl_object_hash($request)])) {
			throw new InvalidStateException("Request is already being processed");
		}

		if ($this->multiHandle === NULL) {
			$this->multiHandle = curl_multi_init();
		}

		$wrapper = $this->initRequest($request);
		$handle = $wrapper->init();
		$code = curl_multi_add_handle($this->multiHandle, $handle);

		if ($code !== CURLM_OK && $code !== CURLM_CALL_MULTI_PERFORM) {
			throw new FailedRequestException($wrapper, $request);
		}

		$code = curl_multi_exec($this->multiHandle, $this->running);

		if ($code !== CURLM_OK && $code !== CURLM_CALL_MULTI_PERFORM) {
			throw new FailedRequestException($wrapper, $request);
		}

		$this->requests[$id] = $request;
		$this->handles[$id] = $wrapper;
		$this->handleResource[$id] = $handle;

		return $this;
	}



	/**
	 * @param Request $request
	 * @return Response
	 * @throws InvalidStateException
	 */
	public function getResponse(Request $request)
	{
		if (isset($this->responses[$id = spl_object_hash($request)])) {
			return $this->responses[$id];
		}

		if (!isset($this->requests[$id])) {
			$this->startRequest($request);
		}

		foreach ($this as $currentId => $response) {
			if ($currentId !== $id) {
				continue;
			}

			return $response;
		}

		throw new InvalidStateException;
	}



	public function next()
	{
		if (!is_resource($this->multiHandle)) {
			return;
		}

		if (curl_multi_select($this->multiHandle) < 0) {
			return $this->end();
		}

		do {
			$this->execStatus = curl_multi_exec($this->multiHandle, $this->running);
			if ($done = curl_multi_info_read($this->multiHandle)) {
				$this->closeHandle($done['handle']);
				return $this->current();

			} elseif ($done === FALSE && $this->execStatus === CURLM_OK) {
				$this->end();
			}

			usleep(1000); // 1 ms

		} while (($this->execStatus == CURLM_OK || $this->execStatus == CURLM_CALL_MULTI_PERFORM) || $this->running);

		if (function_exists('curl_multi_strerror')) {
			throw new CurlException(curl_multi_strerror($this->execStatus));
		}

		throw new CurlException("Execution failed #" . $this->execStatus);
	}



	/**
	 * @param resource $handle
	 * @throws InvalidArgumentException
	 * @return string
	 */
	private function closeHandle($handle)
	{
		foreach ($this->handleResource as $currentId => $runningHandle) {
			if ((string) $runningHandle === (string) $handle) {
				break;
			}
		}

		if (empty($currentId)) {
			throw new InvalidArgumentException("Unknown handle '$handle'.");
		}

		$result = curl_multi_getcontent($handle);
		curl_multi_remove_handle($this->multiHandle, $this->handleResource[$currentId]);

		$this->handles[$currentId]->finish($result);
		unset($this->handleResource[$currentId]);

		$this->responses[$currentId] = $this->finishRequest($this->requests[$currentId], $this->handles[$currentId], 0);
		$this->finished[] = $currentId;
	}



	/**
	 * @return Response|NULL
	 */
	public function current()
	{
		return !empty($this->responses[$id = end($this->finished)]) ? $this->responses[$id] : NULL;
	}



	public function key()
	{
		return end($this->finished);
	}



	public function valid()
	{
		return isset($this->responses[end($this->finished)]);
	}



	public function rewind()
	{
		if (!$this->finished) {
			$this->next();
		}
	}



	protected function end()
	{
		if (!is_resource($this->multiHandle)) {
			return NULL;
		}

		foreach ($this->handleResource as $handle) {
			$this->closeHandle($handle);
		}

		curl_multi_close($this->multiHandle);
		$this->multiHandle = NULL;
		$this->handleResource = array();
		$this->finished = NULL;

		return NULL;
	}



	public function offsetExists($offset)
	{
		if (!$offset instanceof Request) {
			throw InvalidArgumentException::expected('instance of Request', $offset);
		}

		return isset($this->requests[spl_object_hash($offset)]);
	}



	public function offsetGet($offset)
	{
		if (!$offset instanceof Request) {
			throw InvalidArgumentException::expected('instance of Request', $offset);
		}

		return $this->getResponse($offset);
	}



	public function offsetSet($offset, $value)
	{
		if ($offset !== NULL) {
			throw new InvalidArgumentException('Cannot set specific index, only append is supported. Just simply call $sender[] = new Request;');
		}

		if (!$value instanceof Request) {
			throw InvalidArgumentException::expected('instance of Request', $value);
		}

		$this->startRequest($value);
	}



	/**
	 * @param mixed $offset
	 * @throws NotSupportedException
	 */
	public function offsetUnset($offset)
	{
		throw new NotSupportedException;
	}

}
