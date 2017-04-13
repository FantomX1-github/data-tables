<?php
/**
 * Check.php
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
 * DataTables column checkbox filter control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Check extends Filter
{
	// Representation TRUE in URI
	const TRUE = '✓';

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param string $label
	 */
	public function __construct(Components\Control $parent, string $name, string $label)
	{
		parent::__construct($parent, $name, $label);

		$this->setCondition('IS NOT NULL');
	}

	/**
	 * {@inheritdoc
	 */
	public function changeValue($value)
	{
		return (bool) $value === TRUE ? self::TRUE : $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function __getCondition($value)
	{
		return parent::__getCondition($value === self::TRUE);
	}

	/**
	 * @return Forms\Controls\Checkbox|Forms\IControl
	 */
	protected function getFormControl() : Forms\IControl
	{
		return new Forms\Controls\Checkbox($this->getLabel());
	}

	/**
	 * {@inheritdoc}
	 */
	protected function formatValue($value)
	{
		return NULL;
	}
}
