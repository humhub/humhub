/**
 * jQuery Yii GridView plugin file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

(function ($) {
	var selectCheckedRows, methods, 
	 	yiiXHR={},
		gridSettings = [];
	/**
	 * 1. Selects rows that have checkbox checked (only checkbox that is connected with selecting a row)
	 * 2. Check if "check all" need to be checked/unchecked
	 * @return object the jQuery object
	 */
	selectCheckedRows = function (gridId) {
		var settings = gridSettings[gridId],
			table = $('#' + gridId).find('.' + settings.tableClass);

		table.children('tbody').find('input.select-on-check').filter(':checked').each(function () {
			$(this).closest('tr').addClass('selected');
		});

		table.children('thead').find('th input').filter('[type="checkbox"]').each(function () {
			var name = this.name.substring(0, this.name.length - 4) + '[]', //.. remove '_all' and add '[]''
				$checks = $("input[name='" + name + "']", table);
			this.checked = $checks.length > 0 && $checks.length === $checks.filter(':checked').length;
		});
		return this;
	};

	methods = {
		/**
		 * yiiGridView set function.
		 * @param options map settings for the grid view. Available options are as follows:
		 * - ajaxUpdate: array, IDs of the containers whose content may be updated by ajax response
		 * - ajaxVar: string, the name of the request variable indicating the ID of the element triggering the AJAX request
		 * - ajaxType: string, the type (GET or POST) of the AJAX request
		 * - pagerClass: string, the CSS class for the pager container
		 * - tableClass: string, the CSS class for the table
		 * - selectableRows: integer, the number of rows that can be selected
		 * - updateSelector: string, the selector for choosing which elements can trigger ajax requests
		 * - beforeAjaxUpdate: function, the function to be called before ajax request is sent
		 * - afterAjaxUpdate: function, the function to be called after ajax response is received
		 * - ajaxUpdateError: function, the function to be called if an ajax error occurs
		 * - selectionChanged: function, the function to be called after the row selection is changed
		 * @return object the jQuery object
		 */
		init: function (options) {
			var settings = $.extend({
					ajaxUpdate: [],
					ajaxVar: 'ajax',
					ajaxType: 'GET',
					pagerClass: 'pager',
					loadingClass: 'loading',
					filterClass: 'filters',
					tableClass: 'items',
					selectableRows: 1
					// updateSelector: '#id .pager a, '#id .grid thead th a',
					// beforeAjaxUpdate: function (id) {},
					// afterAjaxUpdate: function (id, data) {},
					// selectionChanged: function (id) {},
					// url: 'ajax request URL'
				}, options || {});

			settings.tableClass = settings.tableClass.replace(/\s+/g, '.');

			return this.each(function () {
				var eventType,
					$grid = $(this),
					id = $grid.attr('id'),
					pagerSelector = '#' + id + ' .' + settings.pagerClass.replace(/\s+/g, '.') + ' a',
					sortSelector = '#' + id + ' .' + settings.tableClass + ' thead th a.sort-link',
					inputSelector = '#' + id + ' .' + settings.filterClass + ' input, ' + '#' + id + ' .' + settings.filterClass + ' select';

				settings.updateSelector = settings.updateSelector
								.replace('{page}', pagerSelector)
								.replace('{sort}', sortSelector);
				settings.filterSelector = settings.filterSelector
								.replace('{filter}', inputSelector);

				gridSettings[id] = settings;

				if (settings.ajaxUpdate.length > 0) {
					$(document).on('click.yiiGridView', settings.updateSelector, function () {
						// Check to see if History.js is enabled for our Browser
						if (settings.enableHistory && window.History.enabled) {
							// Ajaxify this link
							var url = $(this).attr('href').split('?'),
								params = $.deparam.querystring('?'+ (url[1] || ''));

							delete params[settings.ajaxVar];
							window.History.pushState(null, document.title, decodeURIComponent($.param.querystring(url[0], params)));
						} else {
							$('#' + id).yiiGridView('update', {url: $(this).attr('href')});
						}
						return false;
					});
				}

				$(document).on('change.yiiGridView keydown.yiiGridView', settings.filterSelector, function (event) {
					if (event.type === 'keydown') {
						if (event.keyCode !== 13) {
							return; // only react to enter key
						} else {
							eventType = 'keydown';
						}
					} else {
						// prevent processing for both keydown and change events
						if (eventType === 'keydown') {
							eventType = '';
							return;
						}
					}
					var data = $(settings.filterSelector).serialize();
					if (settings.pageVar !== undefined) {
						data += '&' + settings.pageVar + '=1';
					}
					if (settings.enableHistory && settings.ajaxUpdate !== false && window.History.enabled) {
						// Ajaxify this link
						var url = $('#' + id).yiiGridView('getUrl'),
							params = $.deparam.querystring($.param.querystring(url, data));

						delete params[settings.ajaxVar];
						window.History.pushState(null, document.title, decodeURIComponent($.param.querystring(url.substr(0, url.indexOf('?')), params)));
					} else {
						$('#' + id).yiiGridView('update', {data: data});
					}
					return false;
				});

				if (settings.enableHistory && settings.ajaxUpdate !== false && window.History.enabled) {
					$(window).bind('statechange', function() { // Note: We are using statechange instead of popstate
						var State = window.History.getState(); // Note: We are using History.getState() instead of event.state
						$('#' + id).yiiGridView('update', {url: State.url});
					});
				}

				if (settings.selectableRows > 0) {
					selectCheckedRows(this.id);
					$(document).on('click.yiiGridView', '#' + id + ' .' + settings.tableClass + ' > tbody > tr', function (e) {
						var $currentGrid, $row, isRowSelected, $checks,
							$target = $(e.target);

						if ($target.closest('td').is('.empty,.button-column') || (e.target.type === 'checkbox' && !$target.hasClass('select-on-check'))) {
							return;
						}

						$row = $(this);
						$currentGrid = $('#' + id);
						$checks = $('input.select-on-check', $currentGrid);
						isRowSelected = $row.toggleClass('selected').hasClass('selected');

						if (settings.selectableRows === 1) {
							$row.siblings().removeClass('selected');
							$checks.prop('checked', false);
						}
						$('input.select-on-check', $row).prop('checked', isRowSelected);
						$("input.select-on-check-all", $currentGrid).prop('checked', $checks.length === $checks.filter(':checked').length);

						if (settings.selectionChanged !== undefined) {
							settings.selectionChanged(id);
						}
					});
					if (settings.selectableRows > 1) {
						$(document).on('click.yiiGridView', '#' + id + ' .select-on-check-all', function () {
							var $currentGrid = $('#' + id),
								$checks = $('input.select-on-check', $currentGrid),
								$checksAll = $('input.select-on-check-all', $currentGrid),
								$rows = $currentGrid.find('.' + settings.tableClass).children('tbody').children();
							if (this.checked) {
								$rows.addClass('selected');
								$checks.prop('checked', true);
								$checksAll.prop('checked', true);
							} else {
								$rows.removeClass('selected');
								$checks.prop('checked', false);
								$checksAll.prop('checked', false);
							}
							if (settings.selectionChanged !== undefined) {
								settings.selectionChanged(id);
							}
						});
					}
				} else {
					$(document).on('click.yiiGridView', '#' + id + ' .select-on-check', false);
				}
			});
		},

		/**
		 * Returns the key value for the specified row
		 * @param row integer the row number (zero-based index)
		 * @return string the key value
		 */
		getKey: function (row) {
			return this.children('.keys').children('span').eq(row).text();
		},

		/**
		 * Returns the URL that generates the grid view content.
		 * @return string the URL that generates the grid view content.
		 */
		getUrl: function () {
			var sUrl = gridSettings[this.attr('id')].url;
			return sUrl || this.children('.keys').attr('title');
		},

		/**
		 * Returns the jQuery collection of the cells in the specified row.
		 * @param row integer the row number (zero-based index)
		 * @return jQuery the jQuery collection of the cells in the specified row.
		 */
		getRow: function (row) {
			var sClass = gridSettings[this.attr('id')].tableClass;
			return this.find('.' + sClass).children('tbody').children('tr').eq(row).children();
		},

		/**
		 * Returns the jQuery collection of the cells in the specified column.
		 * @param column integer the column number (zero-based index)
		 * @return jQuery the jQuery collection of the cells in the specified column.
		 */
		getColumn: function (column) {
			var sClass = gridSettings[this.attr('id')].tableClass;
			return this.find('.' + sClass).children('tbody').children('tr').children('td:nth-child(' + (column + 1) + ')');
		},

		/**
		 * Performs an AJAX-based update of the grid view contents.
		 * @param options map the AJAX request options (see jQuery.ajax API manual). By default,
		 * the URL to be requested is the one that generates the current content of the grid view.
		 * @return object the jQuery object
		 */
		update: function (options) {
			var customError;
			if (options && options.error !== undefined) {
				customError = options.error;
				delete options.error;
			}

			return this.each(function () {
				var $form,
					$grid = $(this),
					id = $grid.attr('id'),
					settings = gridSettings[id];

				options = $.extend({
					type: settings.ajaxType,
					url: $grid.yiiGridView('getUrl'),
					success: function (data) {
						var $data = $('<div>' + data + '</div>');
						$.each(settings.ajaxUpdate, function (i, el) {
							var updateId = '#' + el;
							$(updateId).replaceWith($(updateId, $data));
						});
						if (settings.afterAjaxUpdate !== undefined) {
							settings.afterAjaxUpdate(id, data);
						}
						if (settings.selectableRows > 0) {
							selectCheckedRows(id);
						}
					},
					complete: function () {
						yiiXHR[id] = null;
						$grid.removeClass(settings.loadingClass);
					},
					error: function (XHR, textStatus, errorThrown) {
						var ret, err;
						if (XHR.readyState === 0 || XHR.status === 0) {
							return;
						}
						if (customError !== undefined) {
							ret = customError(XHR);
							if (ret !== undefined && !ret) {
								return;
							}
						}
						switch (textStatus) {
						case 'timeout':
							err = 'The request timed out!';
							break;
						case 'parsererror':
							err = 'Parser error!';
							break;
						case 'error':
							if (XHR.status && !/^\s*$/.test(XHR.status)) {
								err = 'Error ' + XHR.status;
							} else {
								err = 'Error';
							}
							if (XHR.responseText && !/^\s*$/.test(XHR.responseText)) {
								err = err + ': ' + XHR.responseText;
							}
							break;
						}

						if (settings.ajaxUpdateError !== undefined) {
							settings.ajaxUpdateError(XHR, textStatus, errorThrown, err);
						} else if (err) {
							alert(err);
						}
					}
				}, options || {});
				if (options.type === 'GET') {
					if (options.data !== undefined) {
						options.url = $.param.querystring(options.url, options.data);
						options.data = {};
					}
				} else {
					if (options.data === undefined) {
						options.data = $(settings.filterSelector).serialize();
					}
				}
				if(yiiXHR[id] != null){
					yiiXHR[id].abort();
				}
				//class must be added after yiiXHR.abort otherwise ajax.error will remove it
				$grid.addClass(settings.loadingClass);
				
				if (settings.ajaxUpdate !== false) {
					if(settings.ajaxVar) {
						options.url = $.param.querystring(options.url, settings.ajaxVar + '=' + id);
					}
					if (settings.beforeAjaxUpdate !== undefined) {
						settings.beforeAjaxUpdate(id, options);
					}
					yiiXHR[id] = $.ajax(options);
				} else {  // non-ajax mode
					if (options.type === 'GET') {
						window.location.href = options.url;
					} else {  // POST mode
						$form = $('<form action="' + options.url + '" method="post"></form>').appendTo('body');
						if (options.data === undefined) {
							options.data = {};
						}

						if (options.data.returnUrl === undefined) {
							options.data.returnUrl = window.location.href;
						}

						$.each(options.data, function (name, value) {
							$form.append($('<input type="hidden" name="t" value="" />').attr('name', name).val(value));
						});
						$form.submit();
					}
				}
			});
		},

		/**
		 * Returns the key values of the currently selected rows.
		 * @return array the key values of the currently selected rows.
		 */
		getSelection: function () {
			var settings = gridSettings[this.attr('id')],
				keys = this.find('.keys span'),
				selection = [];
			this.find('.' + settings.tableClass).children('tbody').children().each(function (i) {
				if ($(this).hasClass('selected')) {
					selection.push(keys.eq(i).text());
				}
			});
			return selection;
		},

		/**
		 * Returns the key values of the currently checked rows.
		 * @param column_id string the ID of the column
		 * @return array the key values of the currently checked rows.
		 */
		getChecked: function (column_id) {
			var settings = gridSettings[this.attr('id')],
				keys = this.find('.keys span'),
				checked = [];
			if (column_id.substring(column_id.length - 2) !== '[]') {
				column_id = column_id + '[]';
			}
			this.find('.' + settings.tableClass).children('tbody').children('tr').children('td').children('input[name="' + column_id + '"]').each(function (i) {
				if (this.checked) {
					checked.push(keys.eq(i).text());
				}
			});
			return checked;
		}
		
	};

	$.fn.yiiGridView = function (method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method + ' does not exist on jQuery.yiiGridView');
			return false;
		}
	};

