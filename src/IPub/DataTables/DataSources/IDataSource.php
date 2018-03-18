<?php
/**
 * IDataSource.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     DataSources
 * @since          1.0.0
 *
 * @date           18.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\DataSources;

use IPub\DataTables\Filters;

/**
 * Data source interface
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     DataSources
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IDataSource
{
	/**
	 * @return string
	 */
	function getPrimaryKey() : string;

	/**
	 * @return int
	 */
	function getCount() : int;

	/**
	 * @return array
	 */
	function getRows() : array;

	/**
	 * @param mixed $identifier
	 *
	 * @return mixed
	 */
	function getRow($identifier);

	/**
	 * @param mixed $row
	 * @param string $column
	 *
	 * @return mixed
	 */
	function getColumnValue($row, string $column);

	/**
	 * @param mixed $row
	 *
	 * @return mixed
	 */
	function getRowIdentifier($row);

	/**
	 * @param int $offset
	 * @param int $limit
	 *
	 * @return void
	 */
	function limit(int $offset, int $limit);

	/**
	 * @param Filters\Condition[] $conditions
	 *
	 * @return void
	 */
	function filter(array $conditions);

	/**
	 * @param array $sorting
	 *
	 * @return void
	 */
	function sort(array $sorting);

	/**
	 * @param mixed $column
	 * @param array $conditions
	 * @param int $limit
	 *
	 * @return array
	 */
	function suggest($column, array $conditions, $limit);
}
