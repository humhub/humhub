function wallStick(className, id) {

    url = wallStickLinkUrl.replace('-className-', className);
    url = url.replace('-id-', id);
    
    postData = {}
    postData[csrfName] = csrfValue;
    
    $.ajax({
        dataType: "json",
        type: 'post',
        data: postData,        
        url: url
    }).done(function(data) {
        if (data.success) {
            if (currentStream) {
                $.each(data.wallEntryIds, function(k, wallEntryId) {
                    currentStream.deleteEntry(wallEntryId);
                    currentStream.prependEntry(wallEntryId);
                }); 
                $('html, body').animate({scrollTop:0}, 'slow');

            }
        } else {
            alert(data.errorMessage);
        }
    });
}

function wallUnstick(className, id) {

    url = wallUnstickLinkUrl.replace('-className-', className);
    url = url.replace('-id-', id);
    
    postData = {}
    postData[csrfName] = csrfValue;
    
    $.ajax({
        dataType: "json",
        type: 'post',
        data: postData,        
        url: url
    }).done(function(data) {
        if (data.success) {
            if (currentStream) {
                $.each(data.wallEntryIds, function(k, wallEntryId) {
                    currentStream.reloadWallEntry(wallEntryId);
                });
            }
        }
    });
}

/**
 * Click Handler for Archive Link of Wall Posts
 * (archiveLink.php)
 * 
 * @param {type} className
 * @param {type} id
 */
function wallArchive(className, id) {

    url = wallArchiveLinkUrl.replace('-className-', className);
    url = url.replace('-id-', id);

    postData = {}
    postData[csrfName] = csrfValue;

    $.ajax({
        dataType: "json",
        type: 'post',
        data: postData,
        url: url
    }).done(function(data) {
        if (data.success) {
            if (currentStream) {
                $.each(data.wallEntryIds, function(k, wallEntryId) {
                    //currentStream.reloadWallEntry(wallEntryId);
                    // fade out post
                    setInterval(fadeOut(), 1000);

                    function fadeOut() {
                        // fade out current archived post
                        $('#wallEntry_' + wallEntryId).fadeOut('slow');
                    }
                });
            }
        }
    });
}
;


/**
 * Click Handler for Un Archive Link of Wall Posts
 * (archiveLink.php)
 * 
 * @param {type} className
 * @param {type} id
 */
function wallUnarchive(className, id) {
    url = wallUnarchiveLinkUrl.replace('-className-', className);
    url = url.replace('-id-', id);

    postData = {}
    postData[csrfName] = csrfValue;

    $.ajax({
        dataType: "json",
        type: 'post',
        data: postData,
        url: url
    }).done(function(data) {
        if (data.success) {
            if (currentStream) {
                $.each(data.wallEntryIds, function(k, wallEntryId) {
                    currentStream.reloadWallEntry(wallEntryId);
                });

            }
        }
    });
}

/**
 * Wall Delete
 * 
 * Delete Link & Co
 * 
 * @param {type} jsonResp
 * @returns {undefined}
 */
function wallDelete(jsonResp) {
	json = jQuery.parseJSON(jsonResp);
	$.each(json.wallEntryIds, function(i, wallEntryId) {
		currentStream.deleteEntry(wallEntryId); // wall - stream.js function
	});	
}
