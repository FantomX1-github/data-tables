<?php
/**
 * Settings.php
 *
 * @copyright      More in license.md
 * @license        https://www.ipublikuj.eu
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 * @package        iPublikuj:DataTables!
 * @subpackage     Columns
 * @since          1.0.0
 *
 * @date           06.11.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Columns;

use Nette\Application\UI;

use IPub\DataTables;
use IPub\DataTables\Exceptions;

/**
 * DataTables column settings control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
abstract class Settings extends UI\Control implements ISettings
{
	/**
	 * Change the cell type created for the column - either TD cells or TH cells
	 *
	 * @see http://datatables.net/reference/option/columns.cellType
	 *
	 * @var string
	 */
	private $cellType = 'td';

	/**
	 * Class to assign to each cell in the column
	 *
	 * @see http://datatables.net/reference/option/columns.className
	 *
	 * @var string|NULL
	 */
	private $className = NULL;

	/**
	 * Set default, static, content for a column
	 *
	 * @see http://datatables.net/reference/option/columns.defaultContent
	 *
	 * @var string|NULL
	 */
	private $defaultContent = NULL;

	/**
	 * Enable or disable ordering on this column
	 *
	 * @see http://datatables.net/reference/option/columns.orderable
	 *
	 * @var bool
	 */
	private $sortable = TRUE;

	/**
	 * Define multiple column ordering as the default order for a column
	 *
	 * @see http://datatables.net/reference/option/columns.orderData
	 *
	 * @var array
	 */
	private $orderData = [];

	/**
	 * Live DOM sorting type assignment
	 *
	 * @see http://datatables.net/reference/option/columns.orderDataType
	 *
	 * @var string|NULL
	 */
	private $orderDataType = NULL;

	/**
	 * Order direction application sequence
	 *
	 * @see http://datatables.net/reference/option/columns.orderSequence
	 *
	 * @var array
	 */
	private $orderSequence = ['asc', 'desc'];

	/**
	 * Enable or disable filtering on the data in this column
	 *
	 * @see http://datatables.net/reference/option/columns.searchable
	 *
	 * @var bool
	 */
	private $searchable = TRUE;

	/**
	 * Set the column type - used for filtering and sorting string processing
	 *
	 * @see http://datatables.net/reference/option/columns.type
	 *
	 * @var string|NULL
	 */
	private $type = NULL;

	/**
	 * Enable or disable the display of this column
	 *
	 * @see http://datatables.net/reference/option/columns.visible
	 *
	 * @var bool
	 */
	private $visible = TRUE;

	/**
	 * Column width assignment
	 *
	 * @see http://datatables.net/reference/option/columns.width
	 *
	 * @var string|NULL
	 */
	private $width = NULL;

	/**
	 * {@inheritdoc}
	 */
	public function setCellType(string $cellType = 'td')
	{
		$this->cellType = $cellType;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCellType() : string
	{
		return $this->cellType;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setClassName(string $className)
	{
		$this->className = $className;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getClassName()
	{
		return $this->className;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setDefaultContent(string $defaultContent)
	{
		$this->defaultContent = $defaultContent;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDefaultContent()
	{
		return $this->defaultContent;
	}

	/**
	 * {@inheritdoc}
	 */
	public function enableSortable()
	{
		$this->sortable = TRUE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function disableSortable()
	{
		$this->sortable = FALSE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isSortable() : bool
	{
		return $this->sortable;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setOrderData(array $orderData)
	{
		$this->orderData = $orderData;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOrderData() : array
	{
		return $this->orderData;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setOrderDataType(string $type)
	{
		if (!in_array($type, ['dom-text', 'dom-select', 'dom-checkbox'], TRUE)) {
			throw new Exceptions\InvalidArgumentException('Invalid column order data type given.');
		}

		$this->orderDataType = $type;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOrderDataType()
	{
		return $this->orderDataType;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setOrderSequence(array $orderSequence)
	{
		$this->orderSequence = $orderSequence;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getOrderSequence() : array
	{
		return $this->orderSequence;
	}

	/**
	 * {@inheritdoc}
	 */
	public function enableSearchable()
	{
		$this->searchable = TRUE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function disableSearchable()
	{
		$this->searchable = FALSE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isSearchable() : bool
	{
		return $this->searchable;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setType(string $type)
	{
		if (!in_array($type, ['date', 'num', 'num-fmt', 'html-num', 'html-num-fmt', 'string'])) {
			throw new Exceptions\InvalidArgumentException('Invalid column type given.');
		}

		$this->type = $type;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getType()
	{
		return $this->type;
	}

	/**
	 * {@inheritdoc}
	 */
	public function enableVisibility()
	{
		$this->visible = TRUE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function disableVisibility()
	{
		$this->visible = FALSE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function isVisible() : bool
	{
		return $this->visible;
	}

	/**
	 * {@inheritdoc}
	 */
	public function setWidth(string $width)
	{
		$this->width = $width;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getWidth()
	{
		return $this->width;
	}
}
