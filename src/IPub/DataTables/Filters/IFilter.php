<?php
/**
 * Filter.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Filters
 * @since		5.0
 *
 * @date		11.11.14
 */

namespace IPub\DataTables\Filters;

use Nette;
use Nette\Application\UI;
use Nette\Utils;

use IPub\DataTables;
use IPub\DataTables\Components;
use IPub\DataTables\Exceptions;

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
	 * Map to database column
	 *
	 * @param string $column
	 * @param string $operator
	 *
	 * @return $this
	 *
	 * @throws \InvalidArgumentException
	 */
	public function setColumn($column, $operator = Condition::OPERATOR_OR);

	/**
	 * @return array
	 */
	public function getColumn();

	/**
	 * Sets custom condition
	 *
	 * @param $condition
	 *
	 * @return $this
	 */
	public function setCondition($condition);

	/**
	 * @return string
	 */
	public function getCondition();

	/**
	 * Sets custom "sql" where
	 *
	 * @param callable $callback function($value, $source) {}
	 *
	 * @return $this
	 */
	public function setWhere($callback);

	/**
	 * Sets custom format value
	 *
	 * @param string $format for example: "%%value%"
	 *
	 * @return $this
	 */
	public function setFormatValue($format);

	/**
	 * Sets default value
	 *
	 * @param string $value
	 *
	 * @return $this
	 */
	public function setDefaultValue($value);

	/**
	 * Value representation in URI
	 *
	 * @param string $value
	 *
	 * @return string
	 */
	public function changeValue($value);

	/**
	 * @return Nette\Forms\Controls\BaseControl
	 */
	public function getControl();

	/**
	 * Returns wrapper prototype (<th> html tag)
	 *
	 * @return Utils\Html
	 */
	public function getWrapperPrototype();

	/**
	 * @return Nette\Application\UI\Form
	 */
	public function getForm();
}