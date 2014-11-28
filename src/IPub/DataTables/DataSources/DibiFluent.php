<?php
/**
 * DibiFluent.php
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
 * Dibi Fluent data source
 *
 * @author      Petr Bugyík
 *
 * @property-read \DibiFluent $fluent
 * @property-read int $limit
 * @property-read int $offset
 * @property-read int $count
 * @property-read array $data
 */
class DibiFluent extends Nette\Object implements IDataSource
{
	/** @var \DibiFluent */
	protected $fluent;

	/** @var int */
	protected $limit;

	/** @var int */
	protected $offset;

	/**
	 * @param \DibiFluent $fluent
	 */
	public function __construct(\DibiFluent $fluent)
	{
		$this->fluent = $fluent;
	}

	/**
	 * @return \DibiFluent
	 */
	public function getFluent()
	{
		return $this->fluent;
	}

	/**
	 * @return int
	 */
	public function getLimit()
	{
		return $this->limit;
	}

	/**
	 * @return int
	 */
	public function getOffset()
	{
		return $this->offset;
	}

	/**
	 * @param Filters\Condition $condition
	 * @param \DibiFluent $fluent
	 */
	protected function makeWhere(Filters\Condition $condition, \DibiFluent $fluent = NULL)
	{
		$fluent = $fluent === NULL
			? $this->fluent
			: $fluent;

		if ($condition->callback) {
			callback($condition->callback)->invokeArgs(array($condition->value, $fluent));
		} else {
			call_user_func_array(array($fluent, 'where'), $condition->__toArray('[', ']'));
		}
	}

	/********************************** inline editation helpers ************************************/

	/**
	 * Default callback used when an editable column has customRender
	 *
	 * @param mixed $id
	 * @param string $idCol
	 *
	 * @return \DibiRow
	 */
	public function getRow($id, $idCol)
	{
		$fluent = clone $this->fluent;
		return $fluent
			->where("%n = %s", $id, $idCol)
			->fetch();
	}

	/*********************************** interface IDataSource ************************************/

	/**
	 * @return int
	 */
	public function getCount()
	{
		$fluent = clone $this->fluent;
		return $fluent->count();
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->fluent->fetchAll($this->offset, $this->limit);
	}

	/**
	 * @param array $conditions
	 */
	public function filter(array $conditions)
	{
		foreach ($conditions as $condition) {
			$this->makeWhere($condition);
		}
	}

	/**
	 * @param int $offset
	 * @param int $limit
	 */
	public function limit($offset, $limit)
	{
		$this->offset = $offset;
		$this->limit = $limit;
	}

	/**
	 * @param array $sorting
	 */
	public function sort(array $sorting)
	{
		foreach ($sorting as $column => $sort) {
			$this->fluent->orderBy("%n", $column, $sort);
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
		$fluent = clone $this->fluent;
		is_string($column) && $fluent->removeClause('SELECT')->select("DISTINCT $column");

		foreach ($conditions as $condition) {
			$this->makeWhere($condition, $fluent);
		}

		$items = array();
		$data = $fluent->fetchAll(0, $limit);
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