<?php
/**
 * Number.php
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

/**
 * @author      Petr Bugyík
 */
class Number extends Text
{
	/**
	 * @var string
	 */
	protected $condition;

	/**
	 * @return Forms\Controls\TextInput
	 */
	protected function getFormControl()
	{
		$control = parent::getFormControl();
		$control->getControlPrototype()->title = sprintf($this->translate('DataTables.HintNumber'), rand(1, 9));
		$control->getControlPrototype()->class[] = 'number';

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
}