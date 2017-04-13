<?php
/**
 * IModel.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     DataSources
 * @since          1.0.0
 *
 * @date           13.04.17
 */

declare(strict_types=1);

namespace IPub\DataTables\DataSources;

use IPub\DataTables\Filters;

/**
 * Grid model interface
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     DataSources
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IModel
{
	/**
	 * @return IDataSource
	 */
	function getDataSource() : IDataSource;

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
	 * @param int $limitStart
	 * @param int $length
	 *
	 * @return void
	 */
	function limit(int $limitStart, int $length);

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
}
