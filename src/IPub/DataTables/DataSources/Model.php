<?php
/**
 * Model.php
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

use IPub;
use IPub\DataTables;
use IPub\DataTables\Exceptions;

class Model extends Nette\Object
{
	/**
	 * @var array
	 */
	public $callbacks = [];

	/**
	 * @var IDataSource
	 */
	protected $dataSource;

	/**
	 * @param mixed $model
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function __construct($model)
	{
		if ($model instanceof \DibiFluent) {
			$dataSource = new DibiFluent($model);

		} elseif ($model instanceof Nette\Database\Table\Selection) {
			$dataSource = new NetteDatabase($model);

		} elseif ($model instanceof \Doctrine\ORM\QueryBuilder) {
			$dataSource = new Doctrine($model);

		} elseif (is_array($model)) {
			$dataSource = new ArraySource($model);

		} elseif ($model instanceof IDataSource) {
			$dataSource = $model;

		} else {
			throw new Exceptions\InvalidArgumentException('Model must implement \IPub\DataTables\DataSources\IDataSource.');
		}

		$this->dataSource = $dataSource;
	}

	/**
	 * @return IDataSource
	 */
	public function getDataSource()
	{
		return $this->dataSource;
	}

	/**
	 * Magic call for custom calls or data source calls
	 *
	 * @param string $method
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		return isset($this->callbacks[$method])
			? callback($this->callbacks[$method])->invokeArgs(array($this->dataSource, $args))
			: call_user_func_array(array($this->dataSource, $method), $args);
	}
}