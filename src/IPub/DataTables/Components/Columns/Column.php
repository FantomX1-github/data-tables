<?php
/**
 * Column.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Columns
 * @since		5.0
 *
 * @date		18.10.14
 */

namespace IPub\DataTables\Components\Columns;

use Nette;
use Nette\Application\UI;
use Nette\Utils;
use Nette\Localization;

use IPub;
use IPub\DataTables;
use IPub\DataTables\Components;
use IPub\DataTables\Exceptions;
use IPub\DataTables\Filters;

abstract class Column extends UI\Control implements IColumn
{
	/**
	 * @var callback|string
	 */
	protected $label;

	/**
	 * @var callback|string
	 */
	protected $renderer;

	/**
	 * @var callback|string
	 */
	protected $cellRenderer;

	/**
	 * @var Filters\IFilter
	 */
	protected $filter;

	/**
	 * @var bool
	 */
	protected $editable = FALSE;

	/**
	 * @var Components\Control
	 */
	protected $parent;

	/**
	 * @var string
	 */
	protected $column;

	/**
	 * @var Utils\Html <th> html tag
	 */
	protected $headerPrototype;

	/**
	 * @var Utils\Html <th/td> html tag
	 */
	protected $cellPrototypes = [];

	/**
	 * @var Localization\ITranslator
	 */
	protected $translator;

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param string $label
	 * @param string|NULL $insertBefore
	 */
	public function __construct(Components\Control $parent, $name, $label, $insertBefore = NULL)
	{
		parent::__construct();

		// Register component to parent grid
		$this->addComponentToGrid($parent, $name, $insertBefore);

		// Created column label
		$this->label = $label;

		// Get translator
		$this->translator = $parent->getTranslator();
	}

	/**
	 * @param callback|string $label
	 *
	 * @return $this
	 */
	public function setLabel($label)
	{
		$this->label = $label;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->translator ? $this->translator->translate($this->label) : $this->label;
	}

