<?php
/**
 * Link.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Columns
 * @since          1.0.0
 *
 * @date           26.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Columns;

use Nette\Utils;

use IPub\DataTables\Components;

/**
 * URL address column control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Link extends Column
{
	/**
	 * Define column data type for DataTables
	 */
	const COLUMN_DATA_TYPE = 'string';

	/**
	 * @var int|NULL
	 */
	private $truncate = NULL;

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
	}

	/**
	 * @param int $truncate
	 *
	 * @return void
	 */
	public function setTruncate(int $truncate)
	{
		$this->truncate = $truncate;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render($row)
	{
		if ($this->getRenderer() !== NULL && is_callable($this->getRenderer())) {
			echo call_user_func($this->getRenderer(), $row);

		} else {
			$value = $this->getColumnValue($row);

			if ($value !== NULL) {
				$href = $this->formatHref((string) $value);
				$text = $this->formatText((string) $value);

				$anchor = Utils\Html::el('a');
				$anchor->setAttribute('href', $href);
				$anchor->setText($text);

				$anchor->setAttribute('target','_blank');

				if ($this->truncate !== NULL) {
					$anchor->setText(Utils\Strings::truncate($value, $this->truncate));
					$anchor->setAttribute('title', $value);
				}

				echo (string) $anchor;
			}
		}
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	private function formatHref(string $value) : string
	{
		if (!preg_match('~^\w+://~i', $value)) {
			$value = 'http://' . $value;
		}

		return $value;
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	private function formatText(string $value) : string
	{
		return preg_replace('~^https?://~i', '', $value);
	}
}
