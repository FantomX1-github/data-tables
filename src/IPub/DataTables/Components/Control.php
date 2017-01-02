<?php
/**
 * Component.php
 *
 * @copyright      More in license.md
 * @license        http://www.ipublikuj.eu
 * @author         Adam Kadlec http://www.ipublikuj.eu
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 * @since          5.0
 *
 * @date           18.10.14
 */

namespace IPub\DataTables\Components;

use Nette;
use Nette\Application\UI;
use Nette\ComponentModel;
use Nette\Http;
use Nette\Utils;
use Nette\Localization;

use IPub;
use IPub\DataTables;
use IPub\DataTables\Components;
use IPub\DataTables\DataSources;
use IPub\DataTables\Exceptions;
use IPub\DataTables\Filters;
use IPub\DataTables\StateSavers;

/**
 * Data grid control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     UI
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @method onBeforeConfigure(Nette\Application\UI\Control $component)
 * @method onAfterConfigure(Nette\Application\UI\Control $component)
 */
class Control extends Settings
{
	/**
	 * @var \Closure[]
	 */
	public $onBeforeConfigure = [];

	/**
	 * @var \Closure[]
	 */
	public $onAfterConfigure = [];

	/**
	 * @var DataSources\Model
	 */
	protected $model;

	/**
	 * @var string
	 */
	protected $primaryKey = 'id';

	/**
	 * @var StateSavers\IStateSaver
	 */
	protected $stateSaver;

	/**
	 * @var int|null
	 */
	protected $activeRowForm;

	/**
	 * @var array
	 */
	protected $defaultSort = [];

	/**
	 * @var array
	 */
	public $sort = [];

	/**
	 * @var array
	 */
	protected $defaultFilter = [];

	/**
	 * @var array
	 */
	protected $filter = [];

	/**
	 * @var bool
	 */
	protected $hasColumns;

	/**
	 * @var bool
	 */
	protected $hasFilters;

	/**
	 * @var callback
	 */
	protected $rowFormCallback;

	/**
	 * @var Localization\ITranslator
	 */
	protected $translator;

	/**
	 * @var Http\IRequest
	 */
	protected $httpRequest;

	/**
	 * @var bool
	 */
	protected $ajax = TRUE;

	/**
	 * @var bool
	 */
	protected $fullRedraw = FALSE;

	/**
	 * @var null|string
	 */
	protected $templateFile = NULL;

	/**
	 * @param Http\IRequest $httpRequest
	 */
	public function injectHttpRequest(Http\IRequest $httpRequest)
	{
		$this->httpRequest = $httpRequest;
	}

	/**
	 * @param StateSavers\IStateSaver $stateSaver
	 */
	public function injectStateSaver(StateSavers\IStateSaver $stateSaver)
	{
		$this->stateSaver = $stateSaver;
	}

	/**
	 * @param Localization\ITranslator $translator
	 */
	public function injectTranslator(Localization\ITranslator $translator = NULL)
	{
		$this->translator = $translator;
	}

	/**
	 * @param ComponentModel\IComponent $presenter
	 */
	protected function attached($presenter)
	{
		parent::attached($presenter);

		if (!$presenter instanceof UI\Presenter) return;

		// Call events
		$this->onBeforeConfigure($this);

		// Call data grid configuration
		$this->configure($presenter);

		// Call events
		$this->onAfterConfigure($this);

		// Collect all actions
		if ($this->hasGlobalButtons()) {
			$actions = [];

			foreach ($this->getComponent(Components\Actions\Button::ID)->getComponents() as $name => $action) {
				$actions[$name] = $action->getAction();
			}

			$this['dataGridForm'][Components\Actions\Button::ID]['name']->setItems($actions);
		}
	}

	/**
	 * @param ComponentModel\IComponent $presenter
	 */
	protected function configure(ComponentModel\IComponent $presenter)
	{

	}

