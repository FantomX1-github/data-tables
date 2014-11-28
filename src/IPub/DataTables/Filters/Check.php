<?php
/**
 * Check.php
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
class Check extends Filter
{
	/* representation TRUE in URI */
	const TRUE = '✓';

	/**
	 * @var string
	 */
	protected $condition = 'IS NOT NULL';

	/**
	 * @return Forms\Controls\Checkbox
	 */
	protected function getFormControl()
	{
		return new Forms\Controls\Checkbox($this->label);
	}

	/**
	 * @param string $value
	 *
	 * @return array
	 */
	public function __getCondition($value)
	{
		$value = $value == self::TRUE
			? TRUE
			: FALSE;

		return parent::__getCondition($value);
	}

	/**
	 * @param bool $value
	 *
	 * @return NULL
	 */
	public function formatValue($value)
	{
		return NULL;
	}

	/**
	 * @param bool $value
	 *
	 * @return string
	 */
	public function changeValue($value)
	{
		return (bool) $value === TRUE
			? self::TRUE
			: $value;
	}
}