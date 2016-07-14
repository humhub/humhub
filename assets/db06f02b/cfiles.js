/**
 * 
 * @returns {undefined}
 */
function showHideBtns() {
	var checkCounter = 0;
	$('.multiselect').each(function() {
		if ($(this).prop('checked')) {
			checkCounter++;
		}
	});
	if (checkCounter != 0) {
		$('.selectedOnly').show();
		$('.chkCnt').html(checkCounter);
	} else {
		$('.selectedOnly').hide();
	}
	hideActionMenuDivider();
}

jQuery.urlParam = function(name) {
	var results = new RegExp('[\?&]' + name + '=([^&#]*)')
			.exec(window.location.href);
	if (results == null) {
		return null;
	} else {
		return results[1] || 0;
	}
}

/**
 * Inits File List after it's loaded/reloaded
 * 
 * @returns {undefined}
 */
function initFileList() {
	$('.multiselect').change(function() {
		showHideBtns();
	});
	$('.allselect').change(function() {
		$('.multiselect').each(function() {
			$(this).prop('checked', $('.allselect').prop('checked'));
		});
		showHideBtns();
	});
	$("#bs-table tr").contextMenu(
			{
				getMenuSelector : function(invokedOn, settings) {
					itemId = invokedOn.closest('tr').data('type');
					switch (itemId) {
					case "all-posted-files":
						return '#contextMenuAllPostedFiles';
					case "folder":
						return '#contextMenuFolder';
					case "image":
						return '#contextMenuImage';
					default:
						return '#contextMenuFile';
					}
				},
				menuSelected : function(invokedOn, selectedMenu) {

					action = selectedMenu.data('action');
					// file or folder
					itemType = invokedOn.closest('tr').data('type');
					// e.g. file-53
					itemRealId = invokedOn.closest('tr').data('id');
					parentId = jQuery.urlParam('fid') === null ? 0 : jQuery
							.urlParam('fid');

					switch (action) {
					case 'delete':
						$.ajax({
							url : cfilesDeleteUrl,
							type : 'POST',
							data : {
								'selected[]' : itemRealId,
							},
						}).done(function(html) {
							$("#globalModal").html(html);
							$("#globalModal").modal("show");
						});
						break;
					case 'edit':
						$.ajax(
								{
									url : cfilesEditFolderUrl.replace(
											'--folderId--', itemRealId
													.split('_')[1]),
									type : 'GET',
								}).done(function(html) {
							$("#globalModal").html(html);
							$("#globalModal").modal("show");
						});
						break;
					case 'download':
						url = invokedOn.closest('tr').data('url');
						document.location.href = url;
						break;
					case 'zip':
						url = cfilesDownloadArchiveUrl.replace('--folderId--',
								itemRealId.split('_')[1]),
								document.location.href = url;
						break;
					case 'move-files':
						$.ajax({
							url : cfilesMoveUrl,
							type : 'POST',
							data : {
								'selected[]' : itemRealId,
							},
						}).done(function(html) {
							$("#globalModal").html(html);
							$("#globalModal").modal("show");
							openDirectory(parentId);
							selectDirectory(parentId);
						});
						break;
					case 'show-image':
						previewLink = invokedOn.closest('tr').find(
								'.preview-link');
						previewLink.trigger("click");
						break;
					case 'show-post':
						url = invokedOn.closest('tr').data('content-url');
						document.location.href = url;
						break;
					default:
						alert("Unkown action " + action);
						break;
					}
				}
			});
}

function updateLog(messages, container) {
	if ($.isArray(messages)) {
		$.each(messages, function(index, message) {
			container.append('<li>' + message + '</li>');
			container.show();
		});
	} else if (!jQuery.isEmptyObject(messages)) {
		container.append('<li>' + message + '</li>');
		container.show();
	}
}

function updateLogs(errors, warnings, infos) {
	updateLog(errors, $('#hiddenLogContainer .alert-danger'));
	updateLog(warnings, $('#hiddenLogContainer .alert-warning'));
	updateLog(infos, $('#hiddenLogContainer .alert-info'));
	$('#cfiles-log').html($('#hiddenLogContainer').html());
}

function clearLog() {
	$('#hiddenLogContainer .alert-danger').empty();
	$('#hiddenLogContainer .alert-danger').hide();
	$('#hiddenLogContainer .alert-warning').empty();
	$('#hiddenLogContainer .alert-warning').hide();
	$('#hiddenLogContainer .alert-info').empty();
	$('#hiddenLogContainer .alert-info').hide();
}

function hideActionMenuDivider() {
	if($('.files-action-menu').children(":visible").length > 0) {
		$('#files-action-menu-divider').show();
	} else {
		$('#files-action-menu-divider').hide();
	}
}

$(function() {

	/**
	 * Bind event actions.
	 */
	$("#zip-selected-button").click(function(event) {
		event.preventDefault();
		$form = $('#cfiles-form');
		$form.attr("action", $(this).attr("href"));
		$form.attr("method", "post");
		$form.attr("enctype", "multipart/form-data");
		$form.submit()
	});

	hideActionMenuDivider();
	
	/**
	 * Install uploader
	 */
	$('#fileupload')
			.fileupload(
					{
						url : cfilesUploadUrl,
						dataType : 'json',
						done : function(e, data) {
							$.each(data.result.files, function(index, file) {
								$('#fileList').html(file.fileList);
							});
							updateLogs(data.result.errormessages,
									data.result.warningmessages,
									data.result.infomessages);
						},
						fail : function(e, data) {
							updateLogs(data.jqXHR.responseJSON.message, null,
									null);
						},
						start : function(e, data) {
							clearLog();
						},
						progressall : function(e, data) {
							var progress = parseInt(data.loaded / data.total
									* 100, 10);
							if (progress != 100) {
								$('#progress').show();
								$('#progress .progress-bar').css('width',
										progress + '%');
							} else {
								$('#progress').hide();
								$('#fileupload').parents(".btn-group").click();
							}
						}
					}).prop('disabled', !$.support.fileInput).parent()
			.addClass($.support.fileInput ? undefined : 'disabled');
	/**
	 * Install uploader
	 */
	$('#zipupload').fileupload(
			{
				dropZone : $([]),
				url : cfilesZipUploadUrl,
				dataType : 'json',
				done : function(e, data) {
					$.each(data.result.files, function(index, file) {
						$('#fileList').html(file.fileList);
					});
					updateLogs(data.result.errormessages,
							data.result.warningmessages,
							data.result.infomessages);
				},
				fail : function(e, data) {
					updateLogs(data.jqXHR.responseJSON.message, null, null);
				},
				start : function(e, data) {
					clearLog();
				},
				success : function(e, data) {
					$('#progress').hide();
					$("#zipupload").parents(".btn-group").click();
				},
				progressall : function(e, data) {
					var progress = parseInt(50, 10);
					$('#progress').show();
					$('#progress .progress-bar').css('width', progress + '%');
				}
			}).prop('disabled', !$.support.fileInput).parent().addClass(
			$.support.fileInput ? undefined : 'disabled');

});

/**
 * Context Menu
 */
(function($, window) {

	$.fn.contextMenu = function(settings) {

		return this.each(function() {

			// Open context menu
			$(this).on(
					"contextmenu",
					function(e) {
						// return native menu if pressing control
						if (e.ctrlKey)
							return;

						// Make sure all menus are hidden
						$('.contextMenu').hide();

						menuSelector = settings.getMenuSelector.call(this,
								$(e.target));

					    oParent = $(menuSelector).parent().offsetParent().offset(),
					    posTop = e.clientY - oParent.top,
					    posLeft = e.clientX - oParent.left;
						
						// open menu
						var $menu = $(menuSelector).data("invokedOn",
								$(e.target)).show().css(
								{
									position : "absolute",
									left : getMenuPosition(posLeft, 'width',
										'scrollLeft'),
									top : getMenuPosition(posTop, 'height',
									'scrollTop')
								}).off('click').on(
								'click',
								'a',
								function(e) {
									$menu.hide();

									var $invokedOn = $menu.data("invokedOn");
									var $selectedMenu = $(e.target);

									settings.menuSelected.call(this,
											$invokedOn, $selectedMenu);
								});

						return false;
					});

			// make sure menu closes on any click
			$(document).click(function() {
				$('.contextMenu').hide();
			});
		});

		function getMenuPosition(mouse, direction, scrollDir) {
			var win = $(window)[direction]();
			var scroll = $(window)[scrollDir]();
			var menu = $(settings.menuSelector)[direction]();
			var position = mouse + scroll;

			// opening menu would pass the side of the page
			if (mouse + menu > win && menu < mouse)
				position -= menu;

			return position;
		}

	};
})(jQuery, window);