	/**
	 * Render data grid
	 */
	protected function beforeRender()
	{
		// Check if data are loaded via ajax
		if ($this->useAjaxSource()) {
			$rows = NULL;

			// Or are loaded in render process
		} else {
			$rows = $this->model->getData();
		}

		// Add data to template
		$this->template->results = $this->getDataCount();
		$this->template->columns = $this->getColumns();
		$this->template->columnsCount = $this->getColumnsCount();
		$this->template->filters = $this->getFilters();
		$this->template->primaryKey = $this->getPrimaryKey();
		$this->template->rows = $rows;
		$this->template->settings = $this->formatSettings();
		$this->template->useServerSide = $this->useServerSide();
		$this->template->useAjaxSource = $this->useAjaxSource();

		// Check if translator is available
		if ($this->getTranslator() instanceof Localization\ITranslator) {
			$this->template->setTranslator($this->getTranslator());
		}

		// If template was not defined before...
		if ($this->template->getFile() === NULL) {
			// ...try to get base component template file
			$templateFile = !empty($this->templateFile) ? $this->templateFile : __DIR__ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'default.latte';
			$this->template->setFile($templateFile);
		}
	}

	public function render()
	{
		$this->beforeRender();

		// Render component template
		$this->template->render();
	}

	/**
	 * Set data source primary key
	 *
	 * @param string $primaryKey
	 *
	 * @return $this
	 */
	public function setPrimaryKey($primaryKey)
	{
		$this->primaryKey = (string) $primaryKey;

		return $this;
	}

	/**
	 * Get data source primary key
	 *
	 * @return string
	 */
	public function getPrimaryKey()
	{
		return $this->primaryKey;
	}

	/**
	 * Enable ajax requests
	 *
	 * @return $this
	 */
	public function enableAjax()
	{
		$this->ajax = TRUE;

		return $this;
	}

	/**
	 * Disable ajax requests
	 *
	 * @return $this
	 */
	public function disableAjax()
	{
		$this->ajax = FALSE;

		return $this;
	}

	/**
	 * Check if ajax for buttons & other request is enabled
	 *
	 * @return bool
	 */
	public function hasEnabledAjax()
	{
		return (bool) $this->ajax;
	}

	/**
	 * Enable table full redraw
	 *
	 * @return $this
	 */
	public function enableFullRedraw()
	{
		$this->fullRedraw = TRUE;

		return $this;
	}

	/**
	 * Disable table full redraw
	 *
	 * @return $this
	 */
	public function disableFullRedraw()
	{
		$this->fullRedraw = FALSE;

		return $this;
	}

	/**
	 * Create column component
	 *
	 * @param string $type
	 * @param string $name
	 * @param string $label
	 * @param null|string $width
	 *
	 * @return Components\Columns\IColumn
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\DuplicateColumnException
	 */
	public function addColumn($type, $name, $label, $width = NULL)
	{
		if (!in_array($type, [
			Components\Columns\IColumn::TYPE_ACTION,
			Components\Columns\IColumn::TYPE_DATE,
			Components\Columns\IColumn::TYPE_IMAGE,
			Components\Columns\IColumn::TYPE_NUMBER,
			Components\Columns\IColumn::TYPE_STATUS,
			Components\Columns\IColumn::TYPE_EMAIL,
			Components\Columns\IColumn::TYPE_LINK,
			Components\Columns\IColumn::TYPE_TEXT
		])
		) {
			throw new Exceptions\InvalidArgumentException("Invalid column type given.");
		}

		if ($this->columnExists($name)) {
			throw new Exceptions\DuplicateColumnException("Column $name already exists.");
		}

		// Create column class name
		$type = '\\IPub\\DataTables\\Components\\Columns\\' . $type;

		$column = new $type($this, $name, $label);
		$column->setWidth($width);

		return $column;
	}

	/**
	 * @param string $name
	 * @param null|string $label
	 * @param null|string $width
	 * @param null|int $truncate
	 *
	 * @return Components\Columns\IColumn
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\DuplicateColumnException
	 */
	public function addColumnText($name, $label = NULL, $width = NULL, $truncate = NULL)
	{
		$column = $this->addColumn(Components\Columns\IColumn::TYPE_TEXT, $name, $label, $width);
		$column->setTruncate($truncate);

		return $column;
	}

