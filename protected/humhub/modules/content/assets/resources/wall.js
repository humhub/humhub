function wallStick(url) {
    $.ajax({
        dataType: "json",
        type: 'post',
        url: url
    }).done(function (data) {
        if (data.success) {
            if (currentStream) {
                $.each(data.wallEntryIds, function (k, wallEntryId) {
                    currentStream.deleteEntry(wallEntryId);
                    currentStream.prependEntry(wallEntryId);
                });
                $('html, body').animate({scrollTop: 0}, 'slow');
            }
        } else {
            alert(data.errorMessage);
        }
    });
}

function wallUnstick(url) {
    $.ajax({
        dataType: "json",
        type: 'post',
        url: url
    }).done(function (data) {
        if (data.success) {
            //Reload the whole stream, since we have to reorder the entries
            currentStream.showStream();
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
function wallArchive(id) {

    url = wallArchiveLinkUrl.replace('-id-', id);

    $.ajax({
        dataType: "json",
        type: 'post',
        url: url
    }).done(function (data) {
        if (data.success) {
            if (currentStream) {
                $.each(data.wallEntryIds, function (k, wallEntryId) {
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
function wallUnarchive(id) {
    url = wallUnarchiveLinkUrl.replace('-id-', id);

    $.ajax({
        dataType: "json",
        type: 'post',
        url: url
    }).done(function (data) {
        if (data.success) {
            if (currentStream) {
                $.each(data.wallEntryIds, function (k, wallEntryId) {
                    currentStream.reloadWallEntry(wallEntryId);
                });

            }
        }
    });
}
