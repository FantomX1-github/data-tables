<?php
/**
 * ArraySource.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     DataSources
 * @since          5.0
 *
 * @date           23.10.14
 */

namespace IPub\DataTables\DataSources;

use Nette;

use IPub\DataTables;
use IPub\DataTables\Exceptions;
use IPub\DataTables\Filters;

/**
 * Array data source
 *
 * @author      Josef Kříž <pepakriz@gmail.com>
 * @author      Petr Bugyík
 *
 * @property-read array $data
 * @property-read int $count
 */
class ArraySource implements IDataSource
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * Event called when grid data is loaded
	 *
	 * @var callable[]
	 */
	public $onDataLoaded = [];

	/**
	 * @var string
	 */
	private $primaryKey;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @param array $data
	 * @param string $primaryKey
	 */
	public function __construct(array $data, string $primaryKey)
	{
		$this->data = $data;
		$this->primaryKey = $primaryKey;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPrimaryKey() : string
	{
		return $this->primaryKey;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCount() : int
	{
		return count($this->data);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRows() : array
	{
		return $this->data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRow($id)
	{
		return isset($this->data[$id]) ? $this->data[$id] : NULL;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getColumnValue($row, string $column)
	{
		if (is_array($row)) {
			return isset($row[$column]) ? $row[$column] : NULL;

		} elseif (is_object($row)) {
			if (method_exists($row, 'get' . ucfirst($column)) === TRUE) {
				return call_user_func([$row, 'get' . ucfirst($column)]);

			} elseif (property_exists($row, $column) === TRUE) {
				return $row->{$column};
			}
		}

		throw new Exceptions\InvalidStateException(sprintf('Could not get row value for column "%s".', $column));
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRowIdentifier($row)
	{
		if (is_array($row)) {
			if (array_key_exists($this->getPrimaryKey(), $row) === TRUE) {
				return $row[$this->getPrimaryKey()];
			}

		} elseif (is_object($row)) {
			if (method_exists($row, 'get' . ucfirst($this->getPrimaryKey())) === TRUE) {
				return (string) call_user_func([$row, 'get' . ucfirst($this->getPrimaryKey())]);

			} elseif (property_exists($row, $this->getPrimaryKey()) === TRUE) {
				return $row->{$this->getPrimaryKey()};
			}
		}

		throw new Exceptions\InvalidStateException('Could not get row identifier.');
	}

	/**
	 * {@inheritdoc}
	 */
	public function limit(int $offset, int $limit)
	{
		$this->data = array_slice($this->data, $offset, $limit);
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter(array $conditions)
	{
		foreach ($conditions as $condition) {
			$this->data = $this->makeWhere($condition);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function sort(array $sorting)
	{
		if (count($sorting) > 1) {
			throw new \Exception('Multi-column sorting is not implemented yet.');
		}

		foreach ($sorting as $column => $sort) {
			$data = [];

			foreach ($this->data as $item) {
				$sorter = (string) $item->{$column};
				$data[$sorter][] = $item;
			}

			if ($sort === 'ASC') {
				ksort($data);

			} else {
				krsort($data);
			}

			$this->data = [];

			foreach ($data as $i) {
				foreach ($i as $item) {
					$this->data[] = $item;
				}
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function suggest($column, array $conditions, $limit)
	{
		$data = $this->data;

		foreach ($conditions as $condition) {
			$data = $this->makeWhere($condition, $data);
		}

		array_slice($data, 1, $limit);

		$items = [];

		foreach ($data as $row) {
			if (is_string($column)) {
				$value = (string) $row->{$column};

			} elseif (is_callable($column)) {
				$value = (string) $column($row);

			} else {
				$type = gettype($column);

				throw new Exceptions\InvalidArgumentException(sprintf('Column of suggestion must be string or callback, "%s" given.', $type));
			}

			$items[$value] = Nette\Templating\Helpers::escapeHtml($value);
		}

		return array_values($items);
	}

	/**
	 * @param Filters\Condition $condition
	 * @param array $data
	 *
	 * @return array
	 */
	protected function makeWhere(Filters\Condition $condition, array $data = NULL)
	{
		$data = $data === NULL
			? $this->data
			: $data;

		$that = $this;

		return array_filter($data, function ($row) use ($condition, $that) {
			if ($condition->callback) {
				return call_user_func_array($condition->callback, [$condition->getValue(), $row]);
			}

			$i = 0;
			$results = [];

			foreach ($condition->getColumn() as $column) {
				if (Filters\Condition::isOperator($column)) {
					$results[] = " $column ";

				} else {
					$i = count($condition->getCondition()) > 1 ? $i : 0;

					$results[] = (int) $that->compare(
						$row->{$column},
						$condition->getCondition()[$i],
						isset($condition->getValue()[$i]) ? $condition->getValue()[$i] : NULL
					);

					$i++;
				}
			}

			$result = implode('', $results);

			return count($condition->getColumn()) === 1
				? (bool) $result
				: eval("return $result;");
		});
	}

	/**
	 * @param string $actual
	 * @param string $condition
	 * @param mixed $expected
	 *
	 * @return bool
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function compare($actual, $condition, $expected)
	{
		$expected = (array) $expected;
		$expected = current($expected);
		$cond = str_replace(' ?', '', $condition);

		if ($cond === 'LIKE') {
			$pattern = str_replace('%', '(.|\s)*', preg_quote($expected, '/'));

			return (bool) preg_match("/^{$pattern}$/i", $actual);

		} elseif ($cond === '=') {
			return $actual == $expected;

		} elseif ($cond === '<>') {
			return $actual != $expected;

		} elseif ($cond === 'IS NULL') {
			return $actual === NULL;

		} elseif ($cond === 'IS NOT NULL') {
			return $actual !== NULL;

		} elseif (in_array($cond, ['<', '<=', '>', '>='])) {
			return eval("return {$actual} {$cond} {$expected};");

		} else {
			throw new Exceptions\InvalidArgumentException("Condition '$condition' not implemented yet.");
		}
	}
}
