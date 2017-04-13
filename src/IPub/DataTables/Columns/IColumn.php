<?php
/**
 * IColumn.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Columns
 * @since          1.0.0
 *
 * @date           21.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Columns;

use Nette\Forms;
use Nette\Utils;

use IPub;
use IPub\DataTables;
use IPub\DataTables\Filters;

/**
 * Column control interface
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IColumn extends ISettings
{
	/**
	 * Container name in grid
	 */
	const ID = 'columns';

	const ORDER_ASC = 'asc';
	const ORDER_DESC = 'desc';

	/**
	 * Define columns types
	 */
	const TYPE_ACTION = 'Action';
	const TYPE_DATE = 'Date';
	const TYPE_IMAGE = 'Image';
	const TYPE_NUMBER = 'Number';
	const TYPE_STATUS = 'Status';
	const TYPE_TEXT = 'Text';
	const TYPE_EMAIL = 'Email';
	const TYPE_LINK = 'Link';

	/**
	 * @return string
	 */
	function getName();

	/**
	 * @param callback|string $label
	 *
	 * @return void
	 */
	function setLabel($label);

	/**
	 * @return string
	 */
	function getLabel() : string;

	/**
	 * @param string $column
	 *
	 * @return void
	 */
	function setColumn(string $column);

	/**
	 * @return string
	 */
	function getColumn() : string;

	/**
	 * @param callable $renderer
	 *
	 * @return void
	 */
	function setRenderer(callable $renderer);

	/**
	 * @return callable|NULL
	 */
	function getRenderer();

	/**
	 * @param mixed $row
	 *
	 * @return void
	 */
	function render($row);

	/**
	 * @param callable $renderer
	 *
	 * @return void
	 */
	function setCellRenderer(callable $renderer);

	/**
	 * @param mixed $row
	 *
	 * @return array
	 */
	function renderCell($row) : array;

	/**
	 * @return bool
	 */
	function hasCellRenderer() : bool;

	/**
	 * @return bool
	 */
	function hasFilter() : bool;

	/**
	 * @return Filters\IFilter|NULL
	 */
	function getFilter();

	/**
	 * @param string $label
	 *
	 * @return Filters\Text
	 */
	function addFilterText(string $label) : Filters\Text;

	/**
	 * @param string $label
	 *
	 * @return Filters\Number
	 */
	function addFilterNumber(string $label) : Filters\Number;

	/**
	 * @param string $label
	 *
	 * @return Filters\Date
	 */
	function addFilterDate(string $label) : Filters\Date;

	/**
	 * @param string $label
	 *
	 * @return Filters\DateRange
	 */
	function addFilterDateRange(string $label) : Filters\DateRange;

	/**
	 * @param string $label
	 * @param array $items
	 *
	 * @return Filters\Select
	 */
	function addFilterSelect(string $label, array $items = NULL) : Filters\Select;

	/**
	 * @param string $label
	 *
	 * @return Filters\Check
	 */
	function addFilterCheck(string $label) : Filters\Check;

	/**
	 * @param Forms\IControl $formControl
	 *
	 * @return Filters\Custom
	 */
	function addFilterCustom(Forms\IControl $formControl) : Filters\Custom;

	/**
	 * @param bool $asTextarea
	 * @param int|NULL $cols
	 * @param int|NULL $rows
	 *
	 * @return void
	 */
	function setTextEditable(bool $asTextarea = FALSE, int $cols = NULL, int $rows = NULL);

	/**
	 * @param array $values
	 * @param string|NULL $prompt
	 * @param bool $multiselect
	 *
	 * @return void
	 */
	function setSelectEditable(array $values, string $prompt = NULL, bool $multiselect = FALSE);

	/**
	 * @return void
	 */
	function setBooleanEditable();

	/**
	 * @return void
	 */
	function setDateEditable();

	/**
	 * @return bool
	 */
	function isEditable() : bool;

	/**
	 * @return Utils\Html
	 */
	function getHeaderPrototype() : Utils\Html;

	/**
	 * @param mixed $row
	 *
	 * @return Utils\Html
	 */
	function getCellPrototype($row) : Utils\Html;
}
