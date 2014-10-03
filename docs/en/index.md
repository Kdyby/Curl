## Requirements

`Kdyby\Curl` requires PHP 5.3.2 or higher with cUrl extension enabled.

- [Nette Framework 2.0.x](https://github.com/nette/nette)

## Installation

The best way to install Kdyby/Curl is using [Composer](http://getcomposer.org/):

```sh
$ composer require kdyby/curl
```

## Getting started

### Basic methods

There are many ways to use `Kdyby\Curl`. Everytime it returns object of `Kdyby\Curl\Response` though. If the request failed it returns `Kdyby\Curl\CurlException`.

#### GET

```php
$request = new Kdyby\Curl\Request("http://curl.kdyby.org/prevodnik.asm.zdrojak");
try {
    $response = $request->get();

    echo $response->getResponse();
    var_dump($response->getHeaders()); // vrací pole hlaviček

} catch (Kdyby\Curl\CurlException $e ){
    echo $e->getMessage();
}
```

#### POST
```php
$request = new Kdyby\Curl\Request("http://curl.kdyby.org/dump_post.php");

try {
    $response =  $request->post(array(
        'var1' => 'Lorem ipsum dot sit amet',
        'var2' => 0,
        'var3' => 23,
        'var4' => True,
        'var5' => False,
    ));

    echo $response->getResponse();
    var_dump($response->getHeaders()); // vrací pole hlaviček

} catch (Kdyby\Curl\CurlException $e ){
    echo $e->getMessage();
}
```

### HTTP requests

Wrapper contains shortcuts for all basic HTTP methods.

```php
$request = new Kdyby\Curl\Request;

$url = "http://curl.kdyby.org/";
$one = $request->get($url, array(
    'var1' => 'value1',
    'var2' => 'value2'
)); # pošle žádost na http://curl.kdyby.org/?var1=value1&var2=value2

$two = $request->post($url, $post);
$three = $request->head($url, $query);
$four = $request->put($url, $query);
$five = $request->delete($url, $query);
```

Also you can set your own method to do the request.

```php
$request->method = 'MY_WEIRD_METHOD';
$odpoved = $request->send();
```

### Proxy
Wrapper supports proxy as well. It is possible to add as much as proxies you want. Wrapper will use all of them until the request don't success in required timeout.

```php
$sender = new Kdyby\Curl\CurlSender;
$sender->addProxy('192.168.1.160', 3128 [, $username = Null [, $password = Null [, $timeout = 15]]]);
$response = $sender->send($request);
```

### Custom headers

```php
$request->headers['Host'] = '12.345.678.90';
$request->headers['Some-Custom-Header'] = 'Some Custom Value';
```

---

Documentation translated with ♥ by [Northys](http://github.com/Northys)
