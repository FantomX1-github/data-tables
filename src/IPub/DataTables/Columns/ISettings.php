<?php
/**
 * ISettings.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Columns
 * @since          1.0.0
 *
 * @date           11.04.17
 */

declare(strict_types=1);

namespace IPub\DataTables\Columns;

/**
 * DataTables column settings control interface
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface ISettings
{
	/**
	 * @param string $cellType
	 *
	 * @return void
	 */
	function setCellType(string $cellType = 'td');

	/**
	 * @return string
	 */
	function getCellType() : string;

	/**
	 * @param string $className
	 *
	 * @return void
	 */
	function setClassName(string $className);

	/**
	 * @return string|NULL
	 */
	function getClassName();

	/**
	 * @param string $defaultContent
	 *
	 * @return void
	 */
	function setDefaultContent(string $defaultContent);

	/**
	 * @return string|NULL
	 */
	function getDefaultContent();

	/**
	 * @return void
	 */
	function enableSortable();

	/**
	 * @return void
	 */
	function disableSortable();

	/**
	 * @return bool
	 */
	function isSortable() : bool;

	/**
	 * @param array $orderData
	 *
	 * @return void
	 */
	function setOrderData(array $orderData);

	/**
	 * @return array
	 */
	function getOrderData() : array;

	/**
	 * @param string $type
	 *
	 * @return void
	 */
	function setOrderDataType(string $type);

	/**
	 * @return string
	 */
	function getOrderDataType();

	/**
	 * @param array $orderSequence
	 *
	 * @return void
	 */
	function setOrderSequence(array $orderSequence);

	/**
	 * @return array
	 */
	function getOrderSequence() : array;

	/**
	 * @return void
	 */
	function enableSearchable();

	/**
	 * @return void
	 */
	function disableSearchable();

	/**
	 * @return bool
	 */
	function isSearchable() : bool;

	/**
	 * @param string $type
	 *
	 * @return void
	 */
	function setType(string $type);

	/**
	 * @return string|NULL
	 */
	function getType();

	/**
	 * @return void
	 */
	function enableVisibility();

	/**
	 * @return void
	 */
	function disableVisibility();

	/**
	 * @return bool
	 */
	function isVisible() : bool;

	/**
	 * @param string $width
	 *
	 * @return void
	 */
	function setWidth(string $width);

	/**
	 * @return string|NULL
	 */
	function getWidth();
}
