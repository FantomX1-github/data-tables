<?php
/**
 * IDataSource.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	DataSources
 * @since		5.0
 *
 * @date		18.10.14
 */

namespace IPub\DataTables\DataSources;

/**
 * The interface defines methods that must be implemented by each data source
 *
 * @author      Petr Bugyík
 */
interface IDataSource
{
	/**
	 * @return int
	 */
	public function getCount();

	/**
	 * @return array
	 */
	public function getData();

	/**
	 * @param array $condition
	 *
	 * @return void
	 */
	public function filter(array $condition);

	/**
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return void
	 */
	public function limit($offset, $limit);

	/**
	 * @param array $sorting
	 *
	 * @return void
	 */
	public function sort(array $sorting);

	/**
	 * @param mixed $column
	 * @param array $conditions
	 * @param int $limit
	 *
	 * @return array
	 */
	public function suggest($column, array $conditions, $limit);
}