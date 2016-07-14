function initDirectoryList() {
	$('.directory-list li:last-child').addClass('last-child');
	$('.directory-list ul ul').hide();

	// handle selecting folders
	$('.directory-list .selectable').click(function() {
		$('.directory-list .selectedFolder').removeClass('selectedFolder');
		$(this).addClass('selectedFolder');
		$('#input-hidden-selectedFolder').val($(this).attr('id'));
	});

	// handle open close subfolders
	$('.directory-list li:has(ul)').addClass('hassub').find('>span, >a').click(
			function() {
				parentFolder = $(this).parent();

				if (parentFolder.hasClass('expand')) {
					parentFolder.removeClass('expand').find('>ul').slideUp(
							'200');
				} else {
					parentFolder.addClass('expand').find('>ul')
							.slideDown('200');
				}
			});
}

function openDirectory($id) {
	// optinal $id, set to 0 if undefined
	$id = $id || 0;
	folder = $('#' + $id).parent();
	do {
		folder.addClass('expand');
		folder.find('>ul').slideDown('100');
		folder = folder.parent().closest('li');
	} while (folder.hasClass('hassub'))
}

function selectDirectory($id) {
	// optinal $id, set to 0 if undefined
	$id = $id || 0;
	item = $('#' + $id);
	item.addClass('selectedFolder');
	$('#input-hidden-selectedFolder').val($id);
}
