<?php
/**
 * IFilter.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 * @since          1.0.0
 *
 * @date           11.11.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Filters;

use Nette\Forms;
use Nette\Utils;

/**
 * DataTables column filter control interface
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Filters
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
interface IFilter
{
	/**
	 * Components group ID in datagrid
	 */
	const ID = 'filters';

	const VALUE_IDENTIFIER = '%value';

	const RENDER_INNER = 'inner';
	const RENDER_OUTER = 'outer';

	/**
	 * @return string
	 */
	function getLabel() : string;

	/**
	 * Map to database column
	 *
	 * @param string $column
	 * @param string $operator
	 *
	 * @return void
	 */
	function setColumn(string $column, string $operator = Condition::OPERATOR_OR);

	/**
	 * @return array
	 */
	function getColumn() : array;

	/**
	 * Sets custom condition
	 *
	 * @param string $condition
	 *
	 * @return void
	 */
	function setCondition(string $condition);

	/**
	 * @return string
	 */
	function getCondition() : string;

	/**
	 * Sets custom "sql" where
	 *
	 * @param callable $callback function($value, $source) {}
	 *
	 * @return void
	 */
	function setWhere(callable $callback);

	/**
	 * Sets custom format value
	 *
	 * @param string $format for example: "%%value%"
	 *
	 * @return void
	 */
	function setFormatValue(string $format);

	/**
	 * Sets default value
	 *
	 * @param string $value
	 *
	 * @return void
	 */
	function setDefaultValue(string $value);

	/**
	 * Value representation in URI
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	function changeValue(string $value);

	/**
	 * @return Forms\Controls\BaseControl
	 */
	function getControl() : Forms\Controls\BaseControl;

	/**
	 * Returns wrapper prototype (<th> html tag)
	 *
	 * @return Utils\Html
	 */
	function getWrapperPrototype() : Utils\Html;
}
