<?php
/**
 * Settings.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 * @since          1.0.0
 *
 * @date           18.10.14
 */

declare(strict_types=1);

namespace IPub\DataTables\Components;

use Nette\Application\UI;
use Nette\Utils;

use IPub;
use IPub\DataTables;
use IPub\DataTables\Exceptions;

/**
 * DataTables control JavaScript configuration
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 */
abstract class Settings extends UI\Control
{
	/**
	 * Enable or disable automatic column width calculation
	 *
	 * @see http://datatables.net/reference/option/autoWidth
	 *
	 * @var bool
	 */
	private $autoWidth = FALSE;

	/**
	 * Enable or disable defer rendering of cells
	 *
	 * @see http://datatables.net/reference/option/deferRender
	 *
	 * @var bool
	 */
	private $deferRender = FALSE;

	/**
	 * Enable or disable jQuery UI markup
	 *
	 * @see http://datatables.net/reference/option/jQueryUI
	 *
	 * @var bool
	 */
	private $jQueryUI = FALSE;

	/**
	 * When pagination is enabled, this option will display an option for
	 * the end user to change number of records to be shown per page
	 *
	 * @see http://datatables.net/reference/option/lengthChange
	 *
	 * @var bool
	 */
	private $lengthChange = TRUE;

	/**
	 * Enable or disable ordering of columns
	 *
	 * @see http://datatables.net/reference/option/ordering
	 *
	 * @var bool
	 */
	private $ordering = TRUE;

	/**
	 * Enable or disable table pagination
	 *
	 * @see http://datatables.net/reference/option/paging
	 *
	 * @var bool
	 */
	private $paging = TRUE;

	/**
	 * Enable or disable the display of a 'processing' indicator when the table is being processed
	 *
	 * @see http://datatables.net/reference/option/processing
	 *
	 * @var bool
	 */
	private $processing = FALSE;

	/**
	 * Enable or disable horizontal scrolling
	 *
	 * @see http://datatables.net/reference/option/scrollX
	 *
	 * @var bool
	 */
	private $scrollX = FALSE;

	/**
	 * Disable or set vertical scrolling
	 *
	 * @see http://datatables.net/reference/option/scrollY
	 *
	 * @var string|NULL
	 */
	private $scrollY = NULL;

	/**
	 * Enable or disable the search abilities
	 *
	 * @see http://datatables.net/reference/option/searching
	 *
	 * @var bool
	 */
	private $searching = TRUE;

	/**
	 * Enable or disable server-side processing mode
	 *
	 * @see http://datatables.net/reference/option/serverSide
	 *
	 * @var bool
	 */
	private $serverSide = FALSE;

	/**
	 * Enable or disable state saving
	 *
	 * @see http://datatables.net/reference/option/stateSave
	 *
	 * @var bool
	 */
	private $stateSave = FALSE;

	/**
	 * Delay the loading of server-side data until second draw
	 *
	 * @see http://datatables.net/reference/option/deferLoading
	 *
	 * @var int|array
	 */
	private $deferLoading = NULL;

	/**
	 * Enable or disable destroying any existing table matching the selector and replace with the new options
	 *
	 * @see http://datatables.net/reference/option/destroy
	 *
	 * @var bool
	 */
	private $destroy = FALSE;

	/**
	 * Define the starting point for data display when using pagination
	 *
	 * @see http://datatables.net/reference/option/displayStart
	 *
	 * @var int
	 */
	private $displayStart = 0;

	/**
	 * Define the table control elements to appear on the page and in what order
	 *
	 * @see http://datatables.net/reference/option/dom
	 *
	 * @var string
	 */
	private $dom = 'lfrtip';

	/**
	 * Define the options in the page length select list
	 *
	 * @see http://datatables.net/reference/option/lengthMenu
	 *
	 * @var array
	 */
	private $lengthMenu = [10, 25, 50, 100];

	/**
	 * Allows control over whether datagrid should use the top (true) unique cell that is found for a single column, or the bottom (false)
	 *
	 * @see http://datatables.net/reference/option/orderCellsTop
	 *
	 * @var bool
	 */
	private $orderCellsTop = FALSE;

	/**
	 * Highlight the columns being ordered in the table's body
	 *
	 * @see http://datatables.net/reference/option/orderClasses
	 *
	 * @var bool
	 */
	private $orderClasses = TRUE;

	/**
	 * Ordering to always be applied to the table
	 *
	 * @see http://datatables.net/reference/option/orderFixed
	 *
	 * @var array
	 */
	private $orderFixed = NULL;

