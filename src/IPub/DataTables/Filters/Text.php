<?php
/**
 * Text.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 * @since          1.0.0
 *
 * @date           11.11.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Filters;

use Nette\Forms;

use IPub\DataTables\Components;

/**
 * Text field filter control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
class Text extends Filter
{
	/**
	 * @var string
	 */
	private $condition = 'LIKE ?';

	/**
	 * @var string
	 */
	private $formatValue = '%%value%';

	/**
	 * @var bool
	 */
	private $suggestion = FALSE;

	/**
	 * @var string|callable|NULL
	 */
	private $suggestionColumn = NULL;

	/**
	 * @var int
	 */
	private $suggestionLimit = 10;

	/**
	 * @var callback
	 */
	private $suggestionCallback;

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param string $label
	 */
	public function __construct(Components\Control $parent, string $name, string $label)
	{
		parent::__construct($parent, $name, $label);

		$this->setFormatValue($this->formatValue);
		$this->setCondition($this->condition);
	}

	/**
	 * Allows suggestion
	 *
	 * @param string|callable $column
	 *
	 * @return void
	 */
	public function setSuggestion($column = NULL)
	{
		$this->suggestion = TRUE;
		$this->suggestionColumn = $column;

		$prototype = $this->getControl()->getControlPrototype();
		$prototype->setAttribute('autocomplete', 'off');
		$prototype->appendAttribute('class', 'suggest');

		$this->parent->onRender[] = function () use ($prototype) {
			$replacement = '-query-';

			$prototype->data('js-data-grid-suggest-replacement', $replacement);
			$prototype->data('js-data-grid-suggest-limit', $this->suggestionLimit);
			$prototype->data('js-data-grid-suggest-handler', $this->link('suggest!', [
				'query' => $replacement,
			]));
		};
	}

	/**
	 * Sets a limit for suggestion select
	 *
	 * @param int $limit
	 *
	 * @return void
	 */
	public function setSuggestionLimit(int $limit)
	{
		$this->suggestionLimit = $limit;
	}

	/**
	 * Sets custom data callback
	 *
	 * @param callable $callback
	 *
	 * @return void
	 */
	public function setSuggestionCallback(callable $callback)
	{
		$this->suggestionCallback = $callback;
	}

	/**
	 * @param string $query - value from input
	 *
	 * @return void
	 *
	 * @throws \Exception
	 */
	public function handleSuggest(string $query) : void
	{
		$name = $this->getName();

		if (!$this->getPresenter()->isAjax() || !$this->suggestion || $query == '') {
			$this->getPresenter()->terminate();
		}

		$actualFilter = $this->parent->getActualFilter();

		if (isset($actualFilter[$name])) {
			unset($actualFilter[$name]);
		}

		$conditions = $this->parent->__getConditions($actualFilter);

		if ($this->suggestionCallback === NULL) {
			$conditions[] = $this->__getCondition($query);

			$column = $this->suggestionColumn ? $this->suggestionColumn : current($this->getColumn());
			$items = $this->parent->getModel()->suggest($column, $conditions, $this->suggestionLimit);

		} else {
			$items = callback($this->suggestionCallback)->invokeArgs([$query, $actualFilter, $conditions]);

			if (!is_array($items)) {
				throw new \Exception('Items must be an array.');
			}
		}

		// Sort items - first beginning of item is same as query, then case sensitive and case insensitive
		$startsWith = $caseSensitive = $caseInsensitive = [];

		foreach ($items as $item) {
			if (stripos($item, $query) === 0) {
				$startsWith[] = $item;

			} elseif (strpos($item, $query) !== FALSE) {
				$caseSensitive[] = $item;

			} else {
				$caseInsensitive[] = $item;
			}
		}

		sort($startsWith);
		sort($caseSensitive);
		sort($caseInsensitive);

		$items = array_merge($startsWith, $caseSensitive, $caseInsensitive);

		$this->getPresenter()->sendJson($items);
	}

	/**
	 * @return Forms\Controls\TextInput|Forms\IControl
	 */
	protected function getFormControl() : Forms\IControl
	{
		$control = new Forms\Controls\TextInput($this->getLabel());
		$control->getControlPrototype()->appendAttribute('class', 'js-grid-filter-text');

		return $control;
	}
}
