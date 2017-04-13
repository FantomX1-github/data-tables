<?php
/**
 * Model.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     DataSources
 * @since          1.0.0
 *
 * @date           23.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\DataSources;

use Doctrine\ORM;

use Nette;

use IPub\DataTables\Exceptions;

/**
 * @method void onBeforeFilter(IDataSource $source)
 * @method void onAfterFilter(IDataSource $source)
 * @method void onAfterPaginated(IDataSource $source)
 */
class Model implements IModel
{
	/**
	 * Implement nette smart magic
	 */
	use Nette\SmartObject;

	/**
	 * @var callable[]
	 */
	public $onBeforeFilter = [];

	/**
	 * @var callable[]
	 */
	public $onAfterFilter = [];

	/**
	 * @var callable[]
	 */
	public $onAfterPaginated = [];

	/**
	 * @var IDataSource
	 */
	private $dataSource;

	/**
	 * @param mixed $source
	 * @param string $primaryKey
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function __construct($source, string $primaryKey)
	{
		if ($source instanceof ORM\QueryBuilder) {
			$dataSource = new Doctrine($source, $primaryKey);

		} elseif (is_array($source)) {
			$dataSource = new ArraySource($source);

		} elseif ($source instanceof IDataSource) {
			$dataSource = $source;

		} else {
			throw new Exceptions\InvalidArgumentException('Model must implement \IPub\DataTables\DataSources\IDataSource.');
		}

		$this->dataSource = $dataSource;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDataSource() : IDataSource
	{
		return $this->dataSource;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getPrimaryKey() : string
	{
		return $this->dataSource->getPrimaryKey();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCount() : int
	{
		return $this->dataSource->getCount();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRows() : array
	{
		return $this->dataSource->getRows();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRow($identifier)
	{
		return $this->dataSource->getRow($identifier);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getColumnValue($row, string $column) : array
	{
		return $this->dataSource->getColumnValue($row, $column);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getRowIdentifier($row)
	{
		return $this->dataSource->getRowIdentifier($row);
	}

	/**
	 * {@inheritdoc}
	 */
	public function limit(int $limitStart, int $length)
	{
		$this->dataSource->limit($limitStart, $length);
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter(array $conditions)
	{
		$this->dataSource->filter($conditions);
	}

	/**
	 * {@inheritdoc}
	 */
	public function sort(array $sorting)
	{
		$this->dataSource->sort($sorting);
	}
}
