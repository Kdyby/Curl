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
use Nette\Http\IRequest;
use Nette\Http\UrlScript as Url;
use Nette\Utils\ObjectMixin;



/**
 * @author Filip Procházka <filip@prochazka.su>
 *
 * @method \Kdyby\Curl\Request setUrl(string $url)
 * @method \Kdyby\Curl\Request setMethod(string $method)
 */
class Request extends RequestOptions
{
	/**#@+ HTTP Request method */
	const GET = IRequest::GET;
	const POST = IRequest::POST;
	const PUT = IRequest::PUT;
	const HEAD = IRequest::HEAD;
	const DELETE = IRequest::DELETE;
	const PATCH = 'PATCH';
	const DOWNLOAD = 'DOWNLOAD';
	/**#@- */

	/** @var \Nette\Http\UrlScript */
	public $url;

	/** @var string */
	public $method = self::GET;

	/** @var array */
	public $headers = array();

	/** @var array name => value */
	public $cookies = array();

	public $cookiesEncodeCallback = 'urlencode';
	public $cookiesDecodeCallback = 'urldecode';

	/** @var array|string */
	public $post = array();

	/** @var array */
	public $files = array();

	/** @var CurlSender */
	private $sender;



	/**
	 * @param string $url
	 * @param array|string $post
	 */
	public function __construct($url, $post = array())
	{
		$this->setUrl($url);
		$this->post = $post;
	}



	/**
	 * @return \Nette\Http\UrlScript
	 */
	public function getUrl()
	{
		if (!$this->url instanceof Url) {
			$this->url = new Url($this->url);
		}
		return $this->url;
	}



	/**
	 * @return HttpCookies
	 */
	public function getCookies()
	{
		return new HttpCookies($this->cookies, $this->cookiesEncodeCallback, $this->cookiesDecodeCallback);
	}



	/**
	 * @param string $method
	 * @return boolean
	 */
	public function isMethod($method)
	{
		return $this->method === $method;
	}



	/**
	 * @param CurlSender $sender
	 *
	 * @return Request
	 */
	public function setSender(CurlSender $sender)
	{
		$this->sender = $sender;
		return $this;
	}



	/**
	 * @param array|string $post
	 * @param array $files
	 * @return Request
	 */
	public function setPost($post, $files = array())
	{
		$this->post = $post;
		$this->files = $files;
		$this->method = self::POST;

		return $this;
	}



	/**
	 * @throws CurlException
	 * @return Response
	 */
	public function send()
	{
		if ($this->sender === NULL) {
			$this->sender = new CurlSender();
		}

		return $this->sender->send($this);
	}



	/**
	 * @param array|string $query
	 *
	 * @throws CurlException
	 * @return Response
	 */
	public function get($query = NULL)
	{
		$this->method = static::GET;
		$this->post = $this->files = array();
		$this->getUrl()->appendQuery($query);
		return $this->send();
	}



	/**
	 * @param array|string $post
	 * @param array $files
	 *
	 * @throws CurlException
	 * @return Response
	 */
	public function post($post = array(), array $files = NULL)
	{
		$this->method = static::POST;
		$this->post = $post;
		$this->files = (array)$files;
		return $this->send();
	}



	/**
	 * @param array|string $post
	 *
	 * @throws CurlException
	 * @return Response
	 */
	public function put($post = array())
	{
		$this->method = static::PUT;
		$this->post = $post;
		$this->files = array();
		return $this->send();
	}



	/**
	 * @throws CurlException
	 * @return Response
	 */
	public function delete()
	{
		$this->method = static::DELETE;
		$this->post = $this->files = array();
		return $this->send();
	}



	/**
	 * @param array $post
	 * @return Response
	 */
	public function patch($post = array())
	{
		$this->method = static::PATCH;
		$this->post = $post;
		return $this->send();
	}



	/**
	 * @param array|string $post
	 *
	 * @throws CurlException
	 * @return FileResponse
	 */
	public function download($post = array())
	{
		$this->method = static::DOWNLOAD;
		$this->post = $post;
		return $this->send();
	}



	/**
	 * Creates new request that can follow requested location
	 * @param Response $response
	 *
	 * @return Request
	 */
	final public function followRedirect(Response $response)
	{
		$request = clone $this;
		if (!$request->isMethod(Request::DOWNLOAD)) {
			$request->setMethod(Request::GET);
		}
		$request->post = $request->files = array();
		$request->cookies = $response->getCookies() + $request->cookies;
		$request->setUrl(static::fixUrl($request->getUrl(), $response->headers['Location']));
		return $request;
	}



	/**
	 * @param string $name
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function __call($name, $args)
	{
		if (method_exists('Nette\Utils\ObjectMixin', 'callProperty')) {
			return ObjectMixin::callProperty($this, $name, $args);

		} else {
			return ObjectMixin::call($this, $name, $args);
		}
	}



	/**
	 * Clones the url
	 */
	public function __clone()
	{
		if ($this->url instanceof Url) {
			$this->url = clone $this->url;
		}
	}



	/**
	 * @param string|Url $from
	 * @param string|Url $to
	 *
	 * @throws InvalidUrlException
	 * @return Url
	 */
	public static function fixUrl($from, $to)
	{
		$lastUrl = new Url($from);
		$url = new Url($to);

		if (!$to instanceof Url && $url->path[0] !== '/') { // relative
			$url->path = substr($lastUrl->path, 0, strrpos($lastUrl->path, '/') + 1) . $url->path;
		}

		foreach (array('scheme', 'host', 'port') as $copy) {
			if (empty($url->{$copy})) {
				if (empty($lastUrl->{$copy})) {
					throw new InvalidUrlException("Missing URL $copy!");
				}

				$url->{$copy} = $lastUrl->{$copy};
			}
		}

		if (!$url->path || $url->path[0] !== '/') {
			$url->path = '/' . $url->path;
		}

		return $url;
	}



	/**
	 * @return array
	 */
	public function __sleep()
	{
		return array('url', 'method', 'headers', 'options', 'cookies', 'post', 'files');
	}

}
