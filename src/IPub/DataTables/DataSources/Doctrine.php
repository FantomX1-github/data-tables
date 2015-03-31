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
use Nette\Utils;

use Doctrine\ORM;
use Doctrine\ORM\Tools;

use Kdyby;

use IPub\DataTables;
use IPub\DataTables\Exceptions;
use IPub\DataTables\Filters;
use Tracy\Debugger;

/**
 * Doctrine data source
 *
 * @author      Martin Jantosovic <martin.jantosovic@freya.sk>
 * @author      Petr Bugyík
 *
 * @property-read \Doctrine\ORM\QueryBuilder $qb
 * @property-read array $filterMapping
 * @property-read array $sortMapping
 * @property-read int $count
 * @property-read array $data
 */
class Doctrine extends Nette\Object implements IDataSource
{
	/**
	 * @var Kdyby\Doctrine\QueryObject
	 */
	protected $qb;

	/**
	 * @var Kdyby\Doctrine\EntityRepository
	 */
	protected $repository;

	/**
	 * @var Kdyby\Doctrine\ResultSet|array
	 */
	protected $result;

	/** @var array Map column to the query builder */
	protected $filterMapping;

	/** @var array Map column to the query builder */
	protected $sortMapping;

	/** @var array */
	protected $rand;

	/**
	 * If $sortMapping is not set and $filterMapping is set,
	 * $filterMapping will be used also as $sortMapping.
	 *
	 * @param Kdyby\Doctrine\QueryObject $qb
	 * @param Kdyby\Doctrine\EntityRepository $repository
	 * @param array $filterMapping Maps columns to the DQL columns
	 * @param array $sortMapping Maps columns to the DQL columns
	 */
	public function __construct(Kdyby\Doctrine\QueryObject $qb, Kdyby\Doctrine\EntityRepository $repository, $filterMapping = NULL, $sortMapping = NULL)
	{
		$this->qb = $qb;
		$this->repository = $repository;

		$this->filterMapping = $filterMapping;
		$this->sortMapping = $sortMapping;

		if (!$this->sortMapping && $this->filterMapping) {
			$this->sortMapping = $this->filterMapping;
		}
	}

	/**
	 * @return \Doctrine\ORM\Query
	 */
	public function getQuery()
	{
		return $this->qb->getQuery();
	}

	/**
	 * @return array|NULL
	 */
	public function getFilterMapping()
	{
		return $this->filterMapping;
	}

	/**
	 * @return array|NULL
	 */
	public function getSortMapping()
	{
		return $this->sortMapping;
	}

	/**
	 * @param Filters\Condition $condition
	 *
	 * @return mixed
	 */
	protected function makeWhere(Filters\Condition $condition)
	{
		$this->result = NULL;

		$this->qb->addFilter(function (ORM\QueryBuilder $qb) use($condition) {
			if ($condition->callback) {
				return callback($condition->callback)->invokeArgs(array($condition->value, $qb));
			}

			$columns = $condition->column;
			foreach ($columns as $key => $column) {
				if (!Filters\Condition::isOperator($column)) {
					$columns[$key] = (isset($this->filterMapping[$column])
						? $this->filterMapping[$column]
						: (Utils\Strings::contains($column, ".") ? $column : 'e.' . $column));
				}
			}

			$condition->setColumn($columns);
			list($where) = $condition->__toArray(NULL, NULL, FALSE);

			$rand = $this->getRand();
			$where = preg_replace_callback('/\?/', function() use ($rand) {
				static $i = -1;
				$i++;
				return ":$rand{$i}";
			}, $where);

			$qb->andWhere($where);

			foreach ($condition->getValueForColumn() as $i => $val) {
				$qb->setParameter("$rand{$i}", $val);
			}
		});
	}

	/**
	 * @return string
	 */
	protected function getRand()
	{
		do {
			$rand = Utils\Random::generate(4, 'a-z');
		} while (isset($this->rand[$rand]));

		$this->rand[$rand] = $rand;
		return $rand;
	}

	/*********************************** interface IDataSource ************************************/

	/**
	 * @return int
	 */
	public function getCount()
	{
		return $this->getResults()->count();
	}

	/**
	 * It is possible to use query builder with additional columns.
	 * In this case, only item at index [0] is returned, because
	 * it should be an entity object
	 *
	 * @return array
	 */
	public function getData()
	{
		$data = array();

		foreach ($this->getResults() as $result) {
			// Return only entity itself
			$data[] = is_array($result)
				? $result[0]
				: $result;
		}

		return $data;
	}

	/**
	 * Get only one selected row by identifier
	 *
	 * @param int $id
	 *
	 * @return mixed
	 *
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getRow($id)
	{
		return $this->repository->findOneBy(['id' => (int) $id]);
	}

	/**
	 * Sets filter
	 *
	 * @param array $conditions
	 */
	public function filter(array $conditions)
	{
		foreach ($conditions as $condition) {
			$this->makeWhere($condition);
		}
	}

	/**
	 * Sets offset and limit
	 *
	 * @param int $offset
	 * @param int $limit
	 */
	public function limit($offset, $limit)
	{
		$this->getResults()->applyPaging($offset, $limit);
	}

	/**
	 * Sets sorting
	 *
	 * @param array $sorting
	 */
	public function sort(array $sorting)
	{
		$sortColumns = [];

		foreach ($sorting as $key => $value) {
			$column = isset($this->sortMapping[$key])
				? $this->sortMapping[$key]
				: 'e.' . $key;

			$sortColumns[] = $column .' '. $value;
		}

		$this->getResults()->applySorting($sortColumns);
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
		$qb = clone $this->qb;
		$qb->setMaxResults($limit);

		if (is_string($column)) {
			$mapping = isset($this->filterMapping[$column])
				? $this->filterMapping[$column]
				: $qb->getRootAlias() . '.' . $column;

			$qb->select($mapping)->distinct();
		}

		foreach ($conditions as $condition) {
			$this->makeWhere($condition, $qb);
		}

		$items = array();
		$data = $qb->getQuery()->getScalarResult();
		foreach ($data as $row) {
			if (is_string($column)) {
				$value = (string) current($row);
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

	/**
	 * @return Kdyby\Doctrine\ResultSet
	 */
	private function getResults()
	{
		$this->result = $this->result ?:$this->qb->fetch($this->repository);

		return $this->result;
	}
}