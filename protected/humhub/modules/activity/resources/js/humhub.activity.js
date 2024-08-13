/**
 * Core module for managing Streams and StreamItems
 * @type Function
 */
humhub.module('activity', function (module, require, $) {

    var util = require('util');
    var stream = require('stream');
    var Widget = require('ui.widget').Widget;
    var container = require('content.container');
    var user = require('user');
    var view = require('ui.view');

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
     * @type string
     */
    var ACTIVITY_STREAM_SELECTOR = '#activityStream';

    /**
     * ActivityStream instance;
     * @type ActivityStream
     */
    var instance;


    var ActivityStreamEntry = stream.StreamEntry.extend();

    ActivityStreamEntry.prototype.delete = function () {/* Not implemented */};
    ActivityStreamEntry.prototype.edit = function () {/* Not implemented */};

    /**
     * ActivityStream implementation.
     *
     * @param {type} container id or jQuery object of the stream container
     * @returns {undefined}
     */
    var ActivityStream = stream.Stream.extend(function (container, options) {
        stream.Stream.call(this, container, {
            scrollSupport: true,
            scrollOptions: { rootMargin: "30px" },
            initLoadCount: STREAM_INIT_COUNT,
            loadCount: STREAM_LOAD_COUNT,
            autoUpdate: true,
            streamEntryClass: ActivityStreamEntry,
        });
    });

    ActivityStream.prototype.initEvents = function(events) {
        var that = this;
        this.on('humhub:stream:afterAddEntries', function() {
            if(view.isLarge() && !that.$content.getNiceScroll().length) {
                that.$content.niceScroll({
                    cursorwidth: "7",
                    cursorborder: "",
                    cursorcolor: "#555",
                    cursoropacitymax: "0.2",
                    nativeparentscrolling: false,
                    railpadding: {top: 0, right: 3, left: 0, bottom: 0}
                });
            } else {
                that.$content.getNiceScroll().resize();
            }
        });
    };

    ActivityStream.prototype.isUpdateAvailable = function(events) {
        var that = this;

        var updatesAvailable = false;
        events.forEach(function(event) {
            if(that.entry(event.data.contentId)) {
                return;
            }

            if(event.data.streamChannel !== 'activity') {
                return;
            }

            if(event.data.originator === user.guid()) {
                return;
            }

            if(container.guid() === event.data.sguid || container.guid() === event.data.uguid) {
                updatesAvailable = true;
            }
        });

        return updatesAvailable;
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
