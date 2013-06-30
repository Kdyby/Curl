<?php

/**
 * This file is part of the Kdyby (http://www.kdyby.org)
 *
 * Copyright (c) 2008 Filip Procházka (filip@prochazka.su)
 *
 * For the full copyright and license information, please view the file license.md that was distributed with this source code.
 */

namespace Kdyby\Curl\DI;

use Kdyby;
use Nette;
use Nette\Utils\PhpGenerator as Code;



if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
}

if (isset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']) || !class_exists('Nette\Configurator')) {
	unset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']); // fuck you
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

/**
 * @author Filip Procházka <filip@prochazka.su>
 */
class CurlExtension extends Nette\DI\CompilerExtension
{

	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition($this->prefix('sender'))
			->setClass('Kdyby\Curl\CurlSender');
	}



	public function afterCompile(Code\ClassType $class)
	{
		/** @var Code\Method $init */
		$init = $class->methods['initialize'];
		$init->addBody('Kdyby\Curl\Diagnostics\Panel::registerBluescreen();');
	}



	/**
	 * @param \Nette\Configurator $configurator
	 */
	public static function register(Nette\Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Nette\DI\Compiler $compiler) {
			$compiler->addExtension('curl', new CurlExtension());
		};
	}

}
