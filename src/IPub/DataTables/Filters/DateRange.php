<?php
/**
 * DateRange.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 * @since          1.0.0
 *
 * @date           13.11.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Filters;

use Nette\Forms;
use Nette\Utils;

use IPub\DataTables\Components;

/**
 * DataTables column date filter control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class DateRange extends Date
{
	/**
	 * @var string
	 */
	private $condition = 'BETWEEN ? AND ?';

	/**
	 * @var string
	 */
	private $mask = '/(.*)\s?-\s?(.*)/';

	/**
	 * @var array
	 */
	private $dateFormatOutput = ['Y-m-d', 'Y-m-d G:i:s'];

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param string $label
	 */
	public function __construct(Components\Control $parent, string $name, string $label)
	{
		parent::__construct($parent, $name, $label);

		$this->setCondition($this->condition);
	}

	/**
	 * @param string $formatFrom
	 * @param string $formatTo
	 *
	 * @return void
	 */
	public function setDateFormatOutput(string $formatFrom, string $formatTo = NULL)
	{
		$formatTo = $formatTo === NULL ? $formatFrom : $formatTo;

		$this->dateFormatOutput = [$formatFrom, $formatTo];
	}

	/**
	 * Sets mask by regular expression
	 *
	 * @param string $mask
	 *
	 * @return void
	 */
	public function setMask(string $mask)
	{
		$this->mask = $mask;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __getCondition($value)
	{
		if ($this->getWhere() === NULL && is_string($this->getCondition())) {
			list (, $from, $to) = Utils\Strings::match($value, $this->mask);

			$from = \DateTime::createFromFormat($this->getDateFormatInput(), trim($from));
			$to = \DateTime::createFromFormat($this->getDateFormatInput(), trim($to));

			// Input format haven't got hour option
			if ($to && !Utils\Strings::match($this->getDateFormatInput(), '/G|H/i')) {
				Utils\Strings::contains($this->dateFormatOutput[1], 'G') || Utils\Strings::contains($this->dateFormatOutput[1], 'H')
					? $to->setTime(23, 59, 59)
					: $to->setTime(11, 59, 59);
			}

			$values = $from && $to
				? [$from->format($this->dateFormatOutput[0]), $to->format($this->dateFormatOutput[1])]
				: NULL;

			return $values
				? Condition::setup($this->getColumn(), $this->condition, $values)
				: Condition::setupEmpty();
		}

		return parent::__getCondition($value);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getFormControl() : Forms\IControl
	{
		/** @var Forms\Controls\TextInput $control */
		$control = parent::getFormControl();
		$control->getControlPrototype()->appendAttribute('class', 'js-grid-filter-daterange');

		return $control;
	}
}
