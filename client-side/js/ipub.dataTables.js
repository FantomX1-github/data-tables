/**
 * @package		iPublikuj:Framework!
 * @copyright	Copyright (C) 2014. All rights reserved.
 * @license		http://www.ipublikuj.eu
 * @author		Adam Kadlec (http://www.fastybird.com)
 *
 * For the full copyright and license information, please view
 * the file LICENSE.md that was distributed with this source code.
 */

/**
 * Client-side script for iPublikuj:DataTables!
 *
 * @author		Adam Kadlec (http://www.fastybird.com)
 * @package		iPublikuj:Framework!
 * @version		1.0
 *
 * @param {jQuery} $ (version > 1.7)
 * @param {Window} window
 * @param {Document} document
 * @param {Location} location
 * @param {Navigator} navigator
 */
;(function($, window, document, location, navigator) {
	/* jshint laxbreak: true, expr: true */
	"use strict";

	var IPub = window.IPub || {};

	IPub.DataTables = {}

	/**
	 * Data grid definition
	 *
	 * @param {jQuery} $element
	 * @param {Object} options
	 */
	IPub.DataTables.Grid = function($element, options)
	{
		this.$element	= $element;

		this.name		= this.$element.attr('id');
		this.options	= $.extend($.fn.ipubDT.defaults, options, this.$element.data('settings') || {});
	};

	IPub.DataTables.Grid.prototype =
	{
		// Initial function.
		init: function()
		{
			this.dataTablesInit();

			// Ainit ajax extension
			this.ajax		= new IPub.DataTables.Ajax(this).init();
			// Init table actions extension
			this.actions	= new IPub.DataTables.Actions(this).init();

			this.initFilters();
			this.initCheckNumeric();
			this.onInit();

			return this;
		},

		// Init DataTables extension
		dataTablesInit: function()
		{
			var that = this;

			// Preselection callback
			this.options.rowCallback  = function(row, data) {
				if (that.actions && $.inArray(data.DT_RowId, that.actions.selected) !== -1) {
					that.actions.selectOneRow($(row), true);
				}
			};

			// If state have to be saved in session
			if (this.options.stateDuration == -1 ) {
				// Save state into system
				this.options.stateSaveCallback = function (settings, data) {
					$.post(that.options.saveSateLink, data);
				};

				// Load state from system
				this.options.stateLoadCallback = function (settings) {
					$.get(that.options.loadSateLink, null);
				};
			}

			// Attach DataTables to table
			this.$table = $('table', this.$element).DataTable(this.options);
		},

		// Attach a change handler to filter elements (select, checkbox).
		initFilters: function()
		{
			var that = this;

			// One column data grid search
			$('select[name*="filters"], input[name*="filters"][type=checkbox]', this.$element)
				.off('change.ipub.dt')
				.on('change.ipub.dt', $.proxy(this.applyFilter, this));

			$('input[name*="filters"][type=text]', this.$element)
				.off('keyup.ipub.dt')
				.on('keyup.ipub.dt', $.proxy(this.applyFilter, this));

			// Full data grid search
			$('input[name*="filters[fullGridSearch]"]', this.$element)
				.off('keyup.ipub.dt')
				.on('keyup.ipub.dt', $.proxy(function(event){
					// Deselect all rows
					that.actions.deselectAllRows();

					// Process searching on all columns
					that.$table
						.search(event.target.value)
						.draw();
				}, this));
		},

		// Checking numeric input.
		initCheckNumeric: function()
		{
			$('input[name*="filters"].number', this.$element)
				.off('keyup.ipub.dt')
				.on('keyup.ipub.dt', function() {
					var value = $(this).val(),
						pattern = new RegExp(/[^<>=\\.\\,\-0-9]+/g);

					pattern.test(value) && $(this).val(value.replace(pattern, ''));
				});
		},

		onInit: function() {},

		// Sending filter form
		applyFilter: function(event)
		{
			// Deselect all rows
			this.actions.deselectAllRows();

			// Find column index
			var colIndex = helpers.findColumnIndex(this.options.columns, helpers.extractColumnNameFromInput($(event.target)));

			// If index exists...
			if (colIndex) {
				// ...process searching on column
				this.$table
					.column(colIndex)
					.search(event.target.tagName == 'select' ?
						event.target.options[event.target.selectedIndex].value :
						event.target.value
					)
					.draw();
			}
		},

		// Update table rows
		updateRows: function(rows)
		{
			var that = this;

			$.each(rows, function(index, row) {
				// Get updated row element
				var $row = $('#row_' + index);

				// Check if row exists
				if ($row.length) {
					// Check if row was deleted
					if (row == null) {
						// Remove deleted row
						that.$table
							.row($row)
							.remove();

					// Row was updated
					} else {
						// Update row data
						that.$table
							.row($row)
							.data(row);
					}

					$row
						// Remove loading processing class from row
						.removeClass('processing')
						// Deselect row checkbox
						.find('input.js-data-grid-action-checkbox')
							.prop('checked', false);
				}
			});
		}
	}

	/**
	 * Actions definition
	 *
	 * @param {IPub.DataTables} DataTables
	 */
	IPub.DataTables.Actions = function(DataTables)
	{
		this.dataTables = DataTables;
	};

	IPub.DataTables.Actions.prototype =
	{
		// Row checkbox selector
		selectorOne: 'td [type=checkbox].js-data-grid-action-checkbox',

		// All rows checkbox selector
		selectorAll: 'th [type=checkbox].js-select-all',

		// Storage for all selected rows
		selected : [],

		// Storage for last selected row
		$last: null,

		/**
		 * Actions initialisation
		 *
		 * @returns {*}
		 */
		init: function()
		{
			if (!$(this.selectorAll, this.dataTables.$element).length) {
				return null;
			}

			// Bind click events
			this.bindClickOnCheckbox();
			this.bindClickOnRow();
			this.bindClickOnTableCheckbox();
			this.bindClickOnButton();

			// Bind change events
			this.bindChangeOnCheckbox();
			this.bindChangeOnSelect();

			// Bind submit events
			this.bindSubmitForm();

			return this;
		},

		/**
		 * Click on checkbox with shift support
		 */
		bindClickOnCheckbox: function()
		{
			var that = this;

			$('tbody', this.dataTables.$element)
				.off('click.ipub.dt', this.selectorOne)
				.on('click.ipub.dt', this.selectorOne, function(event, data) {
					if(event.shiftKey || (data && data.shiftKey)) {
						var boxes	= $(that.selectorOne, that.dataTables.$element),
							start	= boxes.index(this),
							end		= boxes.index(that.$last);

						boxes.slice(Math.min(start, end), Math.max(start, end))
							.prop('checked', that.$last.checked)
							.trigger('change');
					}

					that.$last = this;
				});
		},

		/**
		 * Click on one row
		 */
		bindClickOnRow: function()
		{
			var that = this;

			$('tbody', this.dataTables.$element)
				.off('click.ipub.dt', 'td:not(.js-data-grid-row-checkbox)')
				.on('click.ipub.dt', 'td:not(.js-data-grid-row-checkbox)', function(event) {
					var $row = $(this).closest('tr');

					if ($row.hasClass('js-data-grid-edit')) {
						return;
					}

					$(that.selectorOne, $row).click();
				});
		},

		/**
		 * Click on all rows select & deselect checkbox
		 */
		bindClickOnTableCheckbox: function()
		{
			var that = this;

			$(this.selectorAll, this.dataTables.$element)
				.off('click.ipub.dt')
				.on('click.ipub.dt', function() {
					var selected	= $(this).prop('checked'),
						$nodes		= that.dataTables.$table.rows().nodes().to$();

					$nodes.each(function(){
						$(that.selectorOne, $(this)).prop('checked', selected);

						that.changeRow($(this), selected);
					});

					// Call event triggers
					that.dataTables.$element.trigger(selected === true ? 'ipub.dt.allRowsSelected':'ipub.dt.allRowsDeselected');
				});
		},

		/**
		 *
		 */
		bindChangeOnCheckbox: function()
		{
			var that = this;

			$('tbody', this.dataTables.$element)
				.off('change.ipub.dt', this.selectorOne)
				.on('change.ipub.dt', this.selectorOne, function(event, data) {
					$.proxy(that.changeRow, that)($(this).closest('tr'), $(this).prop('checked'));
				});
		},

		/**
		 * Bind change event on global action select box for autosumitting
		 */
		bindChangeOnSelect: function()
		{
			var that = this;

			$('select[name^="action"]', this.dataTables.$element)
				.off('change.ipub.dt')
				.on('change.ipub.dt', function() {
					$(this).val() && $('[type=submit][name^="action[send]"]', that.dataTables.$element).click();
				});
		},

		/**
		 * Attach a click handler to action anchors
		 */
		bindClickOnButton: function()
		{
			var that = this;

			// Global action button
			$('[type=submit][name^="action"], a.js-data-grid-action-button', this.dataTables.$element)
				.off('click.ipub.dt')
				.on('click.ipub.dt', function(event){
					event.preventDefault();

					// Cache button
					var $button = $(this),
						index;

					$.proxy(that.onSubmit, that);

					// Get form data
					var $data = $('form', that.dataTables.$element).serializeArray();

					if ($button.data('actionName') && $button.data('actionValue')) {
						// Add info about pushed button
						$data.push({ name: $button.data('actionName'), value: $button.data('actionValue') });

					} else {
						// Add info about pushed button
						$data.push({ name: this.name, value: this.value });
					}

					// Add class for indicate processing row
					for (index = 0; index < that.selected.length; ++index) {
						$('#' + that.selected[index]).addClass('processing');
					}

					// Make ajax call
					that.dataTables.ajax.doRequest($('form', that.dataTables.$element).prop('action'), $data, that.selected, event);

					// Deselect all rows
					that.deselectAllRows();
				});

			// Row action button
			$('tbody', this.$element)
				.off('click.nette', '[type=submit][name^="rowAction"]:not([disabled]), a.js-data-grid-row-button:not([disabled])')
				.off('click.ipub.dt', '[type=submit][name^="rowAction"]:not([disabled]), a.js-data-grid-row-button:not([disabled])')
				.on('click.ipub.dt', '[type=submit][name^="rowAction"]:not([disabled]), a.js-data-grid-row-button:not([disabled])', function(event) {
					// Cache elements
					var $button	= $(this),
						$row	= $button.closest('tr');

					var isAjax		= $button.hasClass('js-data-grid-ajax') && that.dataTables.options.ajaxRequests,
						hasConfirm	= $button.data('js-data-grid-confirm');

					$.proxy(that.onSubmit, that);

					// Deselect all rows
					that.deselectAllRows();
					// Select updated row
					that.selectOneRow($row, true);

					// Get form data
					var $data = $('form', that.dataTables.$element).serializeArray();

					if ($button.data('actionName')) {
						// Add info about pushed button
						$data.push({ name: $button.data('actionName'), value: $button.data('actionValue') });

					} else {
						// Add info about pushed button
						$data.push({ name: this.name, value: this.value });
					}

					if (hasConfirm && confirm(hasConfirm)) {
						// Add class for indicate processing row
						$row.addClass('processing');

						// Make ajax call
						isAjax && (that.dataTables.ajax.doRequest($('form', that.dataTables.$element).prop('action'), $data, that.selected, event) || helpers.eventStopper(event));

					} else if (hasConfirm) {
						helpers.eventStopper(event);

					} else if (isAjax) {
						// Add class for indicate processing row
						$row.addClass('processing');

						// Make ajax call
						that.dataTables.ajax.doRequest($('form', that.dataTables.$element).prop('action'), $data, that.selected, event) || helpers.eventStopper(event);
					}

					// Deselect updated row
					that.selectOneRow($row, false);
				});
		},

		/**
		 *
		 */
		bindSubmitForm: function()
		{
			var that = this;

			$('form', this.dataTables.$element)
				.off('submit.ipub.dt')
				.on('submit.ipub.dt', function(event){
					event.preventDefault();

					// Get form data
					var $data = $(this).serializeArray();

					// Make ajax call
					that.dataTables.ajax.doRequest(this.action, $data, that.selected, event) || stop(event);

					// Deselect all rows
					that.deselectAllRows();
				});
		},

		/**
		 * Deselect all rows in grid
		 */
		deselectAllRows: function()
		{
			var that = this;

			// Get all available nodes in data grid
			var $nodes = this.dataTables.$table.rows().nodes().to$();

			$nodes.each(function(){
				$(that.selectorOne, $(this)).prop('checked', false);

				that.changeRow($(this), false);
			});

			// Call event triggers
			this.dataTables.$element.trigger('ipub.dt.allRowsDeselected');
		},

		/**
		 * Select all rows in grid
		 */
		selectAllRows: function()
		{
			var that = this;

			// Get all available nodes in data grid
			var $nodes = this.dataTables.$table.rows().nodes().to$();

			$nodes.each(function(){
				$(that.selectorOne, $(this)).prop('checked', true);

				that.changeRow($(this), true);
			});

			// Call event triggers
			this.dataTables.$element.trigger('ipub.dt.allRowsDeselected');
		},

		/**
		 * Select or deselect one current row in table
		 *
		 * @param {jQuery} $row
		 * @param {bool} status
		 */
		selectOneRow: function($row, status)
		{
			$(this.selectorOne, $row).prop('checked', status);

			this.changeRow($row, status);
		},

		/**
		 * Change one row selection
		 *
		 * @param {jQuery} $row
		 * @param {bool} selected
		 */
		changeRow: function($row, selected)
		{
			var id		= $row.prop('id'),
				index	= $.inArray(id, this.selected);

			// Row id must be set
			if ($.type(id) == 'undefined') return;

			if (selected === true && index === -1) {
				this.selected.push(id);

			} else if (selected === false && index !== -1) {
				this.selected.splice(index, 1);
			}

			if (this.selected.length) {
				if (this.selected.length == this.dataTables.$table.column(0).data().length) {
					$(this.selectorAll, this.dataTables.$element).prop('indeterminate', false);
					$(this.selectorAll, this.dataTables.$element).prop('checked', true);

				} else {
					$(this.selectorAll, this.dataTables.$element).prop('indeterminate', true);
					$(this.selectorAll, this.dataTables.$element).prop('checked', false);
				}

				// Call event triggers
				this.dataTables.$element.trigger('ipub.dt.rowsSelected');

			} else {
				$(this.selectorAll, this.dataTables.$element).prop('indeterminate', false);
				$(this.selectorAll, this.dataTables.$element).prop('checked', false);

				// Call event triggers
				this.dataTables.$element.trigger('ipub.dt.noRowsSelected');
			}

			$row[selected === true ? 'addClass':'removeClass']('selected');
		},

		onSubmit: function()
		{
			return true;

			var hasConfirm = this.getSelect().attr('js-data-grid-confirm-' + this.getSelect().val());

			if (hasConfirm) {
				if (confirm(hasConfirm.replace(/%i/g, $(this.selector + ':checked', this.dataTables.$element).length))) {
					return true;
				}

				this.getSelect().val('');

				return false;
			}

			return true;
		}
	}

	/**
	 * Ajax definition
	 *
	 * @param {IPub.DataTables} DataTables
	 */
	IPub.DataTables.Ajax = function(DataTables)
	{
		this.dataTables = DataTables;
	};

	IPub.DataTables.Ajax.prototype =
	{
		init: function()
		{
			return this;
		},

		/**
		 * @param {Object} payload
		 */
		handleSuccessEvent: function(payload)
		{
			// Are we updating rows?
			if (payload && payload.rows) {
				// Redraw whole table
				if (payload.rows.length <= 0 || payload.fullRedraw) {
					this.dataTables.$table.draw();

				// Redraw only updated rows
				} else {
					this.dataTables.updateRows(payload.rows)
				}
			}
		},

		handleFailEvent: function() {},

		handleAlwaysEvent: function() {},

		/**
		 * Load or send data from the server using a HTTP GET or POST request
		 *
		 * @param {string} url
		 * @param {array|null} data
		 * @param {array|null} selected
		 * @param {event|null} event
		 */
		doRequest: function(url, data, selected, event)
		{
			// If we have some additional data, then send via POST method
			if ($.type(data) != 'null') {
				var $xhr = $.post(url, data);

			// Without additional data, send as usual via GET method
			} else {
				var $xhr = $.get(url);
			}

			$xhr
				.done($.proxy(this.handleSuccessEvent, this))
				.fail($.proxy(this.handleFailEvent, this))
				.always($.proxy(this.handleAlwaysEvent, this));
		}
	};

	/**
	 * IPub DataTables helpers
	 */

	var helpers =
	{
		// Find column index by column name
		findColumnIndex: function(columns, name) {
			for(var i = 0; i < columns.length; ++i) {
				if (columns[i].name == name) {
					return i;
				}
			}

			return false;
		},

		// Extract column name from form element
		extractColumnNameFromInput: function($el) {
			// Parse element name
			var regex	= /(.+?)\[(.+?)\]/;
			var parsed	= regex.exec($el.prop('name'));

			if ($.type(parsed[1]) != 'undefined' && $.type(parsed[2]) != 'undefined' && parsed[1] == 'filters') {
				return parsed[2];
			}

			return null;
		},

		// Event stopper
		eventStopper: function(event) {
			event.preventDefault();
			event.stopImmediatePropagation();
		}
	};

	/**
	 * IPub DataTables plugin definition
	 */

	var old = $.fn.ipubDataTables;

	$.fn.ipubDT = function(options) {
		return this.each(function() {
			var that = $(this);

			if (!that.data('IPubDT')) {
				that.data('IPubDT', new IPub.DataTables.Grid(that, options).init());
			}
		});
	};

	/**
	 * IPub DataTables no conflict
	 */

	$.fn.ipubDT.noConflict = function () {
		$.fn.ipubDT = old;

		return this;
	};

	/**
	 * IPub DataTables defaults
	 */

	$.fn.ipubDT.defaults = {
		ajaxRequests : true,
		datepicker : {
			mask	: '99.99.9999',
			format	: 'dd.mm.yyyy'
		}
	};

	window.IPub = IPub;
	return IPub;

})(jQuery, window, document, location, navigator);