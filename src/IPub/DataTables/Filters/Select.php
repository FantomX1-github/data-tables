<?php
/**
 * Select.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Filters
 * @since		5.0
 *
 * @date		11.11.14
 */

namespace IPub\DataTables\Filters;

use Nette;
use Nette\Forms;

use IPub\DataTables;
use IPub\DataTables\Components;

/**
 * @author      Petr Bugyík
 */
class Select extends Filter
{
	/**
	 * @param Components\Control $grid
	 * @param string $name
	 * @param string $label
	 * @param array $items for select
	 */
	public function __construct($grid, $name, $label, array $items = NULL)
	{
		parent::__construct($grid, $name, $label);

		if ($items !== NULL) {
			$this->getControl()->setItems($items);
		}
	}

	/**
	 * @return Forms\Controls\SelectBox
	 */
	protected function getFormControl()
	{
		return new Forms\Controls\SelectBox($this->label);
	}
}