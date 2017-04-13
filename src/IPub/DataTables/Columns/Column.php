<?php
/**
 * Column.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Columns
 * @since          1.0.0
 *
 * @date           18.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Columns;

use Nette\Application\UI;
use Nette\ComponentModel;
use Nette\Forms;
use Nette\Utils;

use IPub\DataTables\Components;
use IPub\DataTables\Exceptions;
use IPub\DataTables\Filters;

/**
 * Column control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @property-read UI\Control $parent
 */
abstract class Column extends Settings implements IColumn
{
	/**
	 * @var callable|string
	 */
	private $label;

	/**
	 * @var callable
	 */
	private $renderer;

	/**
	 * @var callable|NULL
	 */
	private $cellRenderer;

	/**
	 * @var Filters\IFilter|NULL
	 */
	private $filter = NULL;

	/**
	 * @var bool
	 */
	private $editable = FALSE;

	/**
	 * @var string
	 */
	private $column;

	/**
	 * @var Utils\Html <th> html tag
	 */
	private $headerPrototype;

	/**
	 * @var Utils\Html <th/td> html tag
	 */
	private $cellPrototypes = [];

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param string $label
	 * @param string|NULL $insertBefore
	 */
	public function __construct(Components\Control $parent, string $name, string $label, string $insertBefore = NULL)
	{
		parent::__construct();

		// Register component to parent grid
		$this->addColumnToContainer($parent, $name, $insertBefore);

		// Created column label
		$this->label = $label;

		$this->setColumn($name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function setLabel($label)
	{
		if (!is_string($label) && !is_callable($label)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided value is not valid. Only string or callable types are allowed. %s provided instead', gettype($label)));
		}

		$this->label = $label;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLabel() : string
	{
		if (is_callable($this->label)) {
			return call_user_func($this->label);
		}

		$translator = $this->getGrid()->getTranslator();

		return $translator !== NULL ? $translator->translate($this->label) : $this->label;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setColumn(string $column)
	{
		$this->column = $column;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getColumn() : string
	{
		return $this->column !== NULL ? $this->column : $this->name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setRenderer(callable $renderer)
	{
		$this->renderer = $renderer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRenderer()
	{
		return $this->renderer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function render($row)
	{
		if ($this->renderer !== NULL && is_callable($this->renderer)) {
			$value = call_user_func($this->renderer, $row);

		} else {
			$value = $this->getGrid()->getModel()->getColumnValue($row, $this->getColumn());
		}

		echo $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setCellRenderer(callable $renderer)
	{
		$this->cellRenderer = $renderer;
	}

	/**
	 * {@inheritdoc}
	 */
	public function renderCell($row) : array
	{
		if (is_callable($this->cellRenderer)) {
			return call_user_func($this->cellRenderer, $row);
		}

		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasCellRenderer() : bool
	{
		return $this->cellRenderer !== NULL;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasFilter() : bool
	{
		return $this->filter !== NULL;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFilter()
	{
		return $this->filter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addFilterText(string $label) : Filters\Text
	{
		$this->filter = new Filters\Text($this->getGrid(), $this->name, $label);

		return $this->filter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addFilterNumber(string $label) : Filters\Number
	{
		$this->filter = new Filters\Number($this->getGrid(), $this->name, $label);

		return $this->filter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addFilterDate(string $label) : Filters\Date
	{
		$this->filter = new Filters\Date($this->getGrid(), $this->name, $label);

		return $this->filter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addFilterDateRange(string $label) : Filters\DateRange
	{
		$this->filter = new Filters\DateRange($this->getGrid(), $this->name, $label);

		return $this->filter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addFilterCheck(string $label) : Filters\Check
	{
		$this->filter = new Filters\Check($this->getGrid(), $this->name, $label);

		return $this->filter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addFilterSelect(string $label, array $items = NULL) : Filters\Select
	{
		$this->filter = new Filters\Select($this->getGrid(), $this->name, $label, $items);

		return $this->filter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function addFilterCustom(Forms\IControl $formControl) : Filters\Custom
	{
		$this->filter = new Filters\Custom($this->getGrid(), $this->name, $formControl);

		return $this->filter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setTextEditable(bool $asTextarea = FALSE, int $cols = NULL, int $rows = NULL)
	{
		if ($this->editable) {
			throw new Exceptions\DuplicateEditableColumnException(sprintf('Column %s is already editable.', $this->name));
		}

		if ($asTextarea) {
			/** @var Forms\Controls\TextArea $input */
			$input = $this->getRowForm()->addTextArea($this->name, NULL, $cols, $rows);

		} else {
			/** @var Forms\Controls\TextInput $input */
			$input = $this->getRowForm()->addText($this->name, NULL);
		}

		$input->getControlPrototype()->appendAttribute('class', 'js-data-grid-editable');

		$this->editable = TRUE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSelectEditable(array $values, string $prompt = NULL, bool $multiSelect = FALSE)
	{
		if ($this->editable) {
			throw new Exceptions\DuplicateEditableColumnException(sprintf('Column %s is already editable.', $this->name));
		}

		if ($multiSelect === TRUE) {
			/** @var Forms\Controls\MultiSelectBox $input */
			$input = $this->getRowForm()->addMultiSelect($this->name, NULL, $values);

		} else {
			/** @var Forms\Controls\SelectBox $input */
			$input = $this->getRowForm()->addSelect($this->name, NULL, $values);
		}

		$input->getControlPrototype()->appendAttribute('class', 'js-data-grid-editable');

		if ($prompt !== NULL) {
			$input->setPrompt($prompt);
		}

		$this->editable = TRUE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setBooleanEditable()
	{
		if ($this->editable) {
			throw new Exceptions\DuplicateEditableColumnException(sprintf('Column %s is already editable.', $this->name));
		}

		/** @var Forms\Controls\Checkbox $input */
		$input = $this->getRowForm()->addCheckbox($this->name, NULL);

		$input->getControlPrototype()->appendAttribute('class', 'js-data-grid-editable');

		$this->editable = TRUE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDateEditable()
	{
		if ($this->editable) {
			throw new Exceptions\DuplicateEditableColumnException(sprintf('Column %s is already editable.', $this->name));
		}

		/** @var Forms\Controls\TextInput $input */
		$input = $this->getRowForm()->addText($this->name, NULL);

		$input->getControlPrototype()->appendAttribute('class', 'js-data-grid-editable js-data-grid-datepicker');

		$this->editable = TRUE;
	}

	/**
	 * @return bool
	 */
	public function isEditable() : bool
	{
		return $this->editable;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getHeaderPrototype() : Utils\Html
	{
		if ($this->headerPrototype === NULL) {
			$element = $this->headerPrototype = Utils\Html::el('th');
			$element->appendAttribute('class', ' column js-data-grid-header-' . $this->getName());
		}

		return $this->headerPrototype;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCellPrototype($row) : Utils\Html
	{
		if (!isset($this->cellPrototypes[$this->getGrid()->getModel()->getRowIdentifier($row)])) {
			$element = $this->cellPrototypes[$this->getGrid()->getModel()->getRowIdentifier($row)] = Utils\Html::el($this->getCellType());
			$element->appendAttribute('class', 'column js-data-grid-cell-' . $this->getName() . ' ' . $this->getClassName());

			if ($this->hasCellRenderer()) {
				$this->cellPrototypes[$this->getGrid()->getModel()->getRowIdentifier($row)]->addAttributes($this->renderCell($row));
			}
		}

		return $this->cellPrototypes[$this->getGrid()->getModel()->getRowIdentifier($row)];
	}

	/**
	 * @return Components\Control
	 */
	protected function getGrid() : Components\Control
	{
		/** @var Components\Control $gridControl */
		$gridControl = $this->lookup(Components\Control::class);

		return $gridControl;
	}

	/**
	 * @return UI\Form
	 */
	protected function getRowForm() : UI\Form
	{
		$gridControl = $this->getGrid();

		return $gridControl['gridForm']['rowForm'];
	}

	/**
	 * @param Components\Control $grid
	 * @param string $name
	 * @param string $insertBefore
	 *
	 * @return void
	 */
	private function addColumnToContainer(Components\Control $grid, string $name, string $insertBefore = NULL)
	{
		/** @var ComponentModel\Container $container */
		$container = $grid->getComponent(self::ID, FALSE);

		// Check container exist
		if ($container === NULL) {
			$grid->addComponent(new ComponentModel\Container, self::ID);

			$container = $grid->getComponent(self::ID);
		}

		$container->addComponent($this, $name, $insertBefore);
	}
}
