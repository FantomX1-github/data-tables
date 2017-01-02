<?php
/**
 * DataTablesExtension.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	DI
 * @since		5.0
 *
 * @date		27.10.14
 */

namespace IPub\DataTables\DI;

use Nette;
use Nette\DI\Compiler;
use Nette\DI\Configurator;
use Nette\PhpGenerator as Code;

use Kdyby\Translation\DI\ITranslationProvider;

use IPub;

if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
}

class DataTablesExtension extends Nette\DI\CompilerExtension implements ITranslationProvider
{
	public function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();

		// State saver
		$builder->addDefinition($this->prefix('stateSaver'))
			->setClass('IPub\DataTables\StateSavers\StateSaver');

		// Define components
		$builder->addDefinition($this->prefix('dataTables'))
			->setClass('IPub\DataTables\Components\Control')
			->setImplement('IPub\DataTables\Components\IControl')
			->addTag('cms.components');
	}

	/**
	 * @param \Nette\Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Nette\Configurator $config, $extensionName = 'dataTables')
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new DataTablesExtension());
		};
	}

	/**
	 * Return array of directories, that contain resources for translator.
	 *
	 * @return string[]
	 */
	function getTranslationResources()
	{
		return array(
			__DIR__ . '/../Translations'
		);
	}
}
