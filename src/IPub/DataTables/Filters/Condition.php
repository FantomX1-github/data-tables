<?php
/**
 * Condition.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 * @since          1.0.0
 *
 * @date           21.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Filters;

use Nette;

use IPub\DataTables\Exceptions;

/**
 * Builds filter condition
 *
 * @author            Petr Bugyík
 *
 * @property-read callable $callback
 * @property-write array $column
 * @property-write array $condition
 * @property-write array $value
 */
class Condition
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	const OPERATOR_OR = 'OR';
	const OPERATOR_AND = 'AND';

	/**
	 * @var array
	 */
	private $column = [];

	/**
	 * @var array
	 */
	private $condition = [];

	/**
	 * @var array
	 */
	private $value = [];

	/**
	 * @var callable
	 */
	private $callback;

	/**
	 * @param mixed $column
	 * @param string|NULL $condition
	 * @param mixed|NULL $value
	 */
	public function __construct($column, string $condition = NULL, $value = NULL)
	{
		$this->setColumn($column);
		$this->setCondition($condition);
		$this->setValue($value);
	}

	/**
	 * @param mixed $column
	 *
	 * @return void
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
					throw new Exceptions\InvalidArgumentException(sprintf('The even values of column must be \'AND\' or \'OR\', "%s" given.', $item));
				}
			}

		} else {
			$column = (array) $column;
		}

		$this->column = $column;
	}

	/**
	 * @return array
	 */
	public function getColumn() : array
	{
		return $this->column;
	}

	/**
	 * @param mixed $condition
	 *
	 * @return void
	 */
	public function setCondition($condition)
	{
		$this->condition = (array) $condition;
	}

	/**
	 * @return array
	 */
	public function getCondition() : array
	{
		return $this->condition;
	}

	/**
	 * @param mixed $value
	 *
	 * @return void
	 */
	public function setValue($value)
	{
		$this->value = (array) $value;
	}

	/**
	 * @return array
	 */
	public function getValue() : array
	{
		return $this->value;
	}

	/**
	 * @param callable $callback
	 *
	 * @return void
	 */
	public function setCallback(callable $callback)
	{
		$this->callback = $callback;
	}

	/**
	 * @return array
	 */
	public function getValueForColumn() : array
	{
		if (count($this->condition) > 1) {
			return $this->value;
		}

		$values = [];

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
	public function getColumnWithoutOperator() : array
	{
		$columns = [];

		foreach ($this->column as $column) {
			if (!self::isOperator($column)) {
				$columns[] = $column;
			}
		}

		return $columns;
	}

	/**
	 * @return callable|NULL
	 */
	public function getCallback()
	{
		return $this->callback;
	}

	/**
	 * Returns TRUE if $item is Condition:OPERATOR_AND or Condition:OPERATOR_OR else FALSE
	 *
	 * @param string $item
	 *
	 * @return bool
	 */
	public static function isOperator($item) : bool
	{
		return in_array(strtoupper($item), [self::OPERATOR_AND, self::OPERATOR_OR], TRUE);
	}

	/**
	 * @param mixed $column
	 * @param string $condition
	 * @param mixed $value
	 *
	 * @return Condition
	 */
	public static function setup($column, string $condition, $value) : Condition
	{
		return new self($column, $condition, $value);
	}

	/**
	 * @return Condition
	 */
	public static function setupEmpty() : Condition
	{
		return new self(NULL, '0 = 1');
	}

	/**
	 * @param array $condition
	 *
	 * @return Condition
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public static function setupFromArray(array $condition) : Condition
	{
		if (count($condition) !== 3) {
			throw new Exceptions\InvalidArgumentException('Condition array must contain 3 items.');
		}

		return new self($condition[0], $condition[1], $condition[2]);
	}

	/**
	 * @param callable $callback
	 * @param string $value
	 *
	 * @return Condition
	 */
	public static function setupFromCallback($callback, $value) : Condition
	{
		$self = new self(NULL, NULL);
		$self->setValue($value);
		$self->setCallback($callback);

		return $self;
	}

	/**
	 * @param string $prefix - column prefix
	 * @param string $suffix - column suffix
	 * @param bool $brackets - add brackets when multiple where
	 *
	 * @return array
	 */
	public function __toArray($prefix = NULL, $suffix = NULL, $brackets = TRUE) : array
	{
		$condition = [];
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
			? array_values(array_merge([implode('', $condition)], $this->getValueForColumn()))
			: $this->condition;
	}
}
