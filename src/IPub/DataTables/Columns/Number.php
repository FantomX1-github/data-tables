<?php
/**
 * Number.php
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

use IPub;
use IPub\DataTables;
use IPub\DataTables\Components;

/**
 * Number column control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Number extends Column
{
	/**
	 * Define column data type for DataTables
	 */
	const COLUMN_DATA_TYPE = 'num';

	/**
	 * @const keys of array $numberFormat
	 */
	const NUMBER_FORMAT_DECIMALS = 0;
	const NUMBER_FORMAT_DECIMAL_POINT = 1;
	const NUMBER_FORMAT_THOUSANDS_SEPARATOR = 2;

	/**
	 * @var array
	 */
	private $numberFormat = [
		self::NUMBER_FORMAT_DECIMALS            => 0,
		self::NUMBER_FORMAT_DECIMAL_POINT       => '.',
		self::NUMBER_FORMAT_THOUSANDS_SEPARATOR => ',',
	];

	/**
	 * @param Components\Control $grid
	 * @param string $name
	 * @param string $label
	 * @param string|NULL $insertBefore
	 * @param int|NULL $decimals
	 * @param string|NULL $decPoint
	 * @param string|NULL $thousandsSep
	 */
	public function __construct(Components\Control $grid, string $name, string $label, string $insertBefore = NULL, int $decimals = NULL, string $decPoint = NULL, string $thousandsSep = NULL)
	{
		parent::__construct($grid, $name, $label, $insertBefore);

		$this->setType(self::COLUMN_DATA_TYPE);
		$this->setNumberFormat($decimals, $decPoint, $thousandsSep);
	}

	/**
	 * @param int|NULL $decimals
	 * @param string|NULL $decPoint
	 * @param string|NULL $thousandsSep
	 *
	 * @return void
	 */
	public function setNumberFormat(int $decimals = NULL, string $decPoint = NULL, string $thousandsSep = NULL)
	{
		if ($decimals !== NULL) {
			$this->numberFormat[self::NUMBER_FORMAT_DECIMALS] = (int) $decimals;
		}

		if ($decPoint !== NULL) {
			$this->numberFormat[self::NUMBER_FORMAT_DECIMAL_POINT] = $decPoint;
		}

		if ($thousandsSep !== NULL) {
			$this->numberFormat[self::NUMBER_FORMAT_THOUSANDS_SEPARATOR] = $thousandsSep;
		}
	}

	/**
	 * @return array
	 */
	public function getNumberFormat() : array
	{
		return $this->numberFormat;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render($row)
	{
		ob_start();
		parent::render($row);
		$value = ob_get_clean();

		$decimals = $this->numberFormat[self::NUMBER_FORMAT_DECIMALS];
		$decPoint = $this->numberFormat[self::NUMBER_FORMAT_DECIMAL_POINT];
		$thousandsSep = $this->numberFormat[self::NUMBER_FORMAT_THOUSANDS_SEPARATOR];

		echo is_numeric($value)
			? number_format($value, $decimals, $decPoint, $thousandsSep)
			: $value;
	}
}
