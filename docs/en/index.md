# Quickstart

Kdyby/Curl is a simple Curl wrapper for sending various HTTP requests. It requires `curl` extension enabled.


## Installation

The best way to install Kdyby/Curl is using [Composer](http://getcomposer.org/):

```sh
$ composer require kdyby/curl:~2.2
```

You can optionally register the extension, which handles bluescreen rendering on CurlException.

```neon
extensions:
	curl: Kdyby\Curl\DI\CurlExtension
```


## Basic requests

The response is always instance of `Kdyby\Curl\Response` or when error occurs, the `Kdyby\Curl\CurlException` is thrown

```php
$test = new Curl\Request("https://www.kdyby.org/");
try {
        $response = $test->get();

        var_dump($response->getHeaders());
        echo $response->getResponse();

} catch (Curl\CurlException $e) {
        echo $e->getMessage();
}
```

```php
$test = new Curl\Request("https://www.kdyby.org/");

try {
        $response =  $test->post(array(
                'var1' => 'Lorem ipsum dot sit amet',
                'var2' => 0,
                'var3' => 23,
                'var4' => True,
                'var5' => False,
        ));

        var_dump($response->getHeaders());
        echo $response->getBody();

} catch (Curl\CurlException $e) {
        echo $e->getMessage();
}
```

### Other methods

Wrapper contains shortcuts for all basic HTTP methods.

```php
$request = new Kdyby\Curl\Request("https://www.kdyby.org/");

$one = $request->get(array('var1' => 'value1', 'var2' => 'value2'));
// sends request to https://www.kdyby.org/?var1=value1&var2=value2

$two = $request->post($post, $files);
$three = $request->head($query);
$four = $request->put($post);
$five = $request->patch($post);
$six = $request->delete();
```

Also you can set your own method to do the request.

```php
$request->method = 'MY_WEIRD_METHOD';
$response = $request->send();
```

### Custom headers


```php
$request->headers['Host'] = '12.345.678.90';
$request->headers['Some-Custom-Header'] = 'Some Custom Value';
```


## CurlSender

This is a class, that is always used to send the request. It's better if you create and hold onto your own instance of it, but it's optional.

```php
$curl = new Kdyby\Curl\CurlSender();
$response = $request->send(new Request('https://www.kdyby.org'));
```


### Proxy

It's also possible to setup proxy servers which will be tried in order until one of them returns the answer or if all fail, the `CurlException` is thrown.

```php
# last three parameters are optional
$curl->addProxy('192.168.1.160', 3128 [, $username = Null [, $password = Null [, $timeout = 15]]]);
```

### Default headers

By setting the headers to the `CurlSender`, you make them default for all the requests that will be processed by this instance.

```php
$curl->headers['Content-Type'] = 'application/json';
```


### Disabling follow redirect

```php
// this is by default allowed
$curl->setFollowRedirects(FALSE);
```

### User agent

You can also easily set the browser name, and there are even some default browser identifiers.

| Shortcut | Full browser name |
| -------- | ----------------- |
| `FireFox3` | `Mozilla/5.0 (Windows; U; Windows NT 5.1; pl; rv:1.9) Gecko/2008052906 Firefox/3.0` |
| `GoogleBot` | `Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)` |
| `IE7` | `Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 6.0)` |
| `Netscape` | `Mozilla/4.8 [en] (Windows NT 6.0; U)` |
| `Opera` | `Opera/9.25 (Windows NT 6.0; U; en)` |

```php
$curl->setReferer('http://google.com');
$curl->setUserAgent('FireFox3');
```

### Other options

You can setup or change every option that is listed at [php.net/curl_setopt](http://php.net/curl_setopt).
It's case insensitive

```php
$curl->setOption('AUTOREFERER', TRUE);
$curl->setOption('autoReferer', TRUE);
```

Or array will work too

```php
$curl->setOptions($array);
```
