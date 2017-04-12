<?php
/**
 * Date.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Columns
 * @since          1.0.0
 *
 * @date           21.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Columns;

use IPub\DataTables\Components;

/**
 * Date/Time column control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Date extends Column
{
	/**
	 * Define column data type for DataTables
	 */
	const COLUMN_DATA_TYPE = 'date';

	/**
	 * Define default date formats
	 */
	const FORMAT_TEXT = 'd M Y';
	const FORMAT_DATE = 'd.m.Y';
	const FORMAT_DATETIME = 'd.m.Y H:i:s';

	/**
	 * @var string
	 */
	private $dateFormat = self::FORMAT_DATE;

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param string $label
	 * @param string|NULL $insertBefore
	 * @param string|NULL $dateFormat
	 */
	public function __construct(Components\Control $parent, string $name, string $label, string $insertBefore = NULL, string $dateFormat = NULL)
	{
		parent::__construct($parent, $name, $label, $insertBefore);

		$this->setType(self::COLUMN_DATA_TYPE);

		if ($dateFormat !== NULL) {
			$this->setDateFormat($dateFormat);
		}
	}

	/**
	 * @param string $format
	 *
	 * @return void
	 */
	public function setDateFormat(string $format)
	{
		$this->dateFormat = $format;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render($row)
	{
		ob_start();
		parent::render($row);
		$value = ob_get_clean();

		echo $value instanceof \DateTime
			? $value->format($this->dateFormat)
			: date($this->dateFormat, is_numeric($value) ? (int) $value : strtotime($value)); // @todo notice for "01.01.1970"
	}
}