	/**
	 * @param string $name
	 * @param null|string $label
	 * @param null|string $width
	 *
	 * @return Components\Columns\IColumn
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\DuplicateColumnException
	 */
	public function addColumnEmail($name, $label = NULL, $width = NULL)
	{
		return $this->addColumn(Components\Columns\IColumn::TYPE_EMAIL, $name, $label, $width);
	}

	/**
	 * @param string $name
	 * @param null|string $label
	 * @param null|string $width
	 *
	 * @return Components\Columns\IColumn
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\DuplicateColumnException
	 */
	public function addColumnLink($name, $label = NULL, $width = NULL)
	{
		return $this->addColumn(Components\Columns\IColumn::TYPE_LINK, $name, $label, $width);
	}

	/**
	 * @param string $name
	 * @param null|string $label
	 * @param null|string $width
	 *
	 * @return Components\Columns\IColumn
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\DuplicateColumnException
	 */
	public function addColumnDate($name, $label = NULL, $width = NULL)
	{
		return $this->addColumn(Components\Columns\IColumn::TYPE_DATE, $name, $label, $width);
	}

	/**
	 * @param string $name
	 * @param null|string $label
	 * @param null|string $width
	 *
	 * @return Components\Columns\IColumn
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\DuplicateColumnException
	 */
	public function addColumnNumber($name, $label = NULL, $width = NULL)
	{
		return $this->addColumn(Components\Columns\IColumn::TYPE_NUMBER, $name, $label, $width);
	}

	/**
	 * @param string $name
	 * @param null|string $label
	 * @param null|string $width
	 *
	 * @return Components\Columns\IColumn
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\DuplicateColumnException
	 */
	public function addColumnAction($name, $label = NULL, $width = NULL)
	{
		return $this->addColumn(Components\Columns\IColumn::TYPE_ACTION, $name, $label, $width);
	}

	/**
	 * @param string $name
	 * @param null|string $label
	 * @param null|string $width
	 *
	 * @return Components\Columns\IColumn
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\DuplicateColumnException
	 */
	public function addColumnImage($name, $label = NULL, $width = NULL)
	{
		return $this->addColumn(Components\Columns\IColumn::TYPE_IMAGE, $name, $label, $width);
	}

	/**
	 * @return array
	 */
	public function getColumns()
	{
		return $this->getComponent(Columns\IColumn::ID)->getComponents();
	}

	/**
	 * @return int $count
	 */
	public function getColumnsCount()
	{
		$count = count($this->getColumns());

		if ($this->hasGlobalButtons() || $this->hasRowButtons()) {
			$count++;
		}

		return $count;
	}

	/**
	 * @param string $columnName
	 *
	 * @return bool
	 */
	public function columnExists($columnName)
	{
		return $this->getComponent(Columns\IColumn::ID, FALSE) && $this->getComponent(Columns\IColumn::ID)->getComponent($columnName, FALSE) ? TRUE : FALSE;
	}

	/**
	 * @param string $name
	 * @param bool $need
	 *
	 * @return mixed
	 */
	public function getColumn($name, $need = TRUE)
	{
		return $this->hasColumns()
			? $this->getComponent(Columns\IColumn::ID)->getComponent($name, $need)
			: NULL;
	}

	/**
	 * @param string $name
	 *
	 * @return Nette\Forms\IControl
	 *
	 * @throws Exceptions\UnknownColumnException
	 */
	public function getColumnInput($name)
	{
		if (!$this->columnExists($name)) {
			throw new Exceptions\UnknownColumnException("Column $name doesn't exists.");
		}

		return $this['dataGridForm'][Components\Buttons\Button::ID][$name];
	}

