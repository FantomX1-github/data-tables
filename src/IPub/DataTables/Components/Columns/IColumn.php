<?php
/**
 * IColumn.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Columns
 * @since		5.0
 *
 * @date		21.10.14
 */

namespace IPub\DataTables\Components\Columns;

use Nette;
use Nette\Utils;

use IPub;
use IPub\DataTables;
use IPub\DataTables\Components;
use IPub\DataTables\Exceptions;
use IPub\DataTables\Filters;

interface IColumn
{
	/**
	 * Components group ID in datagrid
	 */
	const ID = 'columns';

	const ORDER_ASC		= 'asc';
	const ORDER_DESC	= 'desc';

	/**
	 * Define columns types
	 */
	const TYPE_ACTION	= 'Action';
	const TYPE_DATE		= 'Date';
	const TYPE_IMAGE	= 'Image';
	const TYPE_NUMBER	= 'Number';
	const TYPE_STATUS	= 'Status';
	const TYPE_TEXT		= 'Text';
	const TYPE_EMAIL	= 'Email';
	const TYPE_LINK		= 'Link';

	/**
	 * @param callback|string $label
	 *
	 * @return $this
	 */
	public function setLabel($label);

	/**
	 * @return string
	 */
	public function getLabel();

	/**
	 * @param string $column
	 *
	 * @return $this
	 */
	public function setColumn($column);

	/**
	 * @return string
	 */
	public function getColumn();

	/**
	 * @param callback|string $renderer
	 *
	 * @return $this
	 */
	public function setRenderer($renderer);

	/**
	 * @param array $row
	 */
	public function render($row);

	/**
	 * @param callback|string $renderer
	 *
	 * @return $this
	 */
	public function setCellRenderer($renderer);

	/**
	 * @param array $row
	 *
	 * @return string
	 */
	public function getCellRenderer($row);

	/**
	 * @return bool
	 */
	public function hasCellRenderer();

	/**
	 * @return bool
	 */
	public function hasFilter();

	/**
	 * @return Filters\IFilter
	 */
	public function getFilter();

	/**
	 * @param string $label
	 *
	 * @return Filters\Text
	 */
	public function addFilterText($label);

	/**
	 * @param string $label
	 *
	 * @return Filters\Number
	 */
	public function addFilterNumber($label);

	/**
	 * @param string $label
	 *
	 * @return Filters\Date
	 */
	public function addFilterDate($label);

	/**
	 * @param string $label
	 *
	 * @return Filters\DateRange
	 */
	public function addFilterDateRange($label);

	/**
	 * @param string $label
	 * @param array $items
	 *
	 * @return Filters\Select
	 */
	public function addFilterSelect($label, array $items = NULL);

	/**
	 * @param string $label
	 *
	 * @return Filters\Check
	 */
	public function addFilterCheck($label);

	/**
	 * @param Nette\Forms\IControl $formControl
	 *
	 * @return Filters\Custom
	 */
	public function addFilterCustom(Nette\Forms\IControl $formControl);

	/**
	 * @param bool $textarea
	 * @param null|int $cols
	 * @param null|int $rows
	 *
	 * @return $this
	 *
	 * @throws Exceptions\DuplicateEditableColumnException
	 */
	public function setTextEditable($textarea = FALSE, $cols = NULL, $rows = NULL);

	/**
	 * @param array $values
	 * @param string|null $prompt
	 *
	 * @return $this
	 *
	 * @throws Exceptions\DuplicateEditableColumnException
	 */
	public function setSelectEditable(array $values, $prompt = NULL);

	/**
	 * @return $this
	 *
	 * @throws Exceptions\DuplicateEditableColumnException
	 */
	public function setBooleanEditable();

	/**
	 * @return $this
	 *
	 * @throws Exceptions\DuplicateEditableColumnException
	 */
	public function setDateEditable();

	/**
	 * @return bool
	 */
	public function isEditable();

	/**
	 * @param Components\Control $parent
	 */
	public function setGrid(Components\Control $parent);

	/**
	 * @return Utils\Html
	 */
	public function getHeaderPrototype();

	/**
	 * @param mixed $data
	 *
	 * @return Utils\Html
	 */
	public function getCellPrototype($data);

	/**
	 * DataTables settings part
	 */

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setName($name);

	/**
	 * @param string $cellType
	 *
	 * @return $this
	 */
	public function setCellType($cellType = 'td');

	/**
	 * @return string
	 */
	public function getCellType();

	/**
	 * @param string $className
	 *
	 * @return $this
	 */
	public function setClassName($className);

	/**
	 * @return string
	 */
	public function getClassName();

	/**
	 * @param string $defaultContent
	 *
	 * @return $this
	 */
	public function setDefaultContent($defaultContent);

	/**
	 * @return string
	 */
	public function getDefaultContent();

	/**
	 * @return $this
	 */
	public function enableSortable();

	/**
	 * @return $this
	 */
	public function disableSortable();

	/**
	 * @return bool
	 */
	public function isSortable();

	/**
	 * @param array $orderData
	 *
	 * @return $this
	 */
	public function setOrderData(array $orderData);

	/**
	 * @return array
	 */
	public function getOrderData();

	/**
	 * @param string $type
	 *
	 * @return $this
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function setOrderDataType($type);

	/**
	 * @return string
	 */
	public function getOrderDataType();

	/**
	 * @param array $orderSequence
	 *
	 * @return $this
	 */
	public function setOrderSequence(array $orderSequence);

	/**
	 * @return array
	 */
	public function getOrderSequence();

	/**
	 * @return $this
	 */
	public function enableSearchable();

	/**
	 * @return $this
	 */
	public function disableSearchable();

	/**
	 * @return bool
	 */
	public function isSearchable();

	/**
	 * @param string $type
	 *
	 * @return $this
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function setType($type);

	/**
	 * @return string
	 */
	public function getType();

	/**
	 * @return $this
	 */
	public function enableVisibility();

	/**
	 * @return $this
	 */
	public function disableVisibility();

	/**
	 * @return bool
	 */
	public function isVisible();

	/**
	 * @param string $width
	 *
	 * @return $this
	 */
	public function setWidth($width);

	/**
	 * @return string|null
	 */
	public function getWidth();
}