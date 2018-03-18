<?php
/**
 * Text.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     Columns
 * @since          1.0.0
 *
 * @date           21.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Columns;

use Nette\Utils;

use IPub\DataTables\Components;

/**
 * Text column control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Text extends Column
{
	/**
	 * Define column data type for DataTables
	 */
	const COLUMN_DATA_TYPE = 'string';

	/**
	 * @var int|NULL
	 */
	private $truncate = NULL;

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param string $label
	 * @param string|NULL $insertBefore
	 */
	public function __construct(Components\Control $parent, string $name, string $label, string $insertBefore = NULL)
	{
		parent::__construct($parent, $name, $label, $insertBefore);

		$this->setType(self::COLUMN_DATA_TYPE);
	}

	/**
	 * @param int $truncate
	 *
	 * @return void
	 */
	public function setTruncate(int $truncate)
	{
		$this->truncate = $truncate;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render($row)
	{
		ob_start();
		parent::render($row);
		$value = ob_get_clean();

		if ($this->truncate !== NULL) {
			$value = Utils\Strings::truncate($value, $this->truncate);
		}

		echo $value;
	}
}