	/**
	 * Multiple column ordering ability control
	 *
	 * @see http://datatables.net/reference/option/orderMulti
	 *
	 * @var bool
	 */
	private $orderMulti = TRUE;

	/**
	 * Change the initial page length (number of rows per page)
	 *
	 * @see http://datatables.net/reference/option/pageLength
	 *
	 * @var int
	 */
	private $pageLength = 10;

	/**
	 * Pagination button display options
	 *
	 * @see http://datatables.net/reference/option/pagingType
	 *
	 * @var string
	 */
	private $pagingType = 'simple_numbers';

	/**
	 * Retrieve an existing DataTables instance
	 *
	 * @see http://datatables.net/reference/option/retrieve
	 *
	 * @var bool
	 */
	private $retrieve = FALSE;

	/**
	 * Allow the table to reduce in height when a limited number of rows are shown
	 *
	 * @see http://datatables.net/reference/option/scrollCollapse
	 *
	 * @var bool
	 */
	private $scrollCollapse = FALSE;

	/**
	 * Component case-sensitive filtering option
	 *
	 * @see http://datatables.net/reference/option/search.caseInsensitive
	 *
	 * @var bool
	 */
	private $searchCaseInsensitive = TRUE;

	/**
	 * Enable or disable escaping of regular expression characters in the search term
	 *
	 * @see http://datatables.net/reference/option/search.regex
	 *
	 * @var bool
	 */
	private $searchRegex = FALSE;

	/**
	 * Set an initial filtering condition on the table
	 *
	 * @see http://datatables.net/reference/option/search.search
	 *
	 * @var string
	 */
	private $searchSearch = NULL;

	/**
	 * Enable or disable smart filtering
	 *
	 * @see http://datatables.net/reference/option/search.smart
	 *
	 * @var bool
	 */
	private $searchSmart = TRUE;

	/**
	 * Set a throttle frequency for searching
	 *
	 * @see http://datatables.net/reference/option/searchDelay
	 *
	 * @var int
	 */
	private $searchDelay = NULL;

	/**
	 * Saved state validity duration
	 * If -1 is user, Session Storage will be used
	 *
	 * @see http://datatables.net/reference/option/stateDuration
	 *
	 * @var int
	 */
	private $stateDuration = 7200;

	/**
	 * Tab index control for keyboard navigation
	 *
	 * @see http://datatables.net/reference/option/tabIndex
	 *
	 * @var int
	 */
	private $tabIndex = 0;

	/**
	 * @var bool
	 */
	private $ajaxSource = FALSE;

	/**
	 * @return void
	 */
	public function enableAutoWidth()
	{
		$this->autoWidth = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableAutoWidth()
	{
		$this->autoWidth = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledAutoWith() : bool
	{
		return $this->autoWidth;
	}

	/**
	 * @return void
	 */
	public function enableDeferRender()
	{
		$this->deferRender = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableDeferRender()
	{
		$this->deferRender = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledDeferRender() : bool
	{
		return $this->deferRender;
	}

	/**
	 * @return void
	 */
	public function enableJQueryUI()
	{
		$this->jQueryUI = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableJQueryUI()
	{
		$this->jQueryUI = FALSE;
	}

	/**
	 * @return bool
	 */
	public function useJQueryUI() : bool
	{
		return $this->jQueryUI;
	}

	/**
	 * @return void
	 */
	public function enableLengthChange()
	{
		$this->lengthChange = TRUE;

		// When length change feature is enabled, paging have to be enabled too
		$this->paging = TRUE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledLengthChange() : bool
	{
		return $this->lengthChange;
	}

	/**
	 * @return void
	 */
	public function disableLengthChange()
	{
		$this->lengthChange = FALSE;
	}

	/**
	 * @return void
	 */
	public function enableSorting()
	{
		$this->ordering = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableSorting()
	{
		$this->ordering = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledSorting() : bool
	{
		return $this->ordering;
	}

	/**
	 * @return void
	 */
	public function enablePaging()
	{
		$this->paging = TRUE;
	}

	/**
	 * @return void
	 */
	public function disablePaging()
	{
		$this->paging = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledPaging() : bool
	{
		return $this->paging;
	}

	/**
	 * @return void
	 */
	public function enableProcessing()
	{
		$this->processing = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableProcessing()
	{
		$this->processing = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledProcessing() : bool
	{
		return $this->processing;
	}

	/**
	 * @return void
	 */
	public function enableScrollX()
	{
		$this->scrollX = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableScrollX()
	{
		$this->scrollX = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledScrollX() : bool
	{
		return $this->scrollX;
	}

	/**
	 * @param string $scrollY
	 *
	 * @return void
	 */
	public function setScrollY(string $scrollY)
	{
		$this->scrollY = $scrollY;
	}

	/**
	 * @return string|NULL
	 */
	public function getScrollY()
	{
		return $this->scrollY;
	}

	/**
	 * @return void
	 */
	public function disableScrollY()
	{
		$this->scrollY = NULL;
	}

	/**
	 * @return void
	 */
	public function enableSearching()
	{
		$this->searching = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableSearching()
	{
		$this->searching = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledSearching() : bool
	{
		return $this->searching;
	}

	/**
	 * @return void
	 */
	public function enableServerSide()
	{
		$this->serverSide = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableServerSide()
	{
		$this->serverSide = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledServerSide() : bool
	{
		return $this->serverSide;
	}

	/**
	 * @return void
	 */
	public function enableStateSave()
	{
		$this->stateSave = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableStateSave()
	{
		$this->stateSave = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledStateSaving() : bool
	{
		return $this->stateSave;
	}

	/**
	 * @param int|array $deferLoading
	 *
	 * @return void
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function setDeferLoading($deferLoading)
	{
		if (!is_int($deferLoading) && !is_array($deferLoading)) {
			throw new Exceptions\InvalidArgumentException(sprintf('Provided value is not valid. Only integer or array are allowed. %s provided instead', gettype($deferLoading)));
		}

		$this->deferLoading = $deferLoading;
	}

	/**
	 * @return int|array|NULL
	 */
	public function getDeferLoading()
	{
		return $this->deferLoading;
	}

	/**
	 * @return void
	 */
	public function enableDestroy()
	{
		$this->destroy = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableDestroy()
	{
		$this->destroy = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledDestroy() : bool
	{
		return $this->destroy;
	}

	/**
	 * @param int $displayStart
	 *
	 * @return void
	 */
	public function setDisplayStart(int $displayStart)
	{
		$this->displayStart = $displayStart;
	}

	/**
	 * @return int
	 */
	public function getDisplayStart() : int
	{
		return $this->displayStart;
	}

	/**
	 * @param string $dom
	 *
	 * @return void
	 */
	public function setDom(string $dom)
	{
		$this->dom = $dom;
	}

	/**
	 * @return string
	 */
	public function getDom() : string
	{
		return $this->dom;
	}

	/**
	 * @param array $lengthMenu
	 *
	 * @return void
	 */
	public function setLengthMenu(array $lengthMenu)
	{
		$this->lengthMenu = $lengthMenu;
	}

	/**
	 * @return array
	 */
	public function getLengthMenu() : array
	{
		return $this->lengthMenu;
	}

	/**
	 * @return void
	 */
	public function enableOrderCellsTop()
	{
		$this->orderCellsTop = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableOrderCellsTop()
	{
		$this->orderCellsTop = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledOrderCellsTop() : bool
	{
		return $this->orderCellsTop;
	}

	/**
	 * @return void
	 */
	public function enableOrderClasses()
	{
		$this->orderClasses = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableOrderClasses()
	{
		$this->orderClasses = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledOrderClasses() : bool
	{
		return $this->orderClasses;
	}

	/**
	 * @return void
	 */
	public function enableMultiOrdering()
	{
		$this->orderMulti = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableMultiOrdering()
	{
		$this->orderMulti = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledMultiOrdering() : bool
	{
		return $this->orderMulti;
	}

	/**
	 * @param int $pageLength
	 *
	 * @return void
	 */
	public function setPageLength(int $pageLength)
	{
		$this->pageLength = $pageLength;
	}

	/**
	 * @return int
	 */
	public function getPageLength() : int
	{
		return $this->pageLength;
	}

	/**
	 * @param string $pagingType
	 *
	 * @return void
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function setPagingType(string $pagingType)
	{
		if (!in_array($pagingType, ['simple', 'simple_numbers', 'full', 'full_numbers'], TRUE)) {
			throw new Exceptions\InvalidArgumentException('Invalid paging type given.');
		}

		$this->pagingType = $pagingType;
	}

	/**
	 * @return string
	 */
	public function getPagingType() : string
	{
		return $this->pagingType;
	}

	/**
	 * @return void
	 */
	public function enableRetrieve()
	{
		$this->retrieve = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableRetrieve()
	{
		$this->retrieve = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledRetrieve() : bool
	{
		return $this->retrieve;
	}

	/**
	 * @return void
	 */
	public function enableScrollCollapse()
	{
		$this->scrollCollapse = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableScrollCollapse()
	{
		$this->scrollCollapse = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledScrollCollapse() : bool
	{
		return $this->scrollCollapse;
	}

	/**
	 * @return void
	 */
	public function enableCaseSensitiveSearch()
	{
		$this->searchCaseInsensitive = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableCaseSensitiveSearch()
	{
		$this->searchCaseInsensitive = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledCaseSensitiveSearch() : bool
	{
		return $this->searchCaseInsensitive;
	}

	/**
	 * @return void
	 */
	public function enableSearchRegex()
	{
		$this->searchRegex = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableSearchRegex()
	{
		$this->searchRegex = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledSearchRegex() : bool
	{
		return $this->searchRegex;
	}

	/**
	 * @param string $searchString
	 *
	 * @return void
	 */
	public function setDefaultSearchString(string $searchString)
	{
		$this->searchSearch = $searchString;
	}

	/**
	 * @return string|NULL
	 */
	public function getDefaultSearchString()
	{
		return $this->searchSearch;
	}

	/**
	 * @return void
	 */
	public function enableSmartSearch()
	{
		$this->searchSmart = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableSmartSearch()
	{
		$this->searchSmart = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledSmartSearch() : bool
	{
		return $this->searchSmart;
	}

	/**
	 * @param int $searchDelay
	 *
	 * @return void
	 */
	public function setSearchDelay(int $searchDelay)
	{
		$this->searchDelay = $searchDelay;
	}

	/**
	 * @return int|NULL
	 */
	public function getSearchDelay()
	{
		return $this->searchDelay;
	}

	/**
	 * @param int $stateDuration
	 *
	 * @return void
	 */
	public function setSaveStateDuration(int $stateDuration)
	{
		$this->stateDuration = $stateDuration;
	}

	/**
	 * @return void
	 */
	public function saveStateIntoSession()
	{
		$this->stateDuration = -1;
	}

	/**
	 * @return int
	 */
	public function getSaveStateDuration() : int
	{
		return $this->stateDuration;
	}

	/**
	 * @param int $tabIndex
	 *
	 * @return void
	 */
	public function setTabIndex(int $tabIndex)
	{
		$this->tabIndex = $tabIndex;
	}

	/**
	 * @return int
	 */
	public function getTabIndex() : int
	{
		return (int) $this->tabIndex;
	}

	/**
	 * @return void
	 */
	public function enableAjaxSource()
	{
		$this->ajaxSource = TRUE;
	}

	/**
	 * @return void
	 */
	public function disableAjaxSource()
	{
		$this->ajaxSource = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledAjaxSource() : bool
	{
		return $this->ajaxSource;
	}

	/**
	 * @return Utils\ArrayHash
	 */
	public function formatSettings()
	{
		$settings = new Utils\ArrayHash;

		$settings->autoWidth = $this->hasEnabledAutoWith() ? TRUE : FALSE;
		$settings->deferRender = $this->hasEnabledDeferRender() ? TRUE : FALSE;
		$settings->jQueryUI = $this->useJQueryUI() ? TRUE : FALSE;
		$settings->lengthChange = $this->hasEnabledLengthChange() ? TRUE : FALSE;
		$settings->lengthChange = $this->hasEnabledLengthChange() ? TRUE : FALSE;
		$settings->ordering = $this->hasEnabledSorting() ? TRUE : FALSE;
		$settings->paging = $this->hasEnabledPaging() ? TRUE : FALSE;
		$settings->processing = $this->hasEnabledProcessing() ? TRUE : FALSE;
		$settings->ajaxRequests = $this->hasEnabledAjax() ? TRUE : FALSE;

		if ($this->getScrollY() !== NULL) {
			$settings->scrollY = $this->getScrollY();
		}

		$settings->searching = $this->hasEnabledSearching() ? TRUE : FALSE;
		$settings->serverSide = $this->hasEnabledServerSide() ? TRUE : FALSE;
		$settings->ajax = ($this->hasEnabledServerSide() || $this->hasEnabledAjaxSource()) ? $this->link('getData!') : FALSE;

		if ($this->getDeferLoading() !== NULL) {
			$settings->deferLoading = $this->getDeferLoading();
		}

		$settings->destroy = $this->hasEnabledDestroy() ? TRUE : FALSE;
		$settings->displayStart = $this->getDisplayStart();
		$settings->dom = $this->getDom();
		$settings->lengthMenu = $this->getLengthMenu();
		$settings->orderCellsTop = $this->hasEnabledOrderCellsTop() ? TRUE : FALSE;
		$settings->orderClasses = $this->hasEnabledOrderClasses() ? TRUE : FALSE;
		$settings->order = $this->getDefaultSort();
		$settings->orderMulti = $this->hasEnabledMultiOrdering() ? TRUE : FALSE;
		$settings->pageLength = $this->getPageLength();
		$settings->pagingType = $this->getPagingType();
		$settings->retrieve = $this->hasEnabledRetrieve() ? TRUE : FALSE;
		$settings->scrollCollapse = $this->hasEnabledScrollCollapse() ? TRUE : FALSE;
		$settings->tabIndex = $this->getTabIndex();

		// Search settings
		$search = $settings->search = new Utils\ArrayHash;
		$search->caseInsensitive = $this->hasEnabledCaseSensitiveSearch() ? TRUE : FALSE;
		$search->regex = $this->hasEnabledSearchRegex() ? TRUE : FALSE;
		$search->search = $this->getDefaultSearchString();
		$search->smart = $this->hasEnabledSmartSearch() ? TRUE : FALSE;
		$settings->searchDelay = $this->getSearchDelay();

		// DataTables state saver
		if ($this->hasEnabledStateSaving()) {
			$settings->stateSave = $this->hasEnabledStateSaving() ? TRUE : FALSE;
			$settings->stateDuration = $this->getSaveStateDuration() === -1 && !$this->hasStateSaver() ? 7200 : $this->getSaveStateDuration();
			$settings->saveSateLink = $this->link('saveState!');
			$settings->loadSateLink = $this->link('loadState!');
		}

		// Columns settings
		$settings->columns = [];

		// If data grid has row actions
		if ($this->hasGlobalButtons()) {
			$columnSettings = new Utils\ArrayHash;

			$columnSettings->className = 'middle js-data-grid-row-checkbox';
			$columnSettings->orderable = FALSE;
			$columnSettings->searchable = FALSE;
			$columnSettings->visible = TRUE;
			$columnSettings->name = 'rowSelection';
			if ($this->hasEnabledServerSide()) {
				$columnSettings->data = 'rowSelection';
			}

			$settings->columns[] = $columnSettings;
		}

		/** @var DataTables\Columns\IColumn $column */
		foreach ($this->getColumns() as $column) {
			$columnSettings = new Utils\ArrayHash;

			$columnSettings->cellType = $column->getCellType();
			$columnSettings->className = $column->getClassName();

			if ($this->hasEnabledServerSide()) {
				$columnSettings->data = $column->getName();
			}

			$columnSettings->defaultContent = $column->getDefaultContent();
			$columnSettings->name = $column->getName();
			$columnSettings->orderable = $column->isSortable();

			if ($column->getOrderData() !== NULL) {
				$columnSettings->orderData = $column->getOrderData();
			}

			if ($column->getOrderDataType() !== NULL) {
				$columnSettings->orderDataType = $column->getOrderDataType();
			}

			$columnSettings->orderSequence = $column->getOrderSequence();
			$columnSettings->searchable = $column->isSearchable();
			$columnSettings->title = $column->getLabel();
			$columnSettings->type = $column->getType();
			$columnSettings->visible = $column->isVisible();

			if ($column->getWidth() !== NULL) {
				$columnSettings->width = $column->getWidth();
			}

			$settings->columns[] = $columnSettings;
		}

		return $settings;
	}

	/**
	 * @return bool
	 */
	abstract function hasEnabledAjax() : bool;

	/**
	 * @return bool
	 */
	abstract function hasStateSaver() : bool;

	/**
	 * @return bool
	 */
	abstract function hasColumns() : bool;

	/**
	 * @return DataTables\Components\Columns\IColumn[]
	 */
	abstract function getColumns() : array;

	/**
	 * @return bool
	 */
	abstract function hasGlobalButtons() : bool;

	/**
	 * @return DataTables\Components\Actions\IButton[]
	 */
	abstract function getGlobalButtons() : array;
}
