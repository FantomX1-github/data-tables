<?php
/**
 * Image.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     Columns
 * @since          1.0.0
 *
 * @date           21.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Columns;

use Nette\Utils;

use IPub\DataTables\Components;
use IPub\DataTables\Exceptions;

/**
 * Image column control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Image extends Column
{
	/**
	 * Define column data type for DataTables
	 */
	const COLUMN_DATA_TYPE = 'string';

	/**
	 * @var array|callable|NULL
	 */
	private $imageAttributes = NULL;

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param string $label
	 * @param string|NULL $insertBefore
	 */
	public function __construct(Components\Control $parent, string $name, string $label, string $insertBefore = NULL)
	{
		parent::__construct($parent, $name, $label, $insertBefore);

		$this->setType(self::COLUMN_DATA_TYPE);
		$this->disableSortable();
		$this->disableSearchable();
	}

	/**
	 * @param array|callable $imageAttributes
	 *
	 * @return void
	 */
	public function setImage($imageAttributes)
	{
		if (!is_array($imageAttributes) && !is_callable($imageAttributes)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided value is not valid. Only array or callable types are allowed. %s provided instead', gettype($imageAttributes)));
		}

		$this->imageAttributes = $imageAttributes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render($row)
	{
		$imageAttributes = [];

		if (is_callable($this->imageAttributes)) {
			$imageAttributes = call_user_func($this->imageAttributes, $row);

		} elseif ($this->imageAttributes !== NULL) {
			$imageAttributes = $this->imageAttributes;
		}

		if (is_array($imageAttributes) && $imageAttributes !== []) {
			echo Utils\Html::el('img')
				->addAttributes($imageAttributes);
		}
	}
}
