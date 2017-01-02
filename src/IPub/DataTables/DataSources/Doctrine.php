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

use IPub\DataTables;
use IPub\DataTables\Exceptions;
use IPub\DataTables\Filters;
use Ramsey\Uuid\Uuid;

/**
 * Doctrine data source
 *
 * @author      Martin Jantosovic <martin.jantosovic@freya.sk>
 * @author      Petr Bugyík
 *
 * @property-read ORM\QueryBuilder $qb
 * @property-read array $filterMapping
 * @property-read array $sortMapping
 * @property-read int $count
 * @property-read array $data
 */
class Doctrine extends Nette\Object implements IDataSource
{
	/** @var \Doctrine\ORM\QueryBuilder */
	protected $qb;

	/** @var array Map column to the query builder */
	protected $filterMapping;

	/** @var array Map column to the query builder */
	protected $sortMapping;

	/** @var bool use OutputWalker in Doctrine Paginator */
	protected $useOutputWalkers;

	/** @var bool fetch join collection in Doctrine Paginator */
	protected $fetchJoinCollection = TRUE;

	/** @var array */
	protected $rand;

	/**
	 * If $sortMapping is not set and $filterMapping is set,
	 * $filterMapping will be used also as $sortMapping.
	 *
	 * @param ORM\QueryBuilder $qb
	 * @param array $filterMapping Maps columns to the DQL columns
	 * @param array $sortMapping Maps columns to the DQL columns
	 */
	public function __construct(ORM\QueryBuilder $qb, $filterMapping = NULL, $sortMapping = NULL)
	{
		$this->qb = $qb;
		$this->filterMapping = $filterMapping;
		$this->sortMapping = $sortMapping;

		if (!$this->sortMapping && $this->filterMapping) {
			$this->sortMapping = $this->filterMapping;
		}
	}

	/**
	 * @param bool $useOutputWalkers
	 *
	 * @return DataTables\DataSources\Doctrine
	 */
	public function setUseOutputWalkers($useOutputWalkers)
	{
		$this->useOutputWalkers = $useOutputWalkers;
		return $this;
	}

	/**
	 * @param bool $fetchJoinCollection
	 *
	 * @return DataTables\DataSources\Doctrine
	 */
	public function setFetchJoinCollection($fetchJoinCollection)
	{
		$this->fetchJoinCollection = $fetchJoinCollection;
		return $this;
	}

	/**
	 * @return \Doctrine\ORM\Query
	 */
	public function getQuery()
	{
		return $this->qb->getQuery();
	}

	/**
	 * @return \Doctrine\ORM\QueryBuilder
	 */
	public function getQb()
	{
		return $this->qb;
	}

	/**
	 * @param array|NULL $filterMapping Maps columns to the DQL columns
	 */
	public function setFilterMapping(array $filterMapping = NULL)
	{
		$this->filterMapping = $filterMapping;
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
	 * @param \Doctrine\ORM\QueryBuilder $qb
	 */
	protected function makeWhere(Filters\Condition $condition, \Doctrine\ORM\QueryBuilder $qb = NULL)
	{
		$qb = $qb === NULL
			? $this->qb
			: $qb;

		if ($condition->callback) {
			return callback($condition->callback)->invokeArgs(array($condition->value, $qb));
		}

		$columns = $condition->column;
		foreach ($columns as $key => $column) {
			if (!Filters\Condition::isOperator($column)) {
				$columns[$key] = (isset($this->filterMapping[$column])
					? $this->filterMapping[$column]
					: (Utils\Strings::contains($column, ".") ? $column : $this->qb->getRootAlias() . '.' . $column));
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
		$paginator = new Tools\Pagination\Paginator($this->getQuery(), $this->fetchJoinCollection);
		$paginator->setUseOutputWalkers($this->useOutputWalkers);

		return $paginator->count();
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
		// Paginator is better if the query uses ManyToMany associations
		$usePaginator = $this->qb->getMaxResults() !== NULL || $this->qb->getFirstResult() !== NULL;
		$data = [];

		if ($usePaginator) {
			$paginator = new Tools\Pagination\Paginator($this->getQuery(), FALSE);

			// Convert paginator to the array
			foreach ($paginator as $result) {
				// Return only entity itself
				$data[] = is_array($result)
					? $result[0]
					: $result;
			}

		} else {
			foreach ($this->qb->getQuery()->getResult($this->qb->getQuery()->getHydrationMode()) as $result) {
				// Return only entity itself
				$data[] = is_array($result)
					? $result[0]
					: $result;
			}
		}

		return $data;
	}

	/**
	 * Get only one selected row by identifier
	 *
	 * @param mixed $id
	 *
	 * @return mixed
	 *
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function getRow($id)
	{
		$id = Uuid::fromString($id);
		if ($this->qb->getParameters()) {
			return $this->qb
				->andWhere($this->qb->expr()->eq($this->qb->getRootAlias(), ':rowId'))
				->setParameter('rowId', $id->getBytes())
				->getQuery()
					->getOneOrNullResult();

		} else {
			return $this->qb
				->where($this->qb->expr()->eq($this->qb->getRootAlias(), ':rowId'))
				->setParameter('rowId', $id->getBytes())
				->getQuery()
					->getOneOrNullResult();
		}
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
		$this->qb->setFirstResult($offset)
			->setMaxResults($limit);
	}

	/**
	 * Sets sorting
	 *
	 * @param array $sorting
	 */
	public function sort(array $sorting)
	{
		foreach ($sorting as $key => $value) {
			$column = isset($this->sortMapping[$key])
				? $this->sortMapping[$key]
				: $this->qb->getRootAlias() . '.' . $key;

			$this->qb->addOrderBy($column, $value);
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
}
