<?php
/**
 * Select.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 * @since          1.0.0
 *
 * @date           11.11.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Filters;

use Nette\Forms;

use IPub\DataTables\Components;

/**
 * Select box filter control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Select extends Filter
{
	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param string $label
	 * @param array|NULL $items
	 */
	public function __construct(Components\Control $parent, string $name, string $label, array $items = NULL)
	{
		parent::__construct($parent, $name, $label);

		if ($items !== NULL) {
			$this->getControl()->setItems($items);
		}
	}

	/**
	 * @return Forms\Controls\SelectBox|Forms\IControl
	 */
	protected function getFormControl() : Forms\IControl
	{
		$control = new Forms\Controls\SelectBox($this->getLabel());
		$control->getControlPrototype()->appendAttribute('class', 'js-grid-filter-select');

		return $control;
	}
}
