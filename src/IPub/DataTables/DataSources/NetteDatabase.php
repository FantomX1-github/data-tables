<?php
/**
 * Doctrine.php
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
use Nette\Database;
use Nette\Utils;

use IPub\DataTables;
use IPub\DataTables\Exceptions;
use IPub\DataTables\Filters;

/**
 * Nette Database data source.
 *
 * @author      Petr Bugyík
 *
 * @property-read Database\Table\Selection $selection
 * @property-read int $count
 * @property-read array $data
 */
class NetteDatabase extends Nette\Object implements IDataSource
{
	/** @var Database\Table\Selection */
	protected $selection;

	/**
	 * @param Database\Table\Selection $selection
	 */
	public function __construct(Database\Table\Selection $selection)
	{
		$this->selection = $selection;
	}

	/**
	 * @return Database\Table\Selection
	 */
	public function getSelection()
	{
		return $this->selection;
	}

	/**
	 * @param Filters\Condition $condition
	 * @param Database\Table\Selection $selection
	 */
	protected function makeWhere(Filters\Condition $condition, Database\Table\Selection $selection = NULL)
	{
		$selection = $selection === NULL
			? $this->selection
			: $selection;

		if ($condition->callback) {
			callback($condition->callback)->invokeArgs(array($condition->value, $selection));
		} else {
			call_user_func_array(array($selection, 'where'), $condition->__toArray());
		}
	}

	/********************************** inline editation helpers ************************************/

	/**
	 * Default callback for an inline editation save
	 *
	 * @param mixed $id
	 * @param array $values
	 * @param string $idCol
	 *
	 * @return bool
	 */
	public function update($id, array $values, $idCol)
	{
		return (bool) $this->getSelection()
			->where(array($idCol => $id)) //TODO: column escaping requires https://github.com/nette/nette/issues/1324
			->update($values);
	}

	/**
	 * Default callback used when an editable column has customRender
	 *
	 * @param mixed $id
	 * @param string $idCol
	 *
	 * @return Database\Table\ActiveRow
	 */
	public function getRow($id, $idCol)
	{
		return $this->getSelection()
			->where(array($idCol => $id)) //TODO: column escaping requires https://github.com/nette/nette/issues/1324
			->fetch();
	}

	/********************************** interface IDataSource ************************************/

	/**
	 * @return int
	 */
	public function getCount()
	{
		return (int) $this->selection->count('*');
	}

	/**
	 * @return array
	 */
	public function getData()
	{
		return $this->selection;
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
		$this->selection->limit($limit, $offset);
	}

	/**
	 * @param array $sorting
	 */
	public function sort(array $sorting)
	{
		foreach ($sorting as $column => $sort) {
			$this->selection->order("$column $sort");
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
		$selection = clone $this->selection;
		is_string($column) && $selection->select("DISTINCT $column");
		$selection->limit($limit);

		foreach ($conditions as $condition) {
			$this->makeWhere($condition, $selection);
		}

		$items = array();
		foreach ($selection as $row) {
			if (is_string($column)) {
				$value = (string) $row[$column];
			} elseif (is_callable($column)) {
				$value = (string) $column($row);
			} else {
				$type = gettype($column);
				throw new Exceptions\InvalidArgumentException("Column of suggestion must be string or callback, $type given.");
			}

			$items[$value] = \Nette\Templating\Helpers::escapeHtml($value);
		}

		return array_values($items);
	}
}