<?php
/**
 * Text.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Columns
 * @since		5.0
 *
 * @date		21.10.14
 */

namespace IPub\DataTables\Components\Columns;

use Nette;
use Nette\Utils;

class Text extends Settings
{
	/**
	 * {@inheritdoc}
	 */
	protected $type = 'string';

	/**
	 * @var int
	 */
	protected $truncate;

	/**
	 * @param int $truncate
	 *
	 * @return $this
	 */
	public function setTruncate($truncate)
	{
		$this->truncate = $truncate;

		return $this;
	}

	/**
	 * @param array $row
	 */
	public function render($row)
	{
		if (!empty($this->renderer)) {
			$value = call_user_func($this->renderer, $row);

		} else {
			$value = $this->getColumnValue($row);
		}

		if ($this->truncate !== NULL) {
			$value = Utils\Strings::truncate($value, $this->truncate);
		}

		echo $value;
	}
}
