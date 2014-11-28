<?php
/**
 * Condition.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Filters
 * @since		5.0
 *
 * @date		21.10.14
 */

namespace IPub\DataTables\Filters;

use Nette;
use Nette\Utils;

use IPub\DataTables;
use IPub\DataTables\Exceptions;

/**
 * Builds filter condition
 *
 * @author			Petr Bugyík
 *
 * @property-read	callable $callback
 * @property-write	array $column
 * @property-write	array $condition
 * @property-write	array $value
 */
class Condition extends Nette\Object
{
	const OPERATOR_OR	= 'OR';
	const OPERATOR_AND	= 'AND';

	/** @var array */
	protected $column;

	/** @var array */
	protected $condition;

	/** @var array */
	protected $value;

	/** @var callable */
	protected $callback;

	/**
	 * @param mixed $column
	 * @param mixed $condition
	 * @param mixed $value
	 */
	public function __construct($column, $condition, $value = NULL)
	{
		$this->setColumn($column);
		$this->setCondition($condition);
		$this->setValue($value);
	}

	/**
	 * @param mixed $column
	 *
	 * @return $this
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function setColumn($column)
	{
		if (is_array($column)) {
			$count = count($column);

			//check validity
			if ($count % 2 === 0) {
				throw new Exceptions\InvalidArgumentException('Count of column must be odd.');
			}

			for ($i = 0; $i < $count; $i++) {
				$item = $column[$i];
				if ($i & 1 && !self::isOperator($item)) {
					$msg = "The even values of column must be 'AND' or 'OR', '$item' given.";
					throw new Exceptions\InvalidArgumentException($msg);
				}
			}

		} else {
			$column = (array) $column;
		}

		$this->column = $column;

		return $this;
	}

	/**
	 * @param mixed $condition
	 *
	 * @return $this
	 */
	public function setCondition($condition)
	{
		$this->condition = (array) $condition;

		return $this;
	}

	/**
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public function setValue($value)
	{
		$this->value = (array) $value;

		return $this;
	}

	/**********************************************************************************************/

	/**
	 * @return array
	 */
	public function getColumn()
	{
		return $this->column;
	}

	/**
	 * @return array
	 */
	public function getCondition()
	{
		return $this->condition;
	}

	/**
	 * @return array
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @return array
	 */
	public function getValueForColumn()
	{
		if (count($this->condition) > 1) {
			return $this->value;
		}

		$values = array();
		foreach ($this->getColumn() as $column) {
			if (!self::isOperator($column)) {
				foreach ($this->getValue() as $val) {
					$values[] = $val;
				}
			}
		}

		return $values;
	}

	/**
	 * @return array
	 */
	public function getColumnWithoutOperator()
	{
		$columns = array();
		foreach ($this->column as $column) {
			if (!self::isOperator($column)) {
				$columns[] = $column;
			}
		}

		return $columns;
	}

	/**
	 * @return callable
	 */
	public function getCallback()
	{
		return $this->callback;
	}

	/**********************************************************************************************/

	/**
	 * Returns TRUE if $item is Condition:OPERATOR_AND or Condition:OPERATOR_OR else FALSE
	 *
	 * @param string $item
	 *
	 * @return bool
	 */
	public static function isOperator($item)
	{
		return in_array(strtoupper($item), array(self::OPERATOR_AND, self::OPERATOR_OR));
	}

	/**
	 * @param mixed $column
	 * @param string $condition
	 * @param mixed $value
	 *
	 * @return $this
	 */
	public static function setup($column, $condition, $value)
	{
		return new self($column, $condition, $value);
	}

	/**
	 * @return $this
	 */
	public static function setupEmpty()
	{
		return new self(NULL, '0 = 1');
	}

	/**
	 * @param array $condition
	 *
	 * @return $this
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public static function setupFromArray(array $condition)
	{
		if (count($condition) !== 3) {
			throw new Exceptions\InvalidArgumentException("Condition array must contain 3 items.");
		}

		return new self($condition[0], $condition[1], $condition[2]);
	}

	/**
	 * @param callable $callback
	 * @param string $value
	 *
	 * @return $this
	 */
	public static function setupFromCallback($callback, $value)
	{
		$self = new self(NULL, NULL);
		$self->value = $value;
		$self->callback = $callback;

		return $self;
	}

	/**********************************************************************************************/

	/**
	 * @param string $prefix - column prefix
	 * @param string $suffix - column suffix
	 * @param bool $brackets - add brackets when multiple where
	 *
	 * @return array
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function __toArray($prefix = NULL, $suffix = NULL, $brackets = TRUE)
	{
		$condition = array();
		$addBrackets = $brackets && count($this->column) > 1;

		if ($addBrackets) {
			$condition[] = '(';
		}

		$i = 0;
		foreach ($this->column as $column) {
			if (self::isOperator($column)) {
				$operator = strtoupper($column);
				$condition[] = " $operator ";

			} else {
				$i = count($this->condition) > 1 ? $i : 0;
				$condition[] = "{$prefix}$column{$suffix} {$this->condition[$i]}";

				$i++;
			}
		}

		if ($addBrackets) {
			$condition[] = ')';
		}

		return $condition
			? array_values(array_merge(array(implode('', $condition)), $this->getValueForColumn()))
			: $this->condition;
	}
}