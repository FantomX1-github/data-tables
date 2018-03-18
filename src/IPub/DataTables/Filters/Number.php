<?php
/**
 * Number.php
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
 * DataTables column number filter control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Number extends Text
{
	/**
	 * @var string
	 */
	private $condition = '= ?';

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
	 * {@inheritdoc}
	 */
	public function __getCondition($value)
	{
		$condition = parent::__getCondition($value);

		if ($condition === NULL) {
			$condition = Condition::setupEmpty();

			if (preg_match('/(<>|[<|>]=?)?([-0-9,|.]+)/', $value, $matches)) {
				$value = str_replace(',', '.', $matches[2]);
				$operator = $matches[1]
					? $matches[1]
					: '=';

				$condition = Condition::setup($this->getColumn(), $operator . ' ?', $value);
			}
		}

		return $condition;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function getFormControl() : Forms\IControl
	{
		$control = parent::getFormControl();
		$control->getControlPrototype()->setAttribute('title', '');
		$control->getControlPrototype()->appendAttribute('class', 'js-grid-filter-number');

		return $control;
	}
}
