/**
 * Core module for managing Streams and StreamItems
 * @type Function
 */
humhub.initModule('stream', function(module, require, $) {

    var ENTRY_ID_SELECTOR_PREFIX = '#wallEntry_';
    var WALLSTREAM_ID = 'wallStream';

    /**
     * Base class for all StreamItems
     * @param {type} id
     * @returns {undefined}
     */
    var StreamItem = function (id) {
        if (typeof id === 'string') {
            this.id = id;
            this.$ = $('#' + id);
        } else if (id.jquery) {
            this.$ = id;
            this.id = this.$.attr('id');
        }
    };

    /**
     * Removes the stream item from stream
     */
    StreamItem.prototype.remove = function () {
        this.$.remove();
    };
    
    StreamItem.prototype.getContentKey = function () {}
    
    StreamItem.prototype.edit = function () {
        //Search for data-content-edit-url on root.
        //Call this url with data-content-pk
        //Trigger delete event
    };
    
    StreamItem.prototype.delete = function () {
        //Search for data-content-delte-url on root.
        //Call this url with data-content-pk
        //Trigger delete event
    };

    StreamItem.prototype.getContent = function () {
        return this.$.find('.content');
    };

/*
    module.StreamItem.prototype.highlightContent = function () {
        var $content = this.getContent();
        $content.addClass('highlight');
        $content.delay(200).animate({backgroundColor: 'transparent'}, 1000, function () {
            $content.removeClass('highlight');
            $content.css('backgroundColor', '');
        });
    };
*/
    /**
     * Stream implementation
     * @param {type} id
     * @returns {undefined}
     */
    var Stream = function (id) {
        this.id = id;
        this.$ = $('#' + id);
    };

    Stream.prototype.getEntry = function (id) {
        //Search for data-content-base and try to initiate the Item class
        
        return new module.Entry(this.$.find(ENTRY_ID_SELECTOR_PREFIX + id));
    };

    Stream.prototype.wallStick = function (url) {
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
    };

    Stream.prototype.wallUnstick = function (url) {
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
    };

    /**
     * Click Handler for Archive Link of Wall Posts
     * (archiveLink.php)
     * 
     * @param {type} className
     * @param {type} id
     */
    Stream.prototype.wallArchive = function (id) {

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
    };


    /**
     * Click Handler for Un Archive Link of Wall Posts
     * (archiveLink.php)
     * 
     * @param {type} className
     * @param {type} id
     */
    Stream.prototype.wallUnarchive = function (id) {
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
    };
    
    var getStream = function () { 
        if (!module.mainStream) {
            module.mainStream = new module.Stream(WALLSTREAM_ID);
        }
        return module.mainStream;
    };

    var getEntry = function (id) {
        return module.getStream().getEntry(id);
    };
    
    module.export({
       getStream : getStream,
       getEntry : getEntry
    });
});