<?php
/**
 * Date.php
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

class Date extends Settings
{
	const FORMAT_TEXT		= 'd M Y';
	const FORMAT_DATE		= 'd.m.Y';
	const FORMAT_DATETIME	= 'd.m.Y H:i:s';

	/**
	 * {@inheritdoc}
	 */
	protected $type = 'date';

	/**
	 * @var string
	 */
	protected $dateFormat = self::FORMAT_DATE;

	/**
	 * @param Components\Control $grid
	 * @param string $name
	 * @param string $label
	 * @param string $dateFormat
	 */
	public function __construct(Components\Control $grid, $name, $label, $dateFormat = NULL)
	{
		parent::__construct($grid, $name, $label);

		if ($dateFormat !== NULL) {
			$this->dateFormat = $dateFormat;
		}
	}

	/**
	 * @param string $format
	 *
	 * @return $this
	 */
	public function setDateFormat($format)
	{
		$this->dateFormat = $format;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDateFormat()
	{
		return $this->dateFormat;
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
			$value = $row->{$this->name};
		}

		echo $value instanceof \DateTime
			? $value->format($this->dateFormat)
			: date($this->dateFormat, is_numeric($value) ? $value : strtotime($value)); // @todo notice for "01.01.1970"
	}

}