<?php
/**
 * Doctrine.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     DataSources
 * @since          1.0.0
 *
 * @date           23.10.14
 */

namespace IPub\DataTables\DataSources;

use Nette;
use Nette\Utils;
use Nette\Utils\Strings;

use Doctrine\ORM;
use Doctrine\ORM\Tools;

use IPub\DataTables\Exceptions;
use IPub\DataTables\Filters;

use Ramsey\Uuid\Uuid;

/**
 * Doctrine data source
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     DataSources
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @method void onDataLoaded(array $result)
 */
class Doctrine implements IDataSource
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
	 * @var ORM\QueryBuilder
	 */
	private $qb;

	/**
	 * @var string
	 */
	private $primaryKey;

	/**
	 * @var string
	 */
	private $rootAlias;

	/**
	 * @var int
	 */
	private $placeholder;

	/**
	 * Map column to the query builder
	 *
	 * @var array
	 */
	private $filterMapping;

	/**
	 * Map column to the query builder
	 *
	 * @var array
	 */
	private $sortMapping;

	/**
	 * Enable or disable OutputWalker in Doctrine Paginator
	 *
	 * @var bool
	 */
	private $useOutputWalkers;

	/**
	 * Fetch join collection in Doctrine Paginator
	 *
	 * @var bool
	 */
	private $fetchJoinCollection = TRUE;

	/**
	 * @var array
	 */
	private $rand;

	/**
	 * If $sortMapping is not set and $filterMapping is set,
	 * $filterMapping will be used also as $sortMapping.
	 *
	 * @param ORM\QueryBuilder $qb
	 * @param string $primaryKey
	 * @param array $filterMapping Maps columns to the DQL columns
	 * @param array $sortMapping   Maps columns to the DQL columns
	 */
	public function __construct(ORM\QueryBuilder $qb, string $primaryKey, array $filterMapping = NULL, array $sortMapping = NULL)
	{
		$this->qb = $qb;
		$this->primaryKey = $primaryKey;
		$this->filterMapping = $filterMapping;
		$this->sortMapping = $sortMapping;

		$this->placeholder = count($qb->getParameters());

		if (!$this->sortMapping && $this->filterMapping) {
			$this->sortMapping = $this->filterMapping;
		}
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
		if ($this->usePaginator()) {
			return (new Tools\Pagination\Paginator($this->getQuery()))->count();
		}

		$qb = clone $this->qb;
		$qb->select(sprintf('COUNT(%s)', $this->checkAliases($this->primaryKey)));

		return (int) $qb->getQuery()->getSingleScalarResult();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRows() : array
	{
		if ($this->usePaginator()) {
			$iterator = (new Tools\Pagination\Paginator($this->getQuery(), FALSE))->getIterator();

			return iterator_to_array($iterator);
		}

		return $this->qb->getQuery()->getResult($this->qb->getQuery()->getHydrationMode());
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRow($id)
	{
		$id = Uuid::fromString($id);
		$id = $id->getBytes();

		$aliases = $this->qb->getRootAliases();

		if ($this->qb->getParameters()) {
			return $this->qb
				->andWhere($this->qb->expr()->eq($aliases[0], ':rowId'))
				->setParameter('rowId', $id)
				->getQuery()
				->getOneOrNullResult();
		}

		return $this->qb
			->where($this->qb->expr()->eq($aliases[0], ':rowId'))
			->setParameter('rowId', $id)
			->getQuery()
			->getOneOrNullResult();
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
		$this->qb->setFirstResult($offset)
			->setMaxResults($limit);
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter(array $conditions)
	{
		foreach ($conditions as $condition) {
			$this->makeWhere($condition);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function sort(array $sorting)
	{
		$aliases = $this->qb->getRootAliases();

		foreach ($sorting as $key => $value) {
			$column = isset($this->sortMapping[$key])
				? $this->sortMapping[$key]
				: $aliases[0] . '.' . $key;

			$this->qb->addOrderBy($column, $value);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function suggest($column, array $conditions, $limit)
	{
		$qb = clone $this->qb;
		$qb->setMaxResults($limit);

		$aliases = $this->qb->getRootAliases();

		if (is_string($column)) {
			$mapping = isset($this->filterMapping[$column])
				? $this->filterMapping[$column]
				: $aliases[0] . '.' . $column;

			$qb->select($mapping)->distinct();
		}

		foreach ($conditions as $condition) {
			$this->makeWhere($condition, $qb);
		}

		$items = [];
		$data = $qb->getQuery()->getScalarResult();

		foreach ($data as $row) {
			if (is_string($column)) {
				$value = (string) current($row);

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
	 * @param bool $useOutputWalkers
	 *
	 * @return void
	 */
	public function setUseOutputWalkers(bool $useOutputWalkers)
	{
		$this->useOutputWalkers = $useOutputWalkers;
	}

	/**
	 * @param bool $fetchJoinCollection
	 *
	 * @return void
	 */
	public function setFetchJoinCollection(bool $fetchJoinCollection)
	{
		$this->fetchJoinCollection = $fetchJoinCollection;
	}

	/**
	 * @return ORM\Query
	 */
	public function getQuery() : ORM\Query
	{
		return $this->qb->getQuery();
	}

	/**
	 * @return ORM\QueryBuilder
	 */
	public function getQb() : ORM\QueryBuilder
	{
		return $this->qb;
	}

	/**
	 * @param array $filterMapping
	 *
	 * @return void
	 */
	public function setFilterMapping(array $filterMapping = [])
	{
		$this->filterMapping = $filterMapping;
	}

	/**
	 * @return array
	 */
	public function getFilterMapping() : array
	{
		return $this->filterMapping;
	}

	/**
	 * @param array $sortMapping
	 *
	 * @return void
	 */
	public function setSortMapping(array $sortMapping = [])
	{
		$this->sortMapping = $sortMapping;
	}

	/**
	 * @return array
	 */
	public function getSortMapping() : array
	{
		return $this->sortMapping;
	}

	/**
	 * @param Filters\Condition $condition
	 * @param ORM\QueryBuilder|NULL $qb
	 *
	 * @return mixed
	 */
	private function makeWhere(Filters\Condition $condition, ORM\QueryBuilder $qb = NULL)
	{
		$qb = $qb === NULL
			? $this->qb
			: $qb;

		if ($condition->getCallback() !== NULL) {
			return callback($condition->getCallback())->invokeArgs([$condition->getValue(), $qb]);
		}

		$columns = $condition->getColumn();

		foreach ($columns as $key => $column) {
			if (!Filters\Condition::isOperator($column)) {
				$columns[$key] = (isset($this->filterMapping[$column])
					? $this->filterMapping[$column]
					: (Utils\Strings::contains($column, '.') ? $column : $this->qb->getRootAlias() . '.' . $column));
			}
		}

		$condition->setColumn($columns);

		list($where) = $condition->__toArray(NULL, NULL, FALSE);

		$rand = $this->getRand();

		$where = preg_replace_callback('/\?/', function () use ($rand) {
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
	private function getRand() : string
	{
		do {
			$rand = Utils\Random::generate(4, 'a-z');
		} while (isset($this->rand[$rand]));

		$this->rand[$rand] = $rand;

		return $rand;
	}

	/**
	 * @param string $column
	 *
	 * @return string
	 */
	private function checkAliases(string $column) : string
	{
		if (Strings::contains($column, '.')) {
			return $column;
		}

		if (!isset($this->rootAlias)) {
			$this->rootAlias = $this->qb->getRootAliases();
			$this->rootAlias = current($this->rootAlias);
		}

		return $this->rootAlias . '.' . $column;
	}

	/**
	 * @return bool
	 */
	private function usePaginator() : bool
	{
		return $this->qb->getDQLPart('join') || $this->qb->getDQLPart('groupBy');
	}
}
