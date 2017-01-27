<?php
/**
 * Number.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Columns
 * @since		5.0
 *
 * @date		21.10.14
 */

namespace IPub\DataTables\Components\Columns;

use Nette;
use Nette\ComponentModel;

use IPub;
use IPub\DataTables;
use IPub\DataTables\Components;

class Number extends Settings
{
	/**
	 * @const keys of array $numberFormat
	 */
	const NUMBER_FORMAT_DECIMALS			= 0;
	const NUMBER_FORMAT_DECIMAL_POINT		= 1;
	const NUMBER_FORMAT_THOUSANDS_SEPARATOR	= 2;

	/**
	 * {@inheritdoc}
	 */
	protected $type = 'num';

	/**
	 * @var array
	 */
	protected $numberFormat = array(
		self::NUMBER_FORMAT_DECIMALS			=> 0,
		self::NUMBER_FORMAT_DECIMAL_POINT		=> '.',
		self::NUMBER_FORMAT_THOUSANDS_SEPARATOR	=> ','
	);

	/**
	 * @param Components\Control $grid
	 * @param string $name
	 * @param string $label
	 * @param int $decimals number of decimal points
	 * @param string $decPoint separator for the decimal point
	 * @param string $thousandsSep thousands separator
	 */
	public function __construct(Components\Control $grid, $name, $label, $insertBefore = NULL, $decimals = NULL, $decPoint = NULL, $thousandsSep = NULL)
	{
		parent::__construct($grid, $name, $label, $insertBefore);

		$this->setNumberFormat($decimals, $decPoint, $thousandsSep);
	}

	/**
	 * Sets number format. Params are same as internal function number_format()
	 *
	 * @param int $decimals number of decimal points
	 * @param string $decPoint separator for the decimal point
	 * @param string $thousandsSep thousands separator
	 *
	 * @return $this
	 */
	public function setNumberFormat($decimals = NULL, $decPoint = NULL, $thousandsSep = NULL)
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

		return $this;
	}

	/**
	 * @return array
	 */
	public function getNumberFormat()
	{
		return $this->numberFormat;
	}

	/**
	 * @param array $row
	 *
	 * @return string
	 */
	public function render($row)
	{
		if (!empty($this->renderer)) {
			$value = call_user_func($this->renderer, $row);

		} else {
			$value = $this->getColumnValue($row);
		}

		$decimals		= $this->numberFormat[self::NUMBER_FORMAT_DECIMALS];
		$decPoint		= $this->numberFormat[self::NUMBER_FORMAT_DECIMAL_POINT];
		$thousandsSep	= $this->numberFormat[self::NUMBER_FORMAT_THOUSANDS_SEPARATOR];

		echo is_numeric($value)
			? number_format($value, $decimals, $decPoint, $thousandsSep)
			: $value;
	}
}
