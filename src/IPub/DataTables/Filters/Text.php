<?php
/**
 * Text.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Filters
 * @since		5.0
 *
 * @date		11.11.14
 */

namespace IPub\DataTables\Filters;

use Nette;
use Nette\Forms;

class Text extends Filter
{
	/**
	 * @var string
	 */
	protected $condition = 'LIKE ?';

	/**
	 * @var string
	 */
	protected $formatValue = '%%value%';

	/**
	 * @var bool
	 */
	protected $suggestion = FALSE;

	/**
	 * @var mixed
	 */
	protected $suggestionColumn;

	/**
	 * @var int
	 */
	protected $suggestionLimit = 10;

	/**
	 * @var callback
	 */
	protected $suggestionCallback;

	/**
	 * Allows suggestion
	 *
	 * @param mixed $column
	 *
	 * @return $this
	 */
	public function setSuggestion($column = NULL)
	{
		$this->suggestion = TRUE;
		$this->suggestionColumn = $column;

		$prototype = $this->getControl()->getControlPrototype();
		$prototype->attrs['autocomplete'] = 'off';
		$prototype->class[] = 'suggest';

		$filter = $this;

		$this->grid->onRender[] = function() use ($prototype, $filter) {
			$replacement = '-query-';

			$prototype->data('js-data-grid-suggest-replacement', $replacement);
			$prototype->data('js-data-grid-suggest-limit', $filter->suggestionLimit);
			$prototype->data('js-data-grid-suggest-handler', $filter->link('suggest!', [
				'query' => $replacement
			]));
		};

		return $this;
	}

	/**
	 * Sets a limit for suggestion select
	 *
	 * @param int $limit
	 *
	 * @return $this
	 */
	public function setSuggestionLimit($limit)
	{
		$this->suggestionLimit = (int) $limit;

		return $this;
	}

	/**
	 * Sets custom data callback
	 *
	 * @param callback $callback
	 *
	 * @return $this
	 */
	public function setSuggestionCallback($callback)
	{
		$this->suggestionCallback = $callback;

		return $this;
	}

	/**********************************************************************************************/

	/**
	 * @return int
	 */
	public function getSuggestionLimit()
	{
		return $this->suggestionLimit;
	}

	/**
	 * @param string $query - value from input
	 *
	 * @throws \Exception
	 */
	public function handleSuggest($query)
	{
		$this->grid->onRegistered && $this->grid->onRegistered($this->grid);
		$name = $this->getName();

		if (!$this->getPresenter()->isAjax() || !$this->suggestion || $query == '') {
			$this->getPresenter()->terminate();
		}

		$actualFilter = $this->grid->getActualFilter();
		if (isset($actualFilter[$name])) {
			unset($actualFilter[$name]);
		}

		$conditions = $this->grid->__getConditions($actualFilter);

		if ($this->suggestionCallback === NULL) {
			$conditions[] = $this->__getCondition($query);

			$column = $this->suggestionColumn ? $this->suggestionColumn : current($this->getColumn());
			$items = $this->grid->model->suggest($column, $conditions, $this->suggestionLimit);

		} else {
			$items = callback($this->suggestionCallback)->invokeArgs(array($query, $actualFilter, $conditions));
			if (!is_array($items)) {
				throw new \Exception('Items must be an array.');
			}
		}

		//sort items - first beginning of item is same as query, then case sensitive and case insensitive
		$startsWith = $caseSensitive = $caseInsensitive = array();
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
		$this->getPresenter()->sendResponse(new \Nette\Application\Responses\JsonResponse($items));
	}

	/**
	 * @return Forms\Controls\TextInput
	 */
	protected function getFormControl()
	{
		$control = new Forms\Controls\TextInput($this->label);
		$control->getControlPrototype()->class[] = 'text';

		return $control;
	}
}
