<?php
/**
 * ArraySource.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	DataSources
 * @since		5.0
 *
 * @date		23.10.14
 */

namespace IPub\DataTables\DataSources;

use Nette;
use Nette\Utils;

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
class ArraySource extends Nette\Object implements IDataSource
{
	/** @var array */
	protected $data;

	/**
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
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
				return callback($condition->callback)->invokeArgs(array($condition->value, $row));
			}

			$i = 0;
			$results = array();
			foreach ($condition->column as $column) {
				if (Filters\Condition::isOperator($column)) {
					$results[] = " $column ";

				} else {
					$i = count($condition->condition) > 1 ? $i : 0;
					$results[] = (int) $that->compare(
						$row[$column],
						$condition->condition[$i],
						isset($condition->value[$i]) ? $condition->value[$i] : NULL
					);

					$i++;
				}
			}

			$result = implode('', $results);
			return count($condition->column) === 1
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

		} elseif (in_array($cond, array('<', '<=', '>', '>='))) {
			return eval("return {$actual} {$cond} {$expected};");

		} else {
			throw new Exceptions\InvalidArgumentException("Condition '$condition' not implemented yet.");
		}
	}

	/*********************************** interface IDataSource ************************************/

	/**
	 * @return int
	 */
	public function getCount()
	{
		return count($this->data);
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	public function getRow($id)
	{
		return isset($this->data[$id]) ? $this->data[$id] : NULL;
	}

	/**
	 * @param array $conditions
	 */
	public function filter(array $conditions)
	{
		foreach ($conditions as $condition) {
			$this->data = $this->makeWhere($condition);
		}
	}

	/**
	 * @param int $offset
	 * @param int $limit
	 */
	public function limit($offset, $limit)
	{
		$this->data = array_slice($this->data, $offset, $limit);
	}

	/**
	 * @param array $sorting
	 *
	 * @throws \Exception
	 */
	public function sort(array $sorting)
	{
		if (count($sorting) > 1) {
			throw new \Exception('Multi-column sorting is not implemented yet.');
		}

		foreach ($sorting as $column => $sort) {
			$data = array();
			foreach ($this->data as $item) {
				$sorter = (string) $item[$column];
				$data[$sorter][] = $item;
			}

			if ($sort === 'ASC') {
				ksort($data);
			} else {
				krsort($data);
			}

			$this->data = array();
			foreach ($data as $i) {
				foreach ($i as $item) {
					$this->data[] = $item;
				}
			}
		}
	}

	/**
	 * @param mixed $column
	 * @param array $conditions
	 * @param int $limit
	 *
	 * @return array
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function suggest($column, array $conditions, $limit)
	{
		$data = $this->data;
		foreach ($conditions as $condition) {
			$data = $this->makeWhere($condition, $data);
		}

		array_slice($data, 1, $limit);

		$items = array();
		foreach ($data as $row) {
			if (is_string($column)) {
				$value = (string) $row[$column];
			} elseif (is_callable($column)) {
				$value = (string) $column($row);
			} else {
				$type = gettype($column);
				throw new Exceptions\InvalidArgumentException("Column of suggestion must be string or callback, $type given.");
			}

			$items[$value] = Nette\Templating\Helpers::escapeHtml($value);
		}

		return array_values($items);
	}
}
