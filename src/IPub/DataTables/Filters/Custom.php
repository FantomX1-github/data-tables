<?php
/**
 * Custom.php
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

use IPub\DataTables;
use IPub\DataTables\Components;

/**
 * @author      Petr Bugyík
 */
class Custom extends Filter
{
	/**
	 * @var Forms\IControl
	 */
	protected $formControl;

	/**
	 * @param Components\Control $grid
	 * @param string $name
	 * @param string $label
	 * @param Forms\IControl $formControl
	 */
	public function __construct($grid, $name, $label, Forms\IControl $formControl)
	{
		$this->formControl = $formControl;

		parent::__construct($grid, $name, $label);
	}

	/**
	 * @return Forms\IControl
	 */
	public function getFormControl()
	{
		return $this->formControl;
	}
}