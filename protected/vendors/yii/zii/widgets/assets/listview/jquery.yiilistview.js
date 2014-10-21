/**
 * jQuery Yii ListView plugin file.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright 2008-2010 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

;(function($) {
	var yiiXHR = {};
	/**
	 * yiiListView set function.
	 * @param options map settings for the list view. Availablel options are as follows:
	 * - ajaxUpdate: array, IDs of the containers whose content may be updated by ajax response
	 * - ajaxVar: string, the name of the request variable indicating the ID of the element triggering the AJAX request
	 * - ajaxType: string, the type (GET or POST) of the AJAX request
	 * - pagerClass: string, the CSS class for the pager container
	 * - sorterClass: string, the CSS class for the sorter container
	 * - updateSelector: string, the selector for choosing which elements can trigger ajax requests
	 * - beforeAjaxUpdate: function, the function to be called before ajax request is sent
	 * - afterAjaxUpdate: function, the function to be called after ajax response is received
	 */
	$.fn.yiiListView = function(options) {
		return this.each(function(){
			var settings = $.extend({}, $.fn.yiiListView.defaults, options || {}),
			$this = $(this),
			id = $this.attr('id');

			if(settings.updateSelector == undefined) {
				settings.updateSelector = '#'+id+' .'+settings.pagerClass.replace(/\s+/g,'.')+' a, #'+id+' .'+settings.sorterClass.replace(/\s+/g,'.')+' a';
			}
			$.fn.yiiListView.settings[id] = settings;

			if(settings.ajaxUpdate.length > 0) {
				$(document).on('click.yiiListView', settings.updateSelector,function(){
					if(settings.enableHistory && window.History.enabled) {
						var url = $(this).attr('href').split('?'),
							params = $.deparam.querystring('?'+ (url[1] || ''));

						delete params[settings.ajaxVar];
						window.History.pushState(null, document.title, decodeURIComponent($.param.querystring(url[0], params)));
					} else {
						$.fn.yiiListView.update(id, {url: $(this).attr('href')});
					}
					return false;
				});

				if(settings.enableHistory && window.History.enabled) {
					$(window).bind('statechange', function() { // Note: We are using statechange instead of popstate
						var State = window.History.getState(); // Note: We are using History.getState() instead of event.state
						$.fn.yiiListView.update(id, {url: State.url});
					});
				}
			}
		});
	};

	$.fn.yiiListView.defaults = {
		ajaxUpdate: [],
		ajaxVar: 'ajax',
		ajaxType: 'GET',
		pagerClass: 'pager',
		loadingClass: 'loading',
		sorterClass: 'sorter'
		// updateSelector: '#id .pager a, '#id .sort a',
		// beforeAjaxUpdate: function(id) {},
		// afterAjaxUpdate: function(id, data) {},
		// url: 'ajax request URL'
	};

	$.fn.yiiListView.settings = {};

	/**
	 * Returns the key value for the specified row
	 * @param id string the ID of the list view container
	 * @param index integer the zero-based index of the data item
	 * @return string the key value
	 */
	$.fn.yiiListView.getKey = function(id, index) {
		return $('#'+id+' > div.keys > span:eq('+index+')').text();
	};

	/**
	 * Returns the URL that generates the list view content.
	 * @param id string the ID of the list view container
	 * @return string the URL that generates the list view content.
	 */
	$.fn.yiiListView.getUrl = function(id) {
		var settings = $.fn.yiiListView.settings[id];
		return settings.url || $('#'+id+' > div.keys').attr('title');
	};

	/**
	 * Performs an AJAX-based update of the list view contents.
	 * @param id string the ID of the list view container
	 * @param options map the AJAX request options (see jQuery.ajax API manual). By default,
	 * the URL to be requested is the one that generates the current content of the list view.
	 */
	$.fn.yiiListView.update = function(id, options) {
		var customError,
			settings = $.fn.yiiListView.settings[id];

		if (options && options.error !== undefined) {
			customError = options.error;
			delete options.error;
		}

		options = $.extend({
			type: settings.ajaxType,
			url: $.fn.yiiListView.getUrl(id),
			success: function(data,status) {
				$.each(settings.ajaxUpdate, function(i,v) {
					var id='#'+v;
					$(id).replaceWith($(id,'<div>'+data+'</div>'));
				});
				if(settings.afterAjaxUpdate != undefined)
					settings.afterAjaxUpdate(id, data);
			},
			complete: function() {
				$('#'+id).removeClass(settings.loadingClass);
				yiiXHR[id] = null;
			},
			error: function(XHR, textStatus, errorThrown) {
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
		
		if(options.data!=undefined && options.type=='GET') {
			options.url = $.param.querystring(options.url, options.data);
			options.data = {};
		}
		
		if(settings.ajaxVar)
			options.url = $.param.querystring(options.url, settings.ajaxVar+'='+id);
		
		if(yiiXHR[id] != null) {
			yiiXHR[id].abort();	
		}
		
		$('#'+id).addClass(settings.loadingClass);

		if(settings.beforeAjaxUpdate != undefined)
			settings.beforeAjaxUpdate(id);
		yiiXHR[id] = $.ajax(options);
	};

})(jQuery);
