/**
 * Core module for managing Streams and StreamItems
 * @type Function
 */
humhub.initModule('activity', function (module, require, $) {

    var util = require('util');
    var object = util.object;
    var stream = require('stream');
    var loader = require('ui.loader');

    /**
     * Number of initial stream enteis loaded when stream is initialized.
     * @type Number
     */
    var STREAM_INIT_COUNT = 10;

    /**
     * Number of stream entries loaded with each request (except initial request)
     * @type Number
     */
    var STREAM_LOAD_COUNT = 10;

    /**
     * Number of stream entries loaded with each request (except initial request)
     * @type Number
     */
    var ACTIVITY_STREAM_SELECTOR = '#activityStream';

    /**
     * ActivityStream instance;
     * @type ActivityStream
     */
    var instance;


    var ActivityStreamEntry = function (id) {
        stream.StreamEntry.call(this, id);
    };

    object.inherits(ActivityStreamEntry, stream.StreamEntry);


    ActivityStreamEntry.prototype.actions = function () {
        return [];
    };

    ActivityStreamEntry.prototype.delete = function () {/* Not implemented */}
    ActivityStreamEntry.prototype.edit = function () {/* Not implemented */}

    /**
     * ActivityStream implementation.
     * 
     * @param {type} container id or jQuery object of the stream container
     * @returns {undefined}
     */
    var ActivityStream = function (container) {
        stream.Stream.call(this, container, {
            'loadInitialCount': STREAM_INIT_COUNT,
            'loadCount': STREAM_LOAD_COUNT,
            'streamEntryClass': ActivityStreamEntry
        });
    };

    object.inherits(ActivityStream, stream.Stream);
    
    ActivityStream.prototype.showLoader = function() {
        var $loaderListItem = $('<li id="activityLoader" class="streamLoader">');
        loader.append($loaderListItem);
        this.$content.append($loaderListItem);
    };
    
    ActivityStream.prototype.hideLoader = function() {
        this.$content.find('#activityLoader').remove();
    };

    var getStream = function () {
        instance = instance || new ActivityStream($(ACTIVITY_STREAM_SELECTOR));
        return instance;
    };

    var init = function () {
        instance = undefined;
        
        var stream = getStream();

        if (!stream) {
            module.log.info('No activity stream found!');
            return;
        }

        stream.init();

        var activityLastEntryReached = false;

        // listen for scrolling event yes or no
        var scrolling = true;

        stream.$content.scroll(function (evt) {
 
            // save height of the overflow container
            var _containerHeight = stream.$content.height();

            // save scroll height
            var _scrollHeight = stream.$content.prop("scrollHeight");

            // save current scrollbar position
            var _currentScrollPosition = stream.$content.scrollTop();

            // load more activites if current scroll position is near scroll height
            if (_currentScrollPosition >= (_scrollHeight - _containerHeight - 30)) {
                // checking if ajax loading is necessary or the last entries are already loaded
                if (activityLastEntryReached == false) {
                    if (scrolling == true) {
                        // stop listening for scrolling event to load the new activity range only one time
                        scrolling = false;
                        // load more activities
                        stream.loadEntries().finally(function() {
                            scrolling = true;
                        });
                    }
                }
            }
        });
    };

    module.export({
        ActivityStream: ActivityStream,
        getStream: getStream,
        init: init
    });
});