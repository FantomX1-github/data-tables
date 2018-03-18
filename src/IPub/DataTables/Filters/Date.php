<?php
/**
 * Date.php
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

use IPub\DataTables\Components;

/**
 * DataTables column date filter control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Date extends Text
{
	/**
	 * @var string
	 */
	private $condition = '= ?';

	/**
	 * @var string
	 */
	private $dateFormatInput = 'd.m.Y';

	/**
	 * @var string
	 */
	private $dateFormatOutput = 'Y-m-d%';

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
	 * Sets date-input format
	 *
	 * @param string $format
	 *
	 * @return void
	 */
	public function setDateFormatInput(string $format)
	{
		$this->dateFormatInput = $format;
	}

	/**
	 * @return string
	 */
	public function getDateFormatInput() : string
	{
		return $this->dateFormatInput;
	}

	/**
	 * Sets date-output format.
	 *
	 * @param string $format
	 *
	 * @return void
	 */
	public function setDateFormatOutput(string $format)
	{
		$this->dateFormatOutput = $format;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __getCondition($value)
	{
		$condition = $this->getCondition();

		if ($this->getWhere() === NULL && is_string($condition)) {
			$column = $this->getColumn();

			$date = \DateTime::createFromFormat($this->dateFormatInput, $value);

			return $date !== FALSE
				? Condition::setupFromArray([$column, $condition, $date->format($this->dateFormatOutput)])
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
		$control->getControlPrototype()->appendAttribute('class', 'js-grid-filter-date');
		$control->getControlPrototype()->setAttribute('autocomplete', 'off');

		return $control;
	}
}