	/**
	 * @param bool $useCache
	 *
	 * @return bool
	 */
	public function hasColumns($useCache = TRUE)
	{
		$hasColumns = $this->hasColumns;

		if ($hasColumns === NULL || $useCache === FALSE) {
			$container = $this->getComponent(Columns\IColumn::ID, FALSE);
			$hasColumns = $container && count($container->getComponents()) > 0;
			$this->hasColumns = $useCache ? $hasColumns : NULL;
		}

		return $hasColumns;
	}

	/**
	 * @return bool
	 */
	public function isEditable()
	{
		foreach ($this->getColumns() as $column) {
			if ($column->isEditable()) {
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasRowButtons()
	{
		return (($buttons = $this->getComponent(Components\Buttons\Button::ID, FALSE)) AND count($buttons->getComponents()) > 1) ? TRUE : FALSE;
	}

	/**
	 * @param string $name
	 * @param null|string $label
	 *
	 * @return Components\Actions\Button
	 *
	 * @throws Exceptions\InvalidArgumentException
	 * @throws Exceptions\DuplicateGlobalButtonException
	 */
	public function addGlobalButton($name, $label)
	{
		if (($buttons = $this->getComponent(Components\Actions\Button::ID, FALSE)) AND $buttons->getComponent($name, FALSE)) {
			throw new Exceptions\DuplicateGlobalButtonException("Global button $name already exists.");
		}

		return new Components\Actions\Button($this, $name, $label);
	}

	/**
	 * @return bool
	 */
	public function hasGlobalButtons()
	{
		return (($buttons = $this->getComponent(Components\Actions\Button::ID, FALSE)) AND count($buttons->getComponents()) > 1) ? TRUE : FALSE;
	}

	/**
	 * @param $id
	 *
	 * @return Nette\Forms\Controls\Checkbox
	 */
	public function createRowCheckbox($id)
	{
		/** @var Nette\Forms\Controls\CheckboxList $checkBoxList */
		$checkBoxList = $this['dataGridForm']['rows'];

		$items = $checkBoxList->getItems();
		$items = array_merge($items, [$id]);

		$checkBoxList->setItems($items);

		return $checkBoxList->getControlPart($id);
	}

	/**
	 * @return int|null
	 */
	public function getActiveRowForm()
	{
		return $this->activeRowForm;
	}

	/**
	 * @return bool
	 */
	public function hasActiveRowForm()
	{
		return $this->activeRowForm !== NULL ? TRUE : FALSE;
	}

	/**
	 * Sets default filtering
	 *
	 * @param array $filter
	 *
	 * @return $this
	 */
	public function setDefaultFilter(array $filter)
	{
		$this->defaultFilter = array_merge($this->defaultFilter, $filter);

		return $this;
	}

	/**
	 * Get all filters components
	 *
	 * @return array
	 */
	public function getFilters()
	{
		return $this->hasFilters()
			? $this->getComponent(Filters\Filter::ID)->getComponents()
			: NULL;
	}

	/**
	 * Returns filter component by its name
	 *
	 * @param string $name
	 * @param bool $need
	 *
	 * @return Filters\IFilter
	 */
	public function getFilter($name, $need = TRUE)
	{
		return $this->hasFilters()
			? $this->getComponent(Filters\Filter::ID)->getComponent($name, $need)
			: NULL;
	}

	/**
	 * Returns actual filter values
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function getActualFilter($key = NULL)
	{
		$filter = $this->filter ? $this->filter : $this->defaultFilter;

		return $key && isset($filter[$key]) ? $filter[$key] : $filter;
	}

	/**
	 * Check if filter is registered
	 *
	 * @param bool $useCache
	 *
	 * @return bool
	 */
	public function hasFilters($useCache = TRUE)
	{
		$hasFilters = $this->hasFilters;

		if ($hasFilters === NULL || $useCache === FALSE) {
			$container = $this->getComponent(Filters\Filter::ID, FALSE);
			$hasFilters = $container && count($container->getComponents()) > 0;
			$this->hasFilters = $useCache ? $hasFilters : NULL;
		}

		return $hasFilters;
	}

	/**
	 * Set data grid default sorting
	 *
	 * @param array $defaultSort
	 *
	 * @return $this
	 */
	public function setDefaultSort(array $defaultSort)
	{
		$this->defaultSort = array_merge($this->defaultSort, $defaultSort);

		return $this;
	}

	/**
	 * Get columns default sorting for DataTables settings
	 *
	 * @return array
	 */
	protected function getDefaultSort()
	{
		$defaultSort = [];

		if (count($this->defaultSort)) {
			$index = $this->hasGlobalButtons() || $this->hasRowButtons() ? 1 : 0;

			foreach ($this->getColumns() as $column) {
				if (array_key_exists($column->getName(), $this->defaultSort) && $column->isSortable()) {
					$defaultSort[] = [$index, $this->defaultSort[$column->getName()]];
				}

				$index++;
			}
		}

		return $defaultSort;
	}

	/**
	 * Sets a model that implements the interface DataTables\DataSources\IDataSource or data-source object
	 *
	 * @param mixed $model
	 * @param bool $forceWrapper
	 *
	 * @throws Exceptions\InvalidArgumentException
	 *
	 * @return $this
	 */
	public function setModel($model, $forceWrapper = FALSE)
	{
		$this->model = $model instanceof DataSources\IDataSource && $forceWrapper === FALSE
			? $model
			: new DataSources\Model($model);

		return $this;
	}

	/**
	 * @return DataSources\Model
	 */
	public function getModel()
	{
		return $this->model;
	}

	/**
	 * Get table data
	 *
	 * @throws Exceptions\NoDataSourceException
	 * @throws Exceptions\UnknownColumnException
	 * @throws Exceptions\InvalidFilterException
	 */
	public function handleGetData()
	{
		// Check if data source is set
		if (!$this->model) {
			throw new Exceptions\NoDataSourceException("Data source model not set yet, please use method \$grid->setModel().");
		}

		// Get total rows count
		$filteredTotal = $total = $this->getDataCount();

		// Init output collection
		$data = new Utils\ArrayHash;

		// Flag to keep consistent data
		$data->draw = $this->httpRequest->getQuery('draw');

		// Total records count from data source
		$data->recordsTotal = $total;

		// Filtered records count from data source
		$data->recordsFiltered = $filteredTotal;


		// If data are processed as server side (loaded on demand)
		if ($this->useServerSide()) {
			// DataTables params
			$columns = $this->httpRequest->getQuery('columns', []);    // Columns from DataTables
			$displayStart = $this->httpRequest->getQuery('start', 0);        // Limit start
			$displayLength = $this->httpRequest->getQuery('length', 20);    // Limit count
			$ordering = $this->httpRequest->getQuery('order', []);    // Data ordering
			$search = $this->httpRequest->getQuery('search', []);    // Global data search

			// Process sorting
			foreach ($ordering as $columnOrder) {
				if (isset($columns[$columnOrder['column']]) AND ($columnName = $columns[$columnOrder['column']]['name']) AND
					($column = $this->getColumn($columnName, FALSE))
				) {
					$this->sort[$column->getName()] = $columnOrder['dir'];
				}
			}

			// Apply sorting to data source
			$this->applySorting();

			// Global filtering
			if (!empty($search['value'])) {
				$value = addslashes($search['value']);

				foreach ($columns as $index => $column) {

				}
			}

			// Columns filtering
			foreach ($columns as $column) {
				// If filter is set...
				if (isset($this->filter[$column['name']])) {
					//...clean it
					unset($this->filter[$column['name']]);
				}

				// Search value is set and not empty
				if (isset($column['search']['value']) && $column['search']['value'] !== '' && $column['search']['value'] !== NULL) {
					$value = (string) $column['search']['value'];

					// Check if provided column have active filter
					if (($column = $this->getColumn($column['name'], FALSE)) AND $column->hasFilter()) {
						// Apply filter
						$this->filter[$column->getName()] = $column->getFilter()->changeValue($value);
					}
				}
			}

			// Apply columns
			$this->applyFiltering();

			// Update filtered records count
			$data->recordsFiltered = $this->getDataCount();

			// Set limits
			$this->model->limit($displayStart, $displayLength);
		}

		// Format rows data to DataTables format
		$data->data = $this->applyRowFormatting($this->model->getData());

		// Send formatted data to output
		$this->getPresenter()->sendJson($data);
	}

	/**
	 * @param array $rows
	 */
	public function redrawRows(array $rows)
	{
		// If request is done by ajax...
		if ($this->getPresenter()->isAjax()) {
			// Records collector
			$records = [];

			foreach ($rows as $row) {
				$records[$this->getRowIdentifier($row)] = $this->model->getRow($this->getRowIdentifier($row));
			}

			// Validate back all data grid snippets
			$this->redrawControl(NULL, FALSE);

			// Format rows data to DataTables format & put them to payload
			$this->getPresenter()->payload->rows = $this->applyRowFormatting($records);
			// Perform full redraw of data tables?
			$this->getPresenter()->payload->fullRedraw = $this->fullRedraw;

			// Classic request...
		} else {
			// ...do normal redirect
			$this->redirect('this');
		}
	}

	/**
	 * @return int
	 *
	 * @throws Exceptions\NoDataSourceException
	 */
	public function getDataCount()
	{
		// Check if data source is set
		if (!$this->model) {
			throw new Exceptions\NoDataSourceException("DataSource not set yet.");
		}

		$count = $this->model->getCount();

		return $count;
	}

	/**
	 * Store table state
	 */
	public function handleSaveState()
	{
		// Get data to save
		$data = $this->httpRequest->getPost();

		// Store table settings
		$this->stateSaver->saveState($this->lookupPath('Nette\Application\UI\Presenter'), $data);

		$this->getPresenter()->sendJson($data);
	}

	/**
	 * Reload table state
	 */
	public function handleLoadState()
	{
		// Load table settings
		$data = $this->stateSaver->loadState($this->lookupPath('Nette\Application\UI\Presenter') . $this->getName());

		$this->getPresenter()->sendJson($data);
	}

	/**
	 * Set table settings state saver
	 *
	 * @param StateSavers\IStateSaver $stateSaver
	 *
	 * @return $this
	 */
	public function setSateSaver(StateSavers\IStateSaver $stateSaver)
	{
		$this->stateSaver = $stateSaver;

		return $this;
	}

	/**
	 * @return UI\Form
	 */
	protected function createComponentDataGridForm()
	{
		$form = new UI\Form;
		$form
			// Data grid form is handled by post
			->setMethod('post')
			// Set translator from grid to form
			->setTranslator($this->getTranslator());

		$form->addContainer(Components\Buttons\Button::ID);
		$form[Components\Buttons\Button::ID]->addSubmit('send', 'Save')
			->getControlPrototype()
			->addClass('js-data-grid-editable');

		$form->addContainer('filters');
		$form['filters']->addSubmit('send', 'Filter')
			->setValidationScope(FALSE);
		$form['filters']->addText('fullGridSearch', 'Search:');

		$globalAction = $form->addContainer(Components\Actions\Button::ID);
		$globalAction->addSelect('name', 'Marked:');
		$globalAction->addSubmit('send', 'Confirm')
			->setValidationScope(FALSE)
			->getControlPrototype()
			->addData('select', $globalAction['name']->getControl()->name);

		$form->addCheckboxList('rows')
			->getControlPrototype()
			->addAttributes([
				'class'   => 'js-data-grid-action-checkbox',
				'checked' => FALSE
			]);

		$form->onSuccess[] = function (UI\Form $form, $values) {
			$this->processGridForm($form, $values);
		};

		return $form;
	}

	/**
	 * @param UI\Form $form
	 * @param array $values
	 */
	public function processGridForm(UI\Form $form, $values)
	{
		/**
		 * Get selected rows
		 */

		try {
			$rows = [];

			foreach ($this->httpRequest->getPost('rows') as $id) {
				if ($row = $this->model->getRow($id)) {
					$rows[] = $row;
				}
			}

			// Check if some rows were selected
			if (!count($rows)) {
				throw new Exceptions\NoRowSelectedException("No rows selected.");
			}

		} catch (Exceptions\NoRowSelectedException $ex) {
			$this->flashMessage('No rows selected.', 'error');

			// If request is done by ajax...
			if ($this->getPresenter()->isAjax()) {
				// Validate back all data grid snippets
				$this->redrawControl(NULL, FALSE);

				return;

			} else {
				$this->redirect('this');
			}
		}

		/**
		 * Global actions...
		 */

		// Check for custom action submitting
		if ($this->hasGlobalButtons()) {
			try {
				// Check all action buttons...
				foreach ($this->getComponent(Components\Actions\Button::ID, FALSE)->getComponents() as $action) {
					// ...and if form was submitted by this button...
					if ($form[Components\Actions\Button::ID][$action->getName()]->isSubmittedBy()) {
						// ...call button callback
						call_user_func($action->getCallback(), $rows);

						// Redraw updated rows
						$this->redrawRows($rows);
					}
				}

				// Form is submitted by global action submit button
				if ($form[Components\Actions\Button::ID]['send']->isSubmittedBy()) {
					if ($action = $this->getComponent(Components\Actions\Button::ID, FALSE)->getComponent($values[Components\Actions\Button::ID]['name'], FALSE)) {
						call_user_func($action->getCallback(), $rows);

						// Redraw updated rows
						$this->redrawRows($rows);

					} else {
						throw new Exceptions\UnknownActionException("Unknown action submitted.");
					}
				}

				// Action does not exists
			} catch (Exceptions\UnknownActionException $ex) {

				// Callback is not set
			} catch (Exceptions\UnknownActionCallbackException $ex) {

			}
		}

		/**
		 * Row form action...
		 */

		// For row action we need only one row
		$row = current($rows);

		foreach ($this->getColumns() as $column) {
			// If column is action column
			if ($column instanceof Columns\Action) {
				// Get all column buttons
				foreach ($column->getButtons() as $button) {
					// ...and if form was submitted by this button...
					if ($form[Components\Buttons\Button::ID][$button->getName()]->isSubmittedBy()) {
						// ...call button callback
						call_user_func($button->getCallback(), $row);

						// Redraw updated row
						$this->redrawRows([$row]);
					}
				}
			}
		}

		// Check if row form was submitted...
		if ($form[Components\Buttons\Button::ID]['send']->isSubmittedBy()) {
			// Call row edit callback
			call_user_func($this->rowFormCallback, $row, (array) $values);

			// Redraw updated row
			$this->redrawRows([$row]);
		}
	}

	/**
	 * Apply column filtering to the model
	 *
	 * @return $this
	 */
	protected function applyFiltering()
	{
		$conditions = [];

		if ($this->getActualFilter()) {
			$this['dataGridForm']->setDefaults([Filters\Filter::ID => $this->getActualFilter()]);

			foreach ($this->getActualFilter() as $column => $value) {
				if ($component = $this->getFilter($column, FALSE)) {
					if ($condition = $component->__getCondition($value)) {
						$conditions[] = $condition;
					}

				} else {
					trigger_error("Filter with name '$column' does not exist.", E_USER_NOTICE);
				}
			}
		}

		// Apply filter to the data model
		$this->model->filter($conditions);

		return $this;
	}

	/**
	 * Apply sorting to the model
	 *
	 * @return $this
	 */
	protected function applySorting()
	{
		$sort = [];

		$this->sort = $this->sort ? $this->sort : $this->defaultSort;

		foreach ($this->sort as $column => $dir) {
			$component = $this->getColumn($column, FALSE);
			if (!$component) {
				if (!isset($this->defaultSort[$column])) {
					trigger_error("Column with name '$column' does not exist.", E_USER_NOTICE);
					break;
				}

			} else if (!$component->isSortable()) {
				if (isset($this->defaultSort[$column])) {
					$component->setSortable();

				} else {
					trigger_error("Column with name '$column' is not sortable.", E_USER_NOTICE);
					break;
				}
			}

			if (!in_array($dir, [Columns\IColumn::ORDER_ASC, Columns\IColumn::ORDER_DESC])) {
				if ($dir == '' && isset($this->defaultSort[$column])) {
					unset($this->sort[$column]);
					break;
				}

				trigger_error("Dir '$dir' is not allowed.", E_USER_NOTICE);

				break;
			}

			$sort[$component ? $component->getColumn() : $column] = $dir == Columns\IColumn::ORDER_ASC ? 'ASC' : 'DESC';
		}

		if ($sort) {
			$this->model->sort($sort);
		}

		return $this;
	}

	/**
	 * @param array $records
	 *
	 * @return array
	 */
	protected function applyRowFormatting(array $records)
	{
		// Formatted collection
		$collection = [];

		// Process all data from data source
		foreach ($records as $id => $record) {
			if ($record == NULL) {
				$collection[$id] = NULL;

				continue;
			}

			$row = new Utils\ArrayHash;

			// DataGrid form default values
			$defaults = [];

			foreach ($this->getColumns() as $column) {
				if ($column->isEditable()) {
					$defaults[$column->getName()] = $record->{$column->getName()};
				}
			}

			// Store form default values from row
			$this['dataGridForm'][Components\Buttons\Button::ID]->setDefaults($defaults);

			// Row identifier
			$row->DT_RowId = 'row_' . $this->getRowIdentifier($record);

			// Columns counter for non-server side processing
			$counter = 0;

			if ($this->hasGlobalButtons() || $this->hasRowButtons()) {
				$row[$this->useServerSide() ? 'rowSelection' : $counter] = (string) $this->createRowCheckbox($this->getRowIdentifier($record));

				$counter++;
			}

			foreach ($this->getColumns() as $index => $column) {
				if ($this->isEditable() && $column->isEditable() && $this->activeRowForm == $this->getRowIdentifier($record)) {
					// Add edit column data to output
					$row[$this->useServerSide() ? $column->getName() : $counter] = $this['dataGridForm'][Components\Buttons\Button::ID][$column->getColumn()]->getControl();

				} else {
					// Add column data to output
					ob_start();
					$column->render($record);
					$row[$this->useServerSide() ? $column->getName() : $counter] = ob_get_clean();
				}

				$counter++;
			}

			// Add row to output collection
			$collection[$id] = $row;
		}

		return $collection;
	}

	/**
	 * @param Localization\ITranslator $translator
	 *
	 * @return $this
	 */
	public function setTranslator(Localization\ITranslator $translator)
	{
		$this->translator = $translator;

		return $this;
	}

	/**
	 * @return Localization\ITranslator|null
	 */
	public function getTranslator()
	{
		if ($this->translator instanceof Localization\ITranslator) {
			return $this->translator;
		}

		return NULL;
	}

	/**
	 * Change default control template path
	 *
	 * @param string $templateFile
	 *
	 * @return $this
	 *
	 * @throws Exceptions\FileNotFoundException
	 */
	public function setTemplateFile($templateFile)
	{
		// Check if template file exists...
		if (!is_file($templateFile)) {
			// Remove extension
			$template = basename($templateFile, '.latte');

			// ...check if extension template is used
			if (is_file(__DIR__ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . $template . '.latte')) {
				$templateFile = __DIR__ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . $template . '.latte';

			} else {
				// ...if not throw exception
				throw new Exceptions\FileNotFoundException('Template file "' . $templateFile . '" was not found.');
			}
		}

		$this->templateFile = $templateFile;

		return $this;
	}

	/**
	 * @param mixed $row
	 *
	 * @return mixed|NULL
	 */
	private function getRowIdentifier($row)
	{
		// Row identifier
		if (method_exists($row, 'get' . ucfirst($this->getPrimaryKey()))) {
			return (string) call_user_func([$row, 'get' . ucfirst($this->getPrimaryKey())]);
		}

		return NULL;
	}
}
