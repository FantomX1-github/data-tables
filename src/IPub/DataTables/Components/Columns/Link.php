<?php
/**
 * Link.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Columns
 * @since		5.0
 *
 * @date		26.10.14
 */

namespace IPub\DataTables\Components\Columns;

use Nette;
use Nette\Utils;

class Link extends Settings
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
	 *
	 * @return string
	 */
	public function render($row)
	{
		if (!empty($this->renderer)) {
			$anchor = call_user_func($this->renderer, $row);

		} else {
			$value = $this->getColumnValue($row);

			$href = $this->formatHref($value);
			$text = $this->formatText($value);

			$anchor = Utils\Html::el('a')
				->setHref($href)
				->setText($text);

			$anchor->attrs['target'] = '_blank';

			if ($this->truncate !== NULL) {
				$anchor
					->setText(Utils\Strings::truncate($value, $this->truncate))
					->setTitle($value);
			}
		}

		echo $anchor;
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	protected function formatHref($value)
	{
		if (!preg_match('~^\w+://~i', $value)) {
			$value = "http://" . $value;
		}

		return $value;
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	protected function formatText($value)
	{
		return preg_replace('~^https?://~i', '', $value);
	}
}