/******************************************************************************
 *** DEPRECATED METHODS
 *** used before Yii 1.1.9
 ******************************************************************************/
	$.fn.yiiGridView.settings = gridSettings;
	/**
	 * Returns the key value for the specified row
	 * @param id string the ID of the grid view container
	 * @param row integer the row number (zero-based index)
	 * @return string the key value
	 */
	$.fn.yiiGridView.getKey = function (id, row) {
		return $('#' + id).yiiGridView('getKey', row);
	};

	/**
	 * Returns the URL that generates the grid view content.
	 * @param id string the ID of the grid view container
	 * @return string the URL that generates the grid view content.
	 */
	$.fn.yiiGridView.getUrl = function (id) {
		return $('#' + id).yiiGridView('getUrl');
	};

	/**
	 * Returns the jQuery collection of the cells in the specified row.
	 * @param id string the ID of the grid view container
	 * @param row integer the row number (zero-based index)
	 * @return jQuery the jQuery collection of the cells in the specified row.
	 */
	$.fn.yiiGridView.getRow = function (id, row) {
		return $('#' + id).yiiGridView('getRow', row);
	};

	/**
	 * Returns the jQuery collection of the cells in the specified column.
	 * @param id string the ID of the grid view container
	 * @param column integer the column number (zero-based index)
	 * @return jQuery the jQuery collection of the cells in the specified column.
	 */
	$.fn.yiiGridView.getColumn = function (id, column) {
		return $('#' + id).yiiGridView('getColumn', column);
	};

	/**
	 * Performs an AJAX-based update of the grid view contents.
	 * @param id string the ID of the grid view container
	 * @param options map the AJAX request options (see jQuery.ajax API manual). By default,
	 * the URL to be requested is the one that generates the current content of the grid view.
	 */
	$.fn.yiiGridView.update = function (id, options) {
		$('#' + id).yiiGridView('update', options);
	};

	/**
	 * Returns the key values of the currently selected rows.
	 * @param id string the ID of the grid view container
	 * @return array the key values of the currently selected rows.
	 */
	$.fn.yiiGridView.getSelection = function (id) {
		return $('#' + id).yiiGridView('getSelection');
	};

	/**
	 * Returns the key values of the currently checked rows.
	 * @param id string the ID of the grid view container
	 * @param column_id string the ID of the column
	 * @return array the key values of the currently checked rows.
	 */
	$.fn.yiiGridView.getChecked = function (id, column_id) {
		return $('#' + id).yiiGridView('getChecked', column_id);
	};
})(jQuery);
