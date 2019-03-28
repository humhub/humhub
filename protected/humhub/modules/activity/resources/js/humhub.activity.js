/**
 * Core module for managing Streams and StreamItems
 * @type Function
 */
humhub.module('activity', function (module, require, $) {

    var util = require('util');
    var object = util.object;
    var stream = require('stream');
    var loader = require('ui.loader');
    var Widget = require('ui.widget').Widget;

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


    var ActivityStreamEntry = stream.StreamEntry.extend(function (id) {
        stream.StreamEntry.call(this, id);
    });

    ActivityStreamEntry.prototype.delete = function () {/* Not implemented */};
    ActivityStreamEntry.prototype.edit = function () {/* Not implemented */};

    /**
     * ActivityStream implementation.
     * 
     * @param {type} container id or jQuery object of the stream container
     * @returns {undefined}
     */
    var ActivityStream = stream.Stream.extend(function (container, options) {
        var that = this;
        stream.Stream.call(this, container, {
            initLoadCount: STREAM_INIT_COUNT,
            loadCount: STREAM_LOAD_COUNT,
            streamEntryClass: ActivityStreamEntry,
        });
    });

    ActivityStream.prototype.initScroll = function () {
        if(!this.$content.is(':visible')) {
            return;
        }

        // listen for scrolling event yes or no
        var scrolling = true;
        var that = this;
        this.$content.scroll(function (evt) {
            if(that.lastEntryLoaded()) {
                return;
            }
            // save height of the overflow container
            var _containerHeight = that.$content.height();
            // save scroll height
            var _scrollHeight = that.$content.prop("scrollHeight");
            // save current scrollbar position
            var _currentScrollPosition = that.$content.scrollTop();

            // load more activites if current scroll position is near scroll height
            if (_currentScrollPosition >= (_scrollHeight - _containerHeight - 30)) {
                // checking if ajax loading is necessary or the last entries are already loaded
                if (scrolling) {
                    scrolling = false;
                    // load more activities
                    that.loadEntries({loader: true}).then(function() {
                        that.$content.getNiceScroll().resize();
                    }).finally(function () {
                        scrolling = true;
                    });
                }
            }
        });


        // set niceScroll to activity list
        that.$content.niceScroll({
            cursorwidth: "7",
            cursorborder: "",
            cursorcolor: "#555",
            cursoropacitymax: "0.2",
            nativeparentscrolling: false,
            railpadding: {top: 0, right: 3, left: 0, bottom: 0}
        });
    };

    ActivityStream.templates = {
        streamMessage: '<div class="streamMessage activity"><div class="panel-body">{message}</div></div>'
    };

    var getStream = function () {
        instance = instance || Widget.instance(ACTIVITY_STREAM_SELECTOR);

        if (!instance.$.length) {
            return;
        }

        return instance;
    };

    
    var unload = function() {
        // Cleanup nicescroll rails from dom
        if(instance && instance.$) {
            instance.$content.getNiceScroll().remove();
            instance.$content.css('overflow', 'hidden');
        }
        instance = undefined;
    };

    module.export({
        ActivityStream: ActivityStream,
        getStream: getStream,
        initOnPjaxLoad: true,
        unload: unload
    });
});