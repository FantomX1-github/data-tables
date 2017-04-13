<?php
/**
 * Component.php
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
use Nette\Bridges;
use Nette\ComponentModel;
use Nette\Forms;
use Nette\Http;
use Nette\Utils;
use Nette\Localization;

use IPub\DataTables\Columns;
use IPub\DataTables\Components;
use IPub\DataTables\DataSources;
use IPub\DataTables\Exceptions;
use IPub\DataTables\Filters;
use IPub\DataTables\StateSavers;

/**
 * DataTables grid control
 *
 * @package        iPublikuj:DataTables!
 * @subpackage     Components
 *
 * @author         Adam Kadlec <adam.kadlec@ipublikuj.eu>
 *
 * @method onBeforeConfigure(UI\Control $component)
 * @method onAfterConfigure(UI\Control $component)
 *
 * @property-read Bridges\ApplicationLatte\Template $template
 */
class Control extends Settings
{
	use Components\TColumns;
	use Components\TFilters;

	/**
	 * @var \Closure[]
	 */
	public $onBeforeConfigure = [];

	/**
	 * @var \Closure[]
	 */
	public $onAfterConfigure = [];

	/**
	 * @var DataSources\IModel
	 */
	private $model = NULL;

	/**
	 * @var StateSavers\IStateSaver|NULL
	 */
	private $stateSaver = NULL;

	/**
	 * @var int|NULL
	 */
	private $activeRowForm;

	/**
	 * @var array
	 */
	private $defaultSort = [];

	/**
	 * @var array
	 */
	public $sort = [];

	/**
	 * @var callback
	 */
	private $rowFormCallback;

	/**
	 * @var Localization\ITranslator
	 */
	private $translator;

	/**
	 * @var Http\IRequest
	 */
	private $httpRequest;

	/**
	 * @var bool
	 */
	private $ajax = TRUE;

	/**
	 * @var bool
	 */
	private $fullRedraw = FALSE;

	/**
	 * @var string|NULL
	 */
	private $templateFile = NULL;

	/**
	 * @param Http\IRequest $httpRequest
	 *
	 * @return void
	 */
	public function injectHttpRequest(Http\IRequest $httpRequest)
	{
		$this->httpRequest = $httpRequest;
	}

	/**
	 * @param StateSavers\IStateSaver $stateSaver
	 *
	 * @return void
	 */
	public function injectStateSaver(StateSavers\IStateSaver $stateSaver)
	{
		$this->stateSaver = $stateSaver;
	}

	/**
	 * @param Localization\ITranslator $translator
	 *
	 * @return void
	 */
	public function injectTranslator(Localization\ITranslator $translator = NULL)
	{
		$this->translator = $translator;
	}

