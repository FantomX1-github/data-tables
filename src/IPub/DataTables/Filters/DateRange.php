<?php
/**
 * DateRange.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Filters
 * @since		5.0
 *
 * @date		13.11.14
 */

namespace IPub\DataTables\Filters;

use Nette;
use Nette\Forms;
use Nette\Utils;

/**
 * @author      Petr Bugyík
 *
 * @property string $mask
 */
class DateRange extends Date
{
	/**
	 * @var string
	 */
	protected $condition = 'BETWEEN ? AND ?';

	/**
	 * @var string
	 */
	protected $mask = '/(.*)\s?-\s?(.*)/';

	/**
	 * @var array
	 */
	protected $dateFormatOutput = array('Y-m-d', 'Y-m-d G:i:s');

	/**
	 * @param string $formatFrom
	 * @param string $formatTo
	 *
	 * @return $this
	 */
	public function setDateFormatOutput($formatFrom, $formatTo = NULL)
	{
		$formatTo = $formatTo === NULL
			? $formatFrom
			: $formatTo;

		$this->dateFormatOutput = array($formatFrom, $formatTo);

		return $this;
	}

	/**
	 * Sets mask by regular expression
	 *
	 * @param string $mask
	 *
	 * @return $this
	 */
	public function setMask($mask)
	{
		$this->mask = $mask;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getMask()
	{
		return $this->mask;
	}

	/**
	 * @return Forms\Controls\TextInput
	 */
	protected function getFormControl()
	{
		$control = parent::getFormControl();

		$prototype = $control->getControlPrototype();
		array_pop($prototype->class); // Remove "date" class
		$prototype->class[] = 'daterange';

		return $control;
	}

	/**
	 * @param string $value
	 *
	 * @return Condition
	 *
	 * @throws \Exception
	 */
	public function __getCondition($value)
	{
		if ($this->where === NULL && is_string($this->condition)) {

			list (, $from, $to) = Utils\Strings::match($value, $this->mask);
			$from = \DateTime::createFromFormat($this->dateFormatInput, trim($from));
			$to = \DateTime::createFromFormat($this->dateFormatInput, trim($to));

			if ($to && !Utils\Strings::match($this->dateFormatInput, '/G|H/i')) { //input format haven't got hour option
				Utils\Strings::contains($this->dateFormatOutput[1], 'G') || Utils\Strings::contains($this->dateFormatOutput[1], 'H')
					? $to->setTime(23, 59, 59)
					: $to->setTime(11, 59, 59);
			}

			$values = $from && $to
				? array($from->format($this->dateFormatOutput[0]), $to->format($this->dateFormatOutput[1]))
				: NULL;

			return $values
				? Condition::setup($this->getColumn(), $this->condition, $values)
				: Condition::setupEmpty();
		}

		return parent::__getCondition($value);
	}
}