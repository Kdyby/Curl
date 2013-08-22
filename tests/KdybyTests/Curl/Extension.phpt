<?php

/**
 * Test: Kdyby\Curl\Extension.
 *
 * @testCase KdybyTests\Curl\ExtensionTest
 * @author Filip Procházka <filip@prochazka.su>
 * @package Kdyby\Curl
 */

namespace KdybyTests\Curl;

use Kdyby;
use Nette;
use Tester;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';



/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class ExtensionTest extends Tester\TestCase
{

	/**
	 * @return \SystemContainer|\Nette\DI\Container
	 */
	protected function createContainer()
	{
		$config = new Nette\Configurator();
		$config->setTempDirectory(TEMP_DIR);
		Kdyby\Curl\DI\CurlExtension::register($config);

		return $config->createContainer();
	}



	public function testFunctional()
	{
		$dic = $this->createContainer();
		$sender = $dic->getService('curl.sender');
		Assert::true($sender instanceof Kdyby\Curl\CurlSender);
	}

}

\run(new ExtensionTest());
