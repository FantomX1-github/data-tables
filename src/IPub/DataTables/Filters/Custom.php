<?php
/**
 * Custom.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
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
 * DataTables column custom filter control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Custom extends Filter
{
	/**
	 * @var Forms\IControl
	 */
	private $formControl;

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param Forms\IControl|Forms\Controls\BaseControl $formControl
	 */
	public function __construct(Components\Control $parent, string $name, Forms\IControl $formControl)
	{
		parent::__construct($parent, $name, $formControl->caption);

		$this->formControl = $formControl;
	}

	/**
	 * @return Forms\IControl
	 */
	protected function getFormControl() : Forms\IControl
	{
		return $this->formControl;
	}
}
