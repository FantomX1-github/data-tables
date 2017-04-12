<?php
/**
 * Filter.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 * @since          1.0.0
 *
 * @date           11.11.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Filters;

use Nette;
use Nette\Application\UI;
use Nette\ComponentModel;
use Nette\Forms;
use Nette\Utils;

use IPub\DataTables\Components;
use IPub\DataTables\Exceptions;

/**
 * DataTables column filter control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @property-read UI\Control $parent
 */
abstract class Filter extends UI\Control implements IFilter
{
	/**
	 * @var string
	 */
	private $label;

	/**
	 * @var string
	 */
	private $type;

	/**
	 * @var array
	 */
	private $column = [];

	/**
	 * @var string
	 */
	private $condition = '= ?';

	/**
	 * @var callable|NULL
	 */
	private $where = NULL;

	/**
	 * @var string|NULL
	 */
	private $formatValue = NULL;

	/**
	 * @var Utils\Html|NULL
	 */
	private $wrapperPrototype = NULL;

	/**
	 * @var Forms\Controls\BaseControl
	 */
	private $control;

	/**
	 * @param Components\Control $parent
	 * @param string $name
	 * @param string $label
	 */
	public function __construct(Components\Control $parent, string $name, string $label)
	{
		parent::__construct();

		$this->addFilterToContainer($parent, $name);

		$this->label = $label;
		$this->type = get_class($this);

		$form = $this->getForm();

		$filters = $form->getComponent(self::ID, FALSE);

		if ($filters === NULL) {
			$filters = $form->addContainer(self::ID);
		}

		$filters->addComponent($this->getFormControl(), $name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLabel() : string
	{
		return $this->label;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setColumn(string $column, string $operator = Condition::OPERATOR_OR)
	{
		$columnAlreadySet = count($this->column) > 0;

		if (!Condition::isOperator($operator) && $columnAlreadySet) {
			throw new Exceptions\InvalidArgumentException('Operator must be Condition::OPERATOR_AND or Condition::OPERATOR_OR.');
		}

		if ($columnAlreadySet) {
			$this->column[] = $operator;
			$this->column[] = $column;

		} else {
			$this->column[] = $column;
		}
	}

	/**
	 * {@inheritdoc
	 */
	public function setCondition(string $condition)
	{
		$this->condition = $condition;
	}

	/**
	 * {@inheritdoc
	 */
	public function setWhere(callable $callback)
	{
		$this->where = $callback;
	}

	/**
	 * {@inheritdoc
	 */
	public function setFormatValue(string $format)
	{
		$this->formatValue = $format;
	}

	/**
	 * {@inheritdoc
	 */
	public function setDefaultValue(string $value)
	{
		$this->getGrid()->setDefaultFilter([$this->getName() => $value]);
	}

	/**
	 * {@inheritdoc
	 */
	public function getColumn() : array
	{
		if (!$this->column) {
			$column = $this->getName();

			if ($columnComponent = $this->getGrid()->getColumn($column, FALSE)) {
				$column = $columnComponent->getColumn(); // Use db column from column component
			}

			$this->setColumn($column);
		}

		return $this->column;
	}

	/**
	 * {@inheritdoc
	 */
	public function getControl() : Forms\Controls\BaseControl
	{
		if ($this->control === NULL) {
			$this->control = $this->getForm()->getComponent(self::ID)->getComponent($this->getName());
		}

		return $this->control;
	}

	/**
	 * {@inheritdoc
	 */
	public function getWrapperPrototype() : Utils\Html
	{
		if ($this->wrapperPrototype === NULL) {
			$this->wrapperPrototype = Utils\Html::el('th');
			$this->wrapperPrototype->addAttributes([
				'class' => 'js-data-grid-filter-' . $this->getName(),
			]);
		}

		return $this->wrapperPrototype;
	}

	/**
	 * {@inheritdoc
	 */
	public function getCondition() : string
	{
		return $this->condition;
	}

	/**
	 * @param string $value
	 *
	 * @return Condition
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function __getCondition($value)
	{
		if ($value === '' || $value === NULL) {
			return FALSE; // Skip
		}

		$condition = $this->getCondition();

		if ($this->where !== NULL) {
			$condition = Condition::setupFromCallback($this->where, $value);

		} elseif (is_string($condition)) {
			$condition = Condition::setup($this->getColumn(), $condition, $this->formatValue($value));

		} elseif ($condition instanceof Condition) {
			$condition = $condition;

		} elseif (is_callable($condition)) {
			$condition = callback($condition)->invokeArgs([$value]);

		} elseif (is_array($condition)) {
			$condition = isset($condition[$value])
				? $condition[$value]
				: Condition::setupEmpty();
		}

		if (is_array($condition)) { // For user-defined condition by array or callback
			$condition = Condition::setupFromArray($condition);

		} elseif ($condition !== NULL && !$condition instanceof Condition) {
			$type = gettype($condition);
			throw new Exceptions\InvalidArgumentException(sprintf('Condition must be array or Condition object. %s given.', $type));
		}

		return $condition;
	}

	/**
	 * {@inheritdoc
	 */
	public function changeValue(string $value)
	{
		return $value;
	}

	/**
	 * @return Forms\Controls\BaseControl
	 */
	abstract protected function getFormControl() : Forms\Controls\BaseControl;

	/**
	 * Format value for database
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	private function formatValue(string $value) : string
	{
		if ($this->formatValue !== NULL) {
			return str_replace(static::VALUE_IDENTIFIER, $value, $this->formatValue);

		} else {
			return $value;
		}
	}

	/**
	 * @param Components\Control $grid
	 * @param string $name
	 *
	 * @return void
	 */
	private function addFilterToContainer(Components\Control $grid, string $name)
	{
		/** @var ComponentModel\Container $container */
		$container = $grid->getComponent(self::ID, FALSE);

		// Check container exist
		if ($container === NULL) {
			$grid->addComponent(new Nette\ComponentModel\Container, self::ID);

			$container = $grid->getComponent(self::ID);
		}

		$container->addComponent($this, $name);
	}

	/**
	 * @return UI\Form
	 */
	private function getForm() : UI\Form
	{
		return $this->getGrid()->getComponent('dataGridForm');
	}

	/**
	 * @return Components\Control
	 */
	private function getGrid() : Components\Control
	{
		/** @var Components\Control $gridControl */
		$gridControl = $this->parent->lookup(Components\Control::class);

		return $gridControl;
	}
}