	/**
	 * @param ComponentModel\IComponent $presenter
	 *
	 * @return void
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

			foreach ($this->getGlobalButtons() as $name => $action) {
				$actions[$name] = $action->getAction();
			}

			/** @var Forms\Container $globalButtonsContainer */
			$globalButtonsContainer = $this['gridForm'][Components\Actions\IButton::ID];
			/** @var Forms\Controls\SelectBox $actionSelect */
			$actionSelect = $globalButtonsContainer['name'];
			$actionSelect->setItems($actions);
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
	 *
	 * @return void
	 */
	protected function beforeRender()
	{
		// Check if data are loaded via ajax
		if ($this->hasEnabledAjaxSource()) {
			$rows = NULL;

		// Or are loaded in render process
		} else {
			$rows = $this->getModel()->getRows();
		}

		// Add data to template
		$this->template->add('results', $this->getModel()->getCount());
		$this->template->add('columns', $this->getColumns());
		$this->template->add('columnsCount', $this->getColumnsCount());
		$this->template->add('filters', $this->getFilters());
		$this->template->add('primaryKey', $this->getModel()->getPrimaryKey());
		$this->template->add('rows', $rows);
		$this->template->add('settings', $this->formatSettings());
		$this->template->add('useServerSide', $this->hasEnabledServerSide());
		$this->template->add('useAjaxSource', $this->hasEnabledAjaxSource());

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

	/**
	 * @return void
	 */
	public function render()
	{
		$this->beforeRender();

		// Render component template
		$this->template->render();
	}

	/**
	 * Enable ajax requests
	 *
	 * @return void
	 */
	public function enableAjax()
	{
		$this->ajax = TRUE;
	}

	/**
	 * Disable ajax requests
	 *
	 * @return void
	 */
	public function disableAjax()
	{
		$this->ajax = FALSE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasEnabledAjax() : bool
	{
		return $this->ajax;
	}

	/**
	 * Enable table full redraw
	 *
	 * @return void
	 */
	public function enableFullRedraw()
	{
		$this->fullRedraw = TRUE;
	}

	/**
	 * Disable table full redraw
	 *
	 * @return void
	 */
	public function disableFullRedraw()
	{
		$this->fullRedraw = FALSE;
	}

	/**
	 * @return bool
	 */
	public function hasRowButtons() : bool
	{
		/** @var ComponentModel\Container $buttonsContainer */
		$buttonsContainer = $this->getComponent(Components\Buttons\IButton::ID, FALSE);

		return ($buttonsContainer !== NULL && count($buttonsContainer->getComponents()) > 0) ? TRUE : FALSE;
	}

	/**
	 * @param string $name
	 * @param string|NULL $label
	 *
	 * @return Components\Actions\Button
	 *
	 * @throws Exceptions\DuplicateGlobalButtonException
	 */
	public function addGlobalButton(string $name, string $label = NULL) : Components\Actions\Button
	{
		/** @var ComponentModel\Container $buttonsContainer */
		$buttonsContainer = $this->getComponent(Components\Actions\IButton::ID, FALSE);

		if ($buttonsContainer !== NULL && $buttonsContainer->getComponent($name, FALSE)) {
			throw new Exceptions\DuplicateGlobalButtonException(sprintf('Global button "%s" already exists.', $name));
		}

		return new Components\Actions\Button($this, $name, $label);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getGlobalButtons() : array
	{
		/** @var ComponentModel\Container $buttonsContainer */
		$buttonsContainer = $this->getComponent(Components\Actions\IButton::ID, FALSE);

		return $buttonsContainer !== NULL ? $buttonsContainer->getComponents()->getArrayCopy() : [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getGlobalButton(string $name) : Components\Actions\IButton
	{
		/** @var ComponentModel\Container $buttonsContainer */
		$buttonsContainer = $this->getComponent(Components\Actions\IButton::ID);

		/** @var Components\Actions\IButton $globalButton */
		$globalButton = $buttonsContainer->getComponent($name);

		return $globalButton;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasGlobalButtons() : bool
	{
		/** @var ComponentModel\Container $buttonsContainer */
		$buttonsContainer = $this->getComponent(Components\Actions\IButton::ID, FALSE);

		return ($buttonsContainer !== NULL && count($buttonsContainer->getComponents()) >= 1) ? TRUE : FALSE;
	}

	/**
	 * @param string $id
	 *
	 * @return Utils\Html
	 */
	public function createRowCheckbox(string $id) : Utils\Html
	{
		/** @var Forms\Controls\CheckboxList $checkBoxList */
		$checkBoxList = $this['gridForm']['rows'];

		$items = $checkBoxList->getItems();
		$items = array_merge($items, [$id]);

		$checkBoxList->setItems($items);

		return $checkBoxList->getControlPart($id);
	}

	/**
	 * @return int|NULL
	 */
	public function getActiveRowForm()
	{
		return $this->activeRowForm;
	}

	/**
	 * @return bool
	 */
	public function hasActiveRowForm() : bool
	{
		return $this->activeRowForm !== NULL;
	}

	/**
	 * Set data grid default sorting
	 *
	 * @param array $defaultSort
	 *
	 * @return void
	 */
	public function setDefaultSort(array $defaultSort)
	{
		$this->defaultSort = array_merge($this->defaultSort, $defaultSort);
	}

	/**
	 * Get columns default sorting for DataTables settings
	 *
	 * @return array
	 */
	protected function getDefaultSort() : array
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
	 * @param string|NULL $primaryKey
	 * @param bool $forceWrapper
	 *
	 * @return void
	 */
	public function setModel($model, string $primaryKey = NULL, bool $forceWrapper = FALSE)
	{
		$forceWrapper = is_bool($primaryKey) ? $primaryKey : $forceWrapper;

		if ($model instanceof DataSources\IDataSource && $forceWrapper === FALSE) {
			$this->model = $model;

		} else {
			if ($primaryKey === NULL) {
				throw new Exceptions\InvalidArgumentException('Primary key for data source is missing');
			}

			$this->model = new DataSources\Model($model, $primaryKey);
		}
	}

	/**
	 * @param bool $needed
	 *
	 * @return DataSources\IModel|NULL
	 */
	public function getModel(bool $needed = TRUE)
	{
		// Check if data source is set
		if ($this->model === NULL && $needed) {
			throw new Exceptions\NoDataSourceException('Data source model not set yet, please use method \$grid->setModel().');
		}

		return $this->model;
	}

	/**
	 * Get table data
	 *
	 * @return void
	 *
	 * @throws Exceptions\NoDataSourceException
	 * @throws Exceptions\UnknownColumnException
	 * @throws Exceptions\InvalidFilterException
	 */
	public function handleGetData()
	{
		// Get total rows count
		$filteredTotal = $total = $this->getModel()->getCount();

		// Init output collection
		$data = new Utils\ArrayHash;

		// Flag to keep consistent data
		$data->draw = $this->httpRequest->getQuery('draw');

		// Total records count from data source
		$data->recordsTotal = $total;

		// Filtered records count from data source
		$data->recordsFiltered = $filteredTotal;

		// If data are processed as server side (loaded on demand)
		if ($this->hasEnabledServerSide()) {
			// DataTables params
			$columns = $this->httpRequest->getQuery('columns', []);					// Columns from DataTables
			$displayStart = (int) $this->httpRequest->getQuery('start', 0);		// Limit start
			$displayLength = (int) $this->httpRequest->getQuery('length', 20);		// Limit count
			$ordering = $this->httpRequest->getQuery('order', []);					// Data ordering
			$search = $this->httpRequest->getQuery('search', []);					// Global data search

			// Process sorting
			foreach ($ordering as $columnOrder) {
				if (
					isset($columns[$columnOrder['column']]) &&
					($columnName = $columns[$columnOrder['column']]['name']) &&
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
					if (($column = $this->getColumn($column['name'], FALSE)) && $column->hasFilter()) {
						// Apply filter
						$this->filter[$column->getName()] = $column->getFilter()->changeValue($value);
					}
				}
			}

			// Apply columns
			$this->applyFiltering();

			// Update filtered records count
			$data->recordsFiltered = $this->getModel()->getCount();

			// Set limits
			$this->getModel()->limit($displayStart, $displayLength);
		}

		// Format rows data to DataTables format
		$data->data = $this->applyRowFormatting($this->getModel()->getRows());

		// Send formatted data to output
		$this->getPresenter()->sendJson($data);
	}

	/**
	 * @param array $rows
	 *
	 * @return void
	 */
	public function redrawRows(array $rows)
	{
		// If request is done by ajax...
		if ($this->getPresenter()->isAjax()) {
			// Records collector
			$records = [];

			foreach ($rows as $row) {
				$records[$this->getModel()->getRowIdentifier($row)] = $this->getModel()->getRow($this->getModel()->getRowIdentifier($row));
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
	 * Store table state
	 *
	 * @return void
	 */
	public function handleSaveState()
	{
		// Get data to save
		$data = $this->httpRequest->getPost();

		// Store table settings
		$this->stateSaver->saveState($this->lookupPath(UI\Presenter::class), $data);

		$this->getPresenter()->sendJson($data);
	}

	/**
	 *
	 * @return void
	 */
	public function handleLoadState()
	{
		// Load table settings
		$data = $this->stateSaver->loadState($this->lookupPath(UI\Presenter::class) . $this->getName());

		$this->getPresenter()->sendJson($data);
	}

	/**
	 * Set table settings state saver
	 *
	 * @param StateSavers\IStateSaver $stateSaver
	 *
	 * @return void
	 */
	public function setSateSaver(StateSavers\IStateSaver $stateSaver)
	{
		$this->stateSaver = $stateSaver;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasStateSaver() : bool
	{
		return $this->stateSaver !== NULL;
	}

	/**
	 * @return UI\Form
	 */
	protected function createComponentGridForm() : UI\Form
	{
		$form = new UI\Form;
		// Data grid form is handled by post
		$form->setMethod(UI\Form::POST);

		if ($this->getTranslator() !== NULL) {
			// Set translator from grid to form
			$form->setTranslator($this->getTranslator());
		}

		/** @var Forms\Container $rowButtonsContainer */
		$rowButtonsContainer = $form->addContainer(Components\Buttons\IButton::ID);
		// Edit row submit button
		$rowButtonsContainer->addSubmit('send', 'Save')
			->getControlPrototype()
				->appendAttribute('class', 'js-data-grid-editable');

		/** @var Forms\Container $filtersContainer */
		$filtersContainer = $form->addContainer(Filters\IFilter::ID);
		$filtersContainer->addSubmit('send', 'Filter')
			->setValidationScope(FALSE);
		$filtersContainer->addText('fullGridSearch', 'Search:');

		/** @var Forms\Container $globalButtonsContainer */
		$globalButtonsContainer = $form->addContainer(Components\Actions\IButton::ID);
		$globalButtonsContainer->addSelect('name', 'Marked:');
		$globalButtonsContainer->addSubmit('send', 'Confirm')
			->setValidationScope(FALSE)
			->getControlPrototype()
				->data('select', $globalButtonsContainer['name']->getName());

		$form->addCheckboxList('rows')
			->getControlPrototype()
				->addAttributes([
					'class'   => 'js-data-grid-action-checkbox',
					'checked' => FALSE,
				]);

		$form->addContainer('rowForm');

		$form->onSuccess[] = function (UI\Form $form, $values) {
			$this->processGridForm($form, $values);
		};

		return $form;
	}

	/**
	 * @param UI\Form $form
	 * @param array $values
	 */
	public function processGridForm(UI\Form $form, array $values)
	{
		/**
		 * Get selected rows
		 */

		try {
			$rows = [];

			foreach ($this->httpRequest->getPost('rows') as $id) {
				if ($row = $this->getModel()->getRow($id)) {
					$rows[] = $row;
				}
			}

			// Check if some rows were selected
			if (!count($rows)) {
				throw new Exceptions\NoRowSelectedException('No rows selected.');
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
			/** @var Forms\Container $globalButtonsContainer */
			$globalButtonsFormContainer = $form[Components\Actions\IButton::ID];

			try {
				// Check all action buttons...
				foreach ($this->getGlobalButtons() as $globalButton) {
					/** @var Forms\Controls\SubmitButton $globalFormButton */
					$globalFormButton = $globalButtonsFormContainer[$globalButton->getName()];

					// ...and if form was submitted by this button...
					if ($globalFormButton->isSubmittedBy()) {
						// ...call button callback
						call_user_func($globalButton->getCallback(), $rows);

						// Redraw updated rows
						$this->redrawRows($rows);
					}
				}

				/** @var Forms\Controls\SubmitButton $globalFormSubmitButton */
				$globalFormSubmitButton = $globalButtonsFormContainer['send'];

				// Form is submitted by global action submit button
				if ($globalFormSubmitButton->isSubmittedBy()) {
					/** @var Components\Actions\IButton $globalButton */
					$globalButton = $this->getGlobalButton($values[Components\Actions\IButton::ID]['name']);

					call_user_func($globalButton->getCallback(), $rows);

					// Redraw updated rows
					$this->redrawRows($rows);
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
					if ($form[Components\Buttons\IButton::ID][$button->getName()]->isSubmittedBy()) {
						// ...call button callback
						call_user_func($button->getCallback(), $row);

						// Redraw updated row
						$this->redrawRows([$row]);
					}
				}
			}
		}

		// Check if row form was submitted...
		if ($form[Components\Buttons\IButton::ID]['send']->isSubmittedBy()) {
			// Call row edit callback
			call_user_func($this->rowFormCallback, $row, (array) $values);

			// Redraw updated row
			$this->redrawRows([$row]);
		}
	}

	/**
	 * Apply sorting to the model
	 *
	 * @return $this
	 */
	private function applySorting()
	{
		$sort = [];

		$this->sort = $this->sort ? $this->sort : $this->defaultSort;

		foreach ($this->sort as $column => $dir) {
			/** @var Columns\IColumn|NULL $component */
			$component = $this->getColumn($column, FALSE);

			if ($component === NULL) {
				if (!isset($this->defaultSort[$column])) {
					throw new Exceptions\InvalidStateException(sprintf('Column with name "%s" does not exist.', $column));
				}

			} else if (!$component->isSortable()) {
				if (isset($this->defaultSort[$column])) {
					$component->enableSortable();

				} else {
					throw new Exceptions\InvalidStateException(sprintf('Column with name "%s" does not exist.', $column));
				}
			}

			if (!in_array($dir, [Columns\IColumn::ORDER_ASC, Columns\IColumn::ORDER_DESC], TRUE)) {
				if ($dir === '' && isset($this->defaultSort[$column])) {
					unset($this->sort[$column]);
					break;
				}

				throw new Exceptions\InvalidStateException(sprintf('Dir "%s" is not allowed.', $dir));
			}

			$sort[$component ? $component->getColumn() : $column] = $dir === Columns\IColumn::ORDER_ASC ? 'ASC' : 'DESC';
		}

		if ($sort) {
			$this->getModel()->sort($sort);
		}
	}

	/**
	 * @param array $records
	 *
	 * @return array
	 */
	private function applyRowFormatting(array $records) : array
	{
		// Formatted collection
		$collection = [];

		// Process all data from data source
		foreach ($records as $id => $record) {
			if ($record === NULL) {
				$collection[$id] = NULL;

				continue;
			}

			$row = new Utils\ArrayHash;

			// Grid form default values
			$defaults = [];

			foreach ($this->getColumns() as $column) {
				if ($column->isEditable()) {
					$defaults[$column->getName()] = $this->getModel()->getColumnValue($record, $column->getName());
				}
			}

			// Store form default values from row
			$this['gridForm']['rowForm']->setDefaults($defaults);

			// Row identifier
			$row->DT_RowId = 'row_' . $this->getModel()->getRowIdentifier($record);

			// Columns counter for non-server side processing
			$counter = 0;

			if ($this->hasGlobalButtons() || $this->hasRowButtons()) {
				$row[$this->hasEnabledServerSide() ? 'rowSelection' : $counter] = (string) $this->createRowCheckbox($this->getModel()->getRowIdentifier($record));

				$counter++;
			}

			foreach ($this->getColumns() as $index => $column) {
				if ($this->isEditable() && $column->isEditable() && $this->activeRowForm === $this->getModel()->getRowIdentifier($record)) {
					// Add edit column data to output
					$row[$this->hasEnabledServerSide() ? $column->getName() : $counter] = $this['gridForm']['rowForm'][$column->getColumn()]->getControl();

				} else {
					// Add column data to output
					ob_start();
					$column->render($record);
					$row[$this->hasEnabledServerSide() ? $column->getName() : $counter] = ob_get_clean();
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
	 * @return void
	 */
	public function setTranslator(Localization\ITranslator $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @return Localization\ITranslator|NULL
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
	 * @return void
	 *
	 * @throws Exceptions\FileNotFoundException
	 */
	public function setTemplateFile(string $templateFile)
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
				throw new Exceptions\FileNotFoundException(sprintf('Template file "%s" was not found.', $templateFile));
			}
		}

		$this->templateFile = $templateFile;
	}
}
