<?php
/**
 * Image.php
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

class Image extends Settings
{
	/**
	 * {@inheritdoc}
	 */
	protected $sortable = FALSE;

	/**
	 * {@inheritdoc}
	 */
	protected $searchable = FALSE;

	/**
	 * {@inheritdoc}
	 */
	protected $type = 'string';

	/**
	 * @var array|callable
	 */
	protected $imageAttributes;

	/**
	 * @param array|callable $imageAttributes
	 * 
	 * @return $this
	 */
	public function setImage($imageAttributes)
	{
		$this->imageAttributes = $imageAttributes;

		return $this;
	}

	/**
	 * @param array $row
	 *
	 * @return string
	 */
	public function render($row)
	{
		if (is_callable($this->imageAttributes)) {
			$imageAttributes = call_user_func($this->imageAttributes, $row);

		} else if (is_array($this->imageAttributes)) {
			$imageAttributes = $this->imageAttributes;
		}

		echo Utils\Html::el('img')
			->addAttributes($imageAttributes);
	}
}