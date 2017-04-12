<?php
/**
 * DataTablesExtension.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     DI
 * @since          1.0.0
 *
 * @date           27.10.14
 */

declare(strict_types = 1);

namespace IPub\DataTables\DI;

use Nette;
use Nette\DI;

use IPub\DataTables\Components;
use IPub\DataTables\StateSavers;

/**
 * DataTables extension container
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     DI
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @method DI\ContainerBuilder getContainerBuilder()
 * @method string prefix($id)
 */
final class DataTablesExtension extends DI\CompilerExtension
{
	/**
	 * @return void
	 */
	public function loadConfiguration()
	{
		/** @var DI\ContainerBuilder $builder */
		$builder = $this->getContainerBuilder();

		// State saver
		$builder->addDefinition($this->prefix('stateSaver'))
			->setClass(StateSavers\StateSaver::class);

		// Define components
		$builder->addDefinition($this->prefix('dataTables'))
			->setClass(Components\Control::class)
			->setImplement(Components\IControl::class)
			->addTag('cms.components');
	}

	/**
	 * @param Nette\Configurator $config
	 * @param string $extensionName
	 *
	 * @return void
	 */
	public static function register(Nette\Configurator $config, string $extensionName = 'dataTables')
	{
		$config->onCompile[] = function (Nette\Configurator $config, Nette\DI\Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new DataTablesExtension());
		};
	}
}