	/**
	 * @param string $column
	 *
	 * @return $this
	 */
	public function setColumn($column)
	{
		$this->column = (string) $column;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getColumn()
	{
		return $this->column !== NULL ? $this->column : $this->name;
	}

	/**
	 * @param callback|string $renderer
	 *
	 * @return $this
	 */
	public function setRenderer($renderer)
	{
		$this->renderer = $renderer;

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

		echo $value;
	}

	/**
	 * @param callback|string $renderer
	 *
	 * @return $this
	 */
	public function setCellRenderer($renderer)
	{
		$this->cellRenderer = $renderer;

		return $this;
	}

	/**
	 * @param array $row
	 *
	 * @return string
	 */
	public function getCellRenderer($row)
	{
		if (is_callable($this->cellRenderer)) {
			return call_user_func($this->cellRenderer, $row);
		}

		return $this->cellRenderer;
	}

	/**
	 * @return bool
	 */
	public function hasCellRenderer()
	{
		return !empty($this->cellRenderer) ? TRUE : FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasFilter()
	{
		return $this->filter ? TRUE : FALSE;
	}

	/**
	 * @return Filters\IFilter
	 */
	public function getFilter()
	{
		return $this->filter;
	}

	/**
	 * @param string $label
	 *
	 * @return Filters\Text
	 */
	public function addFilterText($label)
	{
		$this->filter = new Filters\Text($this->parent, $this->name, $label);

		return $this->filter;
	}

	/**
	 * @param string $label
	 *
	 * @return Filters\Number
	 */
	public function addFilterNumber($label)
	{
		$this->filter = new Filters\Number($this->parent, $this->name, $label);

		return $this->filter;
	}

	/**
	 * @param string $label
	 *
	 * @return Filters\Date
	 */
	public function addFilterDate($label)
	{
		$this->filter = new Filters\Date($this->parent, $this->name, $label);

		return $this->filter;
	}

	/**
	 * @param string $label
	 *
	 * @return Filters\DateRange
	 */
	public function addFilterDateRange($label)
	{
		$this->filter = new Filters\DateRange($this->parent, $this->name, $label);

		return $this->filter;
	}

	/**
	 * @param string $label
	 *
	 * @return Filters\Check
	 */
	public function addFilterCheck($label)
	{
		$this->filter = new Filters\Check($this->parent, $this->name, $label);

		return $this->filter;
	}

	/**
	 * @param string $label
	 * @param array $items
	 *
	 * @return Filters\Select
	 */
	public function addFilterSelect($label, array $items = NULL)
	{
		$this->filter = new Filters\Select($this->parent, $this->name, $label, $items);

		return $this->filter;
	}

	/**
	 * @param Nette\Forms\IControl $formControl
	 *
	 * @return Filters\Custom
	 */
	public function addFilterCustom(Nette\Forms\IControl $formControl)
	{
		$this->filter = new Filters\Custom($this->parent, $this->name, NULL, $formControl);

		return $this->filter;
	}

	/**
	 * @param bool $textarea
	 * @param null|int $cols
	 * @param null|int $rows
	 *
	 * @return $this
	 *
	 * @throws Exceptions\DuplicateEditableColumnException
	 */
	public function setTextEditable($textarea = FALSE, $cols = NULL, $rows = NULL)
	{
		if ($this->editable) {
			throw new Exceptions\DuplicateEditableColumnException("Column $this->name is already editable.");
		}

		if ($textarea) {
			$input = $this->parent['dataGridForm']['rowForm']->addTextArea($this->name, NULL, $cols, $rows);

		} else {
			$input = $this->parent['dataGridForm']['rowForm']->addText($this->name, NULL);
		}

		$input->getControlPrototype()->addClass('js-data-grid-editable');

		$this->editable = TRUE;

		return $this;
	}

	/**
	 * @param array $values
	 * @param string|null $prompt
	 *
	 * @return $this
	 *
	 * @throws Exceptions\DuplicateEditableColumnException
	 */
	public function setSelectEditable(array $values, $prompt = NULL)
	{
		if ($this->editable) {
			throw new Exceptions\DuplicateEditableColumnException("Column $this->name is already editable.");
		}

		$this->parent['dataGridForm']['rowForm']
			->addSelect($this->name, NULL, $values)
				->getControlPrototype()
					->addClass('js-data-grid-editable');

		if ($prompt) {
			$this->parent['dataGridForm']['rowForm'][$this->name]->setPrompt($prompt);
		}

		$this->editable = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 *
	 * @throws Exceptions\DuplicateEditableColumnException
	 */
	public function setBooleanEditable()
	{
		if ($this->editable) {
			throw new Exceptions\DuplicateEditableColumnException("Column $this->name is already editable.");
		}

		$this->parent['dataGridForm']['rowForm']
			->addCheckbox($this->name, NULL)
				->getControlPrototype()
					->addClass('js-data-grid-editable');

		$this->editable = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 *
	 * @throws Exceptions\DuplicateEditableColumnException
	 */
	public function setDateEditable()
	{
		if ($this->editable) {
			throw new Exceptions\DuplicateEditableColumnException("Column $this->name is already editable.");
		}

		$this->parent['dataGridForm']['rowForm']
			->addText($this->name, NULL)
				->getControlPrototype()
					->addClass('js-data-grid-datepicker')
					->addClass('js-data-grid-editable');

		$this->editable = TRUE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isEditable()
	{
		return $this->editable;
	}

	/**
	 * @param Components\Control $parent
	 */
	public function setGrid(Components\Control $parent)
	{
		$this->parent = $parent;
	}

	/**
	 * @return Utils\Html
	 */
	public function getHeaderPrototype()
	{
		if ($this->headerPrototype === NULL) {
			$this->headerPrototype = Utils\Html::el('th')
				->setClass(array('column', 'js-data-grid-header-' . $this->getName()));
		}

		return $this->headerPrototype;
	}

	/**
	 * @param mixed $data
	 *
	 * @return Utils\Html
	 */
	public function getCellPrototype($data)
	{
		if (!isset($this->cellPrototypes[$data->{$this->parent->getPrimaryKey()}])) {
			$this->cellPrototypes[$data->{$this->parent->getPrimaryKey()}] = Utils\Html::el($this->getCellType())
				->setClass(array('column', 'js-data-grid-cell-' . $this->getName(), $this->getClassName()));

			if ($this->hasCellRenderer()) {
				$this->cellPrototypes[$data->{$this->parent->getPrimaryKey()}]->addAttributes($this->getCellRenderer($data));
			}
		}

		return $this->cellPrototypes[$data->{$this->parent->getPrimaryKey()}];
	}

	/**
	 * @param Components\Control $grid
	 * @param string $name
	 *
	 * @return Nette\ComponentModel\Container
	 */
	protected function addComponentToGrid(Components\Control $grid, $name, $insertBefore = NULL)
	{
		$this->parent = $grid;

		// Check container exist
		$container = $this->parent->getComponent($this::ID, FALSE);
		if (!$container) {
			$this->parent->addComponent(new Nette\ComponentModel\Container, $this::ID, $insertBefore);
			$container = $this->parent->getComponent($this::ID);
		}

		return $container->addComponent($this, $name, $insertBefore);
	}

	/**
	 * @param mixed $row
	 *
	 * @return mixed|NULL
	 */
	protected function getColumnValue($row)
	{
		if (is_array($row)) {
			return isset($row[$this->getName()]) ? $row[$this->getName()] : NULL;

		} elseif (is_object($row)) {
			return method_exists($row, 'get' . ucfirst($this->getName())) ? call_user_func([$row, 'get' . ucfirst($this->getName())]) : NULL;
		}

		return NULL;
	}
}
