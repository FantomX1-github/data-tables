<?php
/**
 * Settings.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Columns
 * @since		5.0
 *
 * @date		06.11.14
 */

namespace IPub\DataTables\Components\Columns;

use Nette;
use Nette\Application\UI;
use Nette\Utils;

use IPub;
use IPub\DataTables;
use IPub\DataTables\Components;
use IPub\DataTables\Exceptions;
use IPub\DataTables\Filters;

abstract class Settings extends Column
{
	/**
	 * Change the cell type created for the column - either TD cells or TH cells
	 *
	 * @see http://datatables.net/reference/option/columns.cellType
	 *
	 * @var string
	 */
	protected $cellType = 'td';

	/**
	 * Class to assign to each cell in the column
	 *
	 * @see http://datatables.net/reference/option/columns.className
	 *
	 * @var string
	 */
	protected $className = NULL;

	/**
	 * Set default, static, content for a column
	 *
	 * @see http://datatables.net/reference/option/columns.defaultContent
	 *
	 * @var string
	 */
	protected $defaultContent = NULL;

	/**
	 * Enable or disable ordering on this column
	 *
	 * @see http://datatables.net/reference/option/columns.orderable
	 *
	 * @var bool
	 */
	protected $sortable = TRUE;

	/**
	 * Define multiple column ordering as the default order for a column
	 *
	 * @see http://datatables.net/reference/option/columns.orderData
	 *
	 * @var array
	 */
	protected $orderData;

	/**
	 * Live DOM sorting type assignment
	 *
	 * @see http://datatables.net/reference/option/columns.orderDataType
	 *
	 * @var string
	 */
	protected $orderDataType;

	/**
	 * Order direction application sequence
	 *
	 * @see http://datatables.net/reference/option/columns.orderSequence
	 *
	 * @var array
	 */
	protected $orderSequence = ['asc', 'desc'];

	/**
	 * Enable or disable filtering on the data in this column
	 *
	 * @see http://datatables.net/reference/option/columns.searchable
	 *
	 * @var bool
	 */
	protected $searchable = TRUE;

	/**
	 * Set the column type - used for filtering and sorting string processing
	 *
	 * @see http://datatables.net/reference/option/columns.type
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Enable or disable the display of this column
	 *
	 * @see http://datatables.net/reference/option/columns.visible
	 *
	 * @var bool
	 */
	protected $visible = TRUE;

	/**
	 * Column width assignment
	 *
	 * @see http://datatables.net/reference/option/columns.width
	 *
	 * @var string
	 */
	protected $width;

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setName($name)
	{
		$this->name = (string) $name;

		return $this;
	}

	/**
	 * @param string $cellType
	 *
	 * @return $this
	 */
	public function setCellType($cellType = 'td')
	{
		$this->cellType = (string) $cellType;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCellType()
	{
		return (string) $this->cellType;
	}

	/**
	 * @param string $className
	 *
	 * @return $this
	 */
	public function setClassName($className)
	{
		$this->className = (string) $className;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return (string) $this->className;
	}

	/**
	 * @param string $defaultContent
	 *
	 * @return $this
	 */
	public function setDefaultContent($defaultContent)
	{
		$this->defaultContent = (string) $defaultContent;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultContent()
	{
		return (string) $this->defaultContent;
	}

	/**
	 * @return $this
	 */
	public function enableSortable()
	{
		$this->sortable = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableSortable()
	{
		$this->sortable = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSortable()
	{
		return $this->sortable;
	}

	/**
	 * @param array $orderData
	 *
	 * @return $this
	 */
	public function setOrderData(array $orderData)
	{
		$this->orderData = (array) $orderData;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getOrderData()
	{
		return $this->orderData;
	}

	/**
	 * @param string $type
	 *
	 * @return $this
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function setOrderDataType($type)
	{
		if (!in_array($type, ['dom-text', 'dom-select', 'dom-checkbox'])) {
			throw new Exceptions\InvalidArgumentException('Invalid column order data type given.');
		}

		$this->orderDataType = (string) $type;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getOrderDataType()
	{
		return $this->orderDataType;
	}

	/**
	 * @param array $orderSequence
	 *
	 * @return $this
	 */
	public function setOrderSequence(array $orderSequence)
	{
		$this->orderSequence = $orderSequence;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getOrderSequence()
	{
		return (array) $this->orderSequence;
	}

	/**
	 * @return $this
	 */
	public function enableSearchable()
	{
		$this->searchable = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableSearchable()
	{
		$this->searchable = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isSearchable()
	{
		return $this->searchable === TRUE;
	}

	/**
	 * @param string $type
	 *
	 * @return $this
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function setType($type)
	{
		if (!in_array($type, ['date', 'num', 'num-fmt', 'html-num', 'html-num-fmt', 'string'])) {
			throw new Exceptions\InvalidArgumentException('Invalid column type given.');
		}

		$this->type = (string) $type;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getType()
	{
		return (string) $this->type;
	}

	/**
	 * @return $this
	 */
	public function enableVisibility()
	{
		$this->visible = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableVisibility()
	{
		$this->visible = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isVisible()
	{
		return $this->visible;
	}

	/**
	 * @param string $width
	 *
	 * @return $this
	 */
	public function setWidth($width)
	{
		$this->width = $width;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getWidth()
	{
		return $this->width;
	}
}