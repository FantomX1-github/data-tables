<?php
/**
 * Settings.php
 *
 * @copyright	More in license.md
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec http://www.ipublikuj.eu
 * @package		iPublikuj:DataTables!
 * @subpackage	Components
 * @since		5.0
 *
 * @date		18.10.14
 */

namespace IPub\DataTables\Components;

use Nette;
use Nette\Application\UI;
use Nette\Utils;

use IPub;
use IPub\DataTables;
use IPub\DataTables\Exceptions;

abstract class Settings extends UI\Control
{
	/**
	 * Enable or disable automatic column width calculation
	 *
	 * @see http://datatables.net/reference/option/autoWidth
	 *
	 * @var bool
	 */
	protected $autoWidth = FALSE;

	/**
	 * Enable or disable defer rendering of cells
	 *
	 * @see http://datatables.net/reference/option/deferRender
	 *
	 * @var bool
	 */
	protected $deferRender = FALSE;

	/**
	 * Enable or disable jQuery UI markup
	 *
	 * @see http://datatables.net/reference/option/jQueryUI
	 *
	 * @var bool
	 */
	protected $jQueryUI = FALSE;

	/**
	 * When pagination is enabled, this option will display an option for
	 * the end user to change number of records to be shown per page
	 *
	 * @see http://datatables.net/reference/option/lengthChange
	 *
	 * @var bool
	 */
	protected $lengthChange = TRUE;

	/**
	 * Enable or disable ordering of columns
	 *
	 * @see http://datatables.net/reference/option/ordering
	 *
	 * @var bool
	 */
	protected $ordering = TRUE;

	/**
	 * Enable or disable table pagination
	 *
	 * @see http://datatables.net/reference/option/paging
	 *
	 * @var bool
	 */
	protected $paging = TRUE;

	/**
	 * Enable or disable the display of a 'processing' indicator when the table is being processed
	 *
	 * @see http://datatables.net/reference/option/processing
	 *
	 * @var bool
	 */
	protected $processing = FALSE;

	/**
	 * Enable or disable horizontal scrolling
	 *
	 * @see http://datatables.net/reference/option/scrollX
	 *
	 * @var bool
	 */
	protected $scrollX = FALSE;

	/**
	 * Disable or set vertical scrolling
	 *
	 * @see http://datatables.net/reference/option/scrollY
	 *
	 * @var string
	 */
	protected $scrollY;

	/**
	 * Enable or disable the search abilities
	 *
	 * @see http://datatables.net/reference/option/searching
	 *
	 * @var bool
	 */
	protected $searching = TRUE;

	/**
	 * Enable or disable server-side processing mode
	 *
	 * @see http://datatables.net/reference/option/serverSide
	 *
	 * @var bool
	 */
	protected $serverSide = FALSE;

	/**
	 * Enable or disable state saving
	 *
	 * @see http://datatables.net/reference/option/stateSave
	 *
	 * @var bool
	 */
	protected $stateSave = FALSE;

	/**
	 * Delay the loading of server-side data until second draw
	 *
	 * @see http://datatables.net/reference/option/deferLoading
	 *
	 * @var int|array
	 */
	protected $deferLoading = NULL;

	/**
	 * Enable or disable destroying any existing table matching the selector and replace with the new options
	 *
	 * @see http://datatables.net/reference/option/destroy
	 *
	 * @var bool
	 */
	protected $destroy = FALSe;

	/**
	 * Define the starting point for data display when using pagination
	 *
	 * @see http://datatables.net/reference/option/displayStart
	 *
	 * @var int
	 */
	protected $displayStart = 0;

	/**
	 * Define the table control elements to appear on the page and in what order
	 *
	 * @see http://datatables.net/reference/option/dom
	 *
	 * @var string
	 */
	protected $dom = 'lfrtip';

	/**
	 * Define the options in the page length select list
	 *
	 * @see http://datatables.net/reference/option/lengthMenu
	 *
	 * @var array
	 */
	protected $lengthMenu = [10, 25, 50, 100];

	/**
	 * Allows control over whether datagrid should use the top (true) unique cell that is found for a single column, or the bottom (false)
	 *
	 * @see http://datatables.net/reference/option/orderCellsTop
	 *
	 * @var bool
	 */
	protected $orderCellsTop = FALSE;

	/**
	 * Highlight the columns being ordered in the table's body
	 *
	 * @see http://datatables.net/reference/option/orderClasses
	 *
	 * @var bool
	 */
	protected $orderClasses = TRUE;

	/**
	 * Ordering to always be applied to the table
	 *
	 * @see http://datatables.net/reference/option/orderFixed
	 *
	 * @var array
	 */
	protected $orderFixed = NULL;

	/**
	 * Multiple column ordering ability control
	 *
	 * @see http://datatables.net/reference/option/orderMulti
	 *
	 * @var bool
	 */
	protected $orderMulti = TRUE;

	/**
	 * Change the initial page length (number of rows per page)
	 *
	 * @see http://datatables.net/reference/option/pageLength
	 *
	 * @var int
	 */
	protected $pageLength = 10;

	/**
	 * Pagination button display options
	 *
	 * @see http://datatables.net/reference/option/pagingType
	 *
	 * @var string
	 */
	protected $pagingType = 'simple_numbers';

	/**
	 * Retrieve an existing DataTables instance
	 *
	 * @see http://datatables.net/reference/option/retrieve
	 *
	 * @var bool
	 */
	protected $retrieve = FALSE;

	/**
	 * Allow the table to reduce in height when a limited number of rows are shown
	 *
	 * @see http://datatables.net/reference/option/scrollCollapse
	 *
	 * @var bool
	 */
	protected $scrollCollapse = FALSE;

	/**
	 * Component case-sensitive filtering option
	 *
	 * @see http://datatables.net/reference/option/search.caseInsensitive
	 *
	 * @var bool
	 */
	protected $searchCaseInsensitive = TRUE;

	/**
	 * Enable or disable escaping of regular expression characters in the search term
	 *
	 * @see http://datatables.net/reference/option/search.regex
	 *
	 * @var bool
	 */
	protected $searchRegex = FALSE;

	/**
	 * Set an initial filtering condition on the table
	 *
	 * @see http://datatables.net/reference/option/search.search
	 *
	 * @var string
	 */
	protected $searchSearch = NULL;

	/**
	 * Enable or disable smart filtering
	 *
	 * @see http://datatables.net/reference/option/search.smart
	 *
	 * @var bool
	 */
	protected $searchSmart = TRUE;

	/**
	 * Set a throttle frequency for searching
	 *
	 * @see http://datatables.net/reference/option/searchDelay
	 *
	 * @var int
	 */
	protected $searchDelay = NULL;

	/**
	 * Saved state validity duration
	 * If -1 is user, Session Storage will be used
	 *
	 * @see http://datatables.net/reference/option/stateDuration
	 *
	 * @var int
	 */
	protected $stateDuration = 7200;

	/**
	 * Tab index control for keyboard navigation
	 *
	 * @see http://datatables.net/reference/option/tabIndex
	 *
	 * @var int
	 */
	protected $tabIndex = 0;

	/**
	 * @var bool
	 */
	protected $ajaxSource = FALSE;

	/**
	 * @return $this
	 */
	public function enableAutoWidth()
	{
		$this->autoWidth = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableAutoWidth()
	{
		$this->autoWidth = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledAutoWith()
	{
		return $this->autoWidth;
	}

	/**
	 * @return $this
	 */
	public function enableDeferRender()
	{
		$this->deferRender = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableDeferRender()
	{
		$this->deferRender = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledDeferRender()
	{
		return $this->deferRender;
	}

	/**
	 * @return $this
	 */
	public function enableJQueryUI()
	{
		$this->jQueryUI = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableJQueryUI()
	{
		$this->jQueryUI = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function useJQueryUI()
	{
		return $this->jQueryUI;
	}

	/**
	 * @return $this
	 */
	public function enableLengthChange()
	{
		$this->lengthChange = TRUE;

		// When length change feature is enabled, paging have to be enabled too
		$this->paging = TRUE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledLengthChange()
	{
		return $this->lengthChange;
	}

	/**
	 * @return $this
	 */
	public function disableLengthChange()
	{
		$this->lengthChange = FALSE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function enableSorting()
	{
		$this->ordering = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableSorting()
	{
		$this->ordering = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledSorting()
	{
		return $this->ordering;
	}

	/**
	 * @return $this
	 */
	public function enablePaging()
	{
		$this->paging = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disablePaging()
	{
		$this->paging = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledPaging()
	{
		return $this->paging;
	}

	/**
	 * @return $this
	 */
	public function enableProcessing()
	{
		$this->processing = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableProcessing()
	{
		$this->processing = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledProcessing()
	{
		return $this->processing;
	}

	/**
	 * @return $this
	 */
	public function enableScrollX()
	{
		$this->scrollX = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableScrollX()
	{
		$this->scrollX = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledScrollX()
	{
		return $this->scrollX;
	}

	/**
	 * @param string $scrollY
	 *
	 * @return $this
	 */
	public function setScrollY($scrollY)
	{
		$this->scrollY = (string) $scrollY;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getScrollY()
	{
		return $this->scrollY;
	}

	/**
	 * @return $this
	 */
	public function disableScrollY()
	{
		$this->scrollY = NULL;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function enableSearching()
	{
		$this->searching = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableSearching()
	{
		$this->searching = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledSearching()
	{
		return $this->searching;
	}

	/**
	 * @return $this
	 */
	public function enableServerSide()
	{
		$this->serverSide = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableServerSide()
	{
		$this->serverSide = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function useServerSide()
	{
		return $this->serverSide;
	}

	/**
	 * @return $this
	 */
	public function enableStateSave()
	{
		$this->stateSave = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableStateSave()
	{
		$this->stateSave = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledStateSaving()
	{
		return $this->stateSave;
	}

	/**
	 * @param string|array $deferLoading
	 *
	 * @return $this
	 */
	protected function setDeferLoading($deferLoading)
	{
		$this->deferLoading = $deferLoading;

		return $this;
	}

	/**
	 * @return string|array
	 */
	protected function getDeferLoading()
	{
		return $this->deferLoading;
	}

	/**
	 * @return $this
	 */
	public function enableDestroy()
	{
		$this->destroy = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableDestroy()
	{
		$this->destroy = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledDestroy()
	{
		return $this->destroy;
	}

	/**
	 * @param int $displayStart
	 *
	 * @return $this
	 */
	public function setDisplayStart($displayStart)
	{
		$this->displayStart = (int) $displayStart;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getDisplayStart()
	{
		return (int) $this->displayStart;
	}

	/**
	 * @param string $dom
	 *
	 * @return $this
	 */
	public function setDom($dom)
	{
		$this->dom = (string) $dom;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDom()
	{
		return (string) $this->dom;
	}

	/**
	 * @param array $lengthMenu
	 *
	 * @return $this
	 */
	public function setLengthMenu(array $lengthMenu)
	{
		$this->lengthMenu = $lengthMenu;

		return $this;
	}

	/**
	 * @return array
	 */
	public function getLengthMenu()
	{
		return $this->lengthMenu;
	}

	/**
	 * @return $this
	 */
	public function enableOrderCellsTop()
	{
		$this->orderCellsTop = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableOrderCellsTop()
	{
		$this->orderCellsTop = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledOrderCellsTop()
	{
		return $this->orderCellsTop;
	}

	/**
	 * @return $this
	 */
	public function enableOrderClasses()
	{
		$this->orderClasses = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableOrderClasses()
	{
		$this->orderClasses = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledOrderClasses()
	{
		return $this->orderClasses;
	}

	/**
	 * @return $this
	 */
	public function enableMultiOrdering()
	{
		$this->orderMulti = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableMultiOrdering()
	{
		$this->orderMulti = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledMultiOrdering()
	{
		return $this->orderMulti;
	}

	/**
	 * @param int $pageLength
	 *
	 * @return $this
	 */
	public function setPageLength($pageLength)
	{
		$this->pageLength = (int) $pageLength;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getPageLength()
	{
		return (int) $this->pageLength;
	}

	/**
	 * @param string $pagingType
	 *
	 * @return $this
	 *
	 * @throws Exceptions\InvalidArgumentException
	 */
	public function setPagingType($pagingType)
	{
		if (!in_array($pagingType, ['simple', 'simple_numbers', 'full', 'full_numbers'])) {
			throw new Exceptions\InvalidArgumentException('Invalid paging type given.');
		}

		$this->pagingType = (string) $pagingType;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getPagingType()
	{
		return (string) $this->pagingType;
	}

	/**
	 * @return $this
	 */
	public function enableRetrieve()
	{
		$this->retrieve = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableRetrieve()
	{
		$this->retrieve = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledRetrieve()
	{
		return $this->retrieve;
	}

	/**
	 * @return $this
	 */
	public function enableScrollCollapse()
	{
		$this->scrollCollapse = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableScrollCollapse()
	{
		$this->scrollCollapse = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledScrollCollapse()
	{
		return $this->scrollCollapse;
	}

	/**
	 * @return $this
	 */
	public function enableCaseSensitiveSearch()
	{
		$this->searchCaseInsensitive = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableCaseSensitiveSearch()
	{
		$this->searchCaseInsensitive = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledCaseSensitiveSearch()
	{
		return $this->searchCaseInsensitive;
	}

	/**
	 * @return $this
	 */
	public function enableSearchRegex()
	{
		$this->searchRegex = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableSearchRegex()
	{
		$this->searchRegex = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledSearchRegex()
	{
		return $this->searchRegex;
	}

	/**
	 * @param string $searchString
	 *
	 * @return $this
	 */
	public function setDefaultSearchString($searchString)
	{
		$this->searchSearch = (string) $searchString;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getDefaultSearchString()
	{
		return (string) $this->searchSearch;
	}

	/**
	 * @return $this
	 */
	public function enableSmartSearch()
	{
		$this->searchSmart = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableSmartSearch()
	{
		$this->searchSmart = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function hasEnabledSmartSearch()
	{
		return $this->searchSmart;
	}

	/**
	 * @param int $searchDelay
	 *
	 * @return $this
	 */
	public function setSearchDelay($searchDelay)
	{
		$this->searchDelay = (int) $searchDelay;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getSearchDelay()
	{
		return (int) $this->searchDelay;
	}

	/**
	 * @param int $stateDuration
	 *
	 * @return $this
	 */
	public function setSaveStateDuration($stateDuration)
	{
		$this->stateDuration = (int) $stateDuration;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function saveStateIntoSession()
	{
		$this->stateDuration = -1;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getSaveStateDuration()
	{
		return $this->stateDuration;
	}

	/**
	 * @param int $tabIndex
	 *
	 * @return $this
	 */
	public function setTabIndex($tabIndex)
	{
		$this->tabIndex = (int) $tabIndex;

		return $this;
	}

	/**
	 * @return int
	 */
	public function getTabIndex()
	{
		return (int) $this->tabIndex;
	}

	/**
	 * @return $this
	 */
	public function enableAjaxSource()
	{
		$this->ajaxSource = TRUE;

		return $this;
	}

	/**
	 * @return $this
	 */
	public function disableAjaxSource()
	{
		$this->ajaxSource = FALSE;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function useAjaxSource()
	{
		return $this->ajaxSource;
	}

	/**
	 * @return Utils\ArrayHash
	 */
	public function formatSettings()
	{
		$settings = new Utils\ArrayHash;

		$settings->autoWidth		= $this->hasEnabledAutoWith() ? true : false;
		$settings->deferRender		= $this->hasEnabledDeferRender() ? true : false;
		$settings->jQueryUI			= $this->useJQueryUI() ? true : false;
		$settings->lengthChange		= $this->hasEnabledLengthChange() ? true : false;
		$settings->lengthChange		= $this->hasEnabledLengthChange() ? true : false;
		$settings->ordering			= $this->hasEnabledSorting() ? true : false;
		$settings->paging			= $this->hasEnabledPaging() ? true : false;
		$settings->processing		= $this->hasEnabledProcessing() ? true : false;
		$settings->ajaxRequests		= $this->hasEnabledAjax() ? true : false;
		if ($this->getScrollY() !== NULL) {
			$settings->scrollY		= $this->getScrollY();
		}
		$settings->searching		= $this->hasEnabledSearching() ? true : false;
		$settings->serverSide		= $this->useServerSide() ? true : false;
		$settings->ajax				= ($this->useServerSide() || $this->useAjaxSource()) ? $this->link('getData!') : false;
		if ($this->getDeferLoading() !== NULL) {
			$settings->deferLoading	= $this->getDeferLoading();
		}
		$settings->destroy			= $this->hasEnabledDestroy() ? true : false;
		$settings->displayStart		= $this->getDisplayStart();
		$settings->dom				= $this->getDom();
		$settings->lengthMenu		= $this->getLengthMenu();
		$settings->orderCellsTop	= $this->hasEnabledOrderCellsTop() ? true : false;
		$settings->orderClasses		= $this->hasEnabledOrderClasses() ? true : false;
		$settings->order			= $this->getDefaultSort();
		$settings->orderMulti		= $this->hasEnabledMultiOrdering() ? true : false;
		$settings->pageLength		= $this->getPageLength();
		$settings->pagingType		= $this->getPagingType();
		$settings->retrieve			= $this->hasEnabledRetrieve() ? true : false;
		$settings->scrollCollapse	= $this->hasEnabledScrollCollapse() ? true : false;
		$settings->tabIndex			= $this->getTabIndex();

		// Search settings
		$search = $settings->search = new Utils\ArrayHash;
		$search->caseInsensitive	= $this->hasEnabledCaseSensitiveSearch() ? true : false;
		$search->regex				= $this->hasEnabledSearchRegex() ? true : false;
		$search->search				= $this->getDefaultSearchString();
		$search->smart				= $this->hasEnabledSmartSearch() ? true : false;
		$settings->searchDelay		= $this->getSearchDelay();

		// DataTables state saver
		if ($this->hasEnabledStateSaving()) {
			$settings->stateSave		= $this->hasEnabledStateSaving() ? true : false;
			$settings->stateDuration	= $this->getSaveStateDuration() == -1 && !$this->stateSaver ? 7200 : $this->getSaveStateDuration();
			$settings->saveSateLink		= $this->link('saveState!');
			$settings->loadSateLink		= $this->link('loadState!');
		}

		// Columns settings
		$settings->columns = [];

		// If data grid has row actions
		if ($this->hasGlobalButtons()) {
			$columnSettings = new Utils\ArrayHash;

			$columnSettings->className		= 'middle js-data-grid-row-checkbox';
			$columnSettings->orderable		= FALSE;
			$columnSettings->searchable		= FALSE;
			$columnSettings->visible		= TRUE;
			$columnSettings->name			= 'rowSelection';
			if ($this->useServerSide()) {
				$columnSettings->data		= 'rowSelection';
			}

			$settings->columns[] = $columnSettings;
		}

		foreach($this->getColumns() as $column) {
			$columnSettings = new Utils\ArrayHash;

			$columnSettings->cellType		= $column->getCellType();
			$columnSettings->className		= $column->getClassName();
			if ($this->useServerSide()) {
				$columnSettings->data		= $column->getName();
			}
			$columnSettings->defaultContent	= $column->getDefaultContent();
			$columnSettings->name			= $column->getName();
			$columnSettings->orderable		= $column->isSortable();
			if ($column->getOrderData() !== NULL) {
				$columnSettings->orderData		= $column->getOrderData();
			}
			if ($column->getOrderDataType() !== NULL) {
				$columnSettings->orderDataType	= $column->getOrderDataType();
			}
			$columnSettings->orderSequence	= $column->getOrderSequence();
			$columnSettings->searchable		= $column->isSearchable();
			$columnSettings->title			= $column->getLabel();
			$columnSettings->type			= $column->getType();
			$columnSettings->visible		= $column->isVisible();
			if ($column->getWidth() !== NULL) {
				$columnSettings->width		= $column->getWidth();
			}

			$settings->columns[] = $columnSettings;
		}

		return $settings;
	}
}
