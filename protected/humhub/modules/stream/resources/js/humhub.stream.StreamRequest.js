/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**do
 * Core module for managing Streams and StreamItems
 * @type Function
 */

humhub.module('stream.StreamRequest', function (module, require, $) {

    var util = require('util');
    var object = util.object;
    var client = require('client');

    /**
     * The stream request is responsible for sending stream entry requests and forwarding the result.
     *
     * Available options:
     *
     *  - url: Used as stream request url, if not given the url is fetched from the stream root element by means of the data-stream.
     *  - limit: Used to limit the result of a request if not given a module default is used
     *  - from: First content id which should be included in the result if not provided, the stream state is used
     *  - contentId: Used to load a single entry with a given id
     *  - loader: Weather or not triggering the stream loader for this request (default: true)
     *  - suppressionsOnly: Used for reloading suppressed entries
     *
     * @param stream
     * @param options
     * @constructor
     */
    var StreamRequest = function(stream, options) {
        this.options = options || {};
        this.stream = stream;
        this.stream.request = this;
        this.initOptions(options);
    };

    StreamRequest.prototype.initOptions = function(options) {
        this.contentId = this.options.contentId;
        this.loader = object.defaultValue(this.options.loader, !object.isDefined(this.options.insertAfter));
        this.url = object.defaultValue(this.options.url, this.stream.options.stream);
        this.limit = object.defaultValue(this.options.limit, this.stream.options.loadCount);
        this.from = object.defaultValue(this.options.from, this.stream.state.lastContentId);
        this.suppressionsOnly = this.options.suppressionsOnly;
        this.channel = this.options.channel;
    };

    StreamRequest.prototype.loadSingle = function(contentId) {
        this.options.contentId = contentId;
        return this.load();
    };

    StreamRequest.prototype.load = function() {
        this.stream.trigger('humhub:stream:beforeLoadEntries', [this.stream, this]);

        if(this.stream.isLoading()) {
            return Promise.resolve();
        }

        if(this.loader) {
            this.stream.loader.show(true);
        }

        this.stream.state.loading = true;
        this.stream.state.lastRequest = this;

        var that = this;
        return that._send().then(function (response) {
            that.response = response;

            if(that.loader) {
                that.stream.loader.show(false);
            }

            that.stream.state.loading = false;
            that.stream.trigger('humhub:stream:afterLoadEntries', [that.stream, this]);
            return that;
        }).finally(function () {
            that.stream.state.loading = false;
        });
    };

    StreamRequest.prototype._send = function () {
        var stream = this.stream;

        if (stream.currentXhr) {
            stream.currentXhr.abort();
        }

        return client.ajax(this.url, {data:  this.getRequestData(), beforeSend: function (xhr) {
            stream.currentXhr = xhr;
        }}).then(function(response) {
            stream.currentXhr = undefined;
            return response;
        });
    };

    StreamRequest.prototype.getRequestData = function() {
        var data = {};

        var that = this;

        if(!this.contentId) {
            data[this.buildRequestDataKey('sort')] = this.sort;
            data[this.buildRequestDataKey('from')] = this.from;
            data[this.buildRequestDataKey('limit')] = this.limit;
        }

        data[this.buildRequestDataKey('contentId')] = this.contentId;
        data[this.buildRequestDataKey('suppressionsOnly')] = this.suppressionsOnly;

        if(this.options.data) {
            $.each(this.options.data, function(key, value) {
                data[this.buildRequestDataKey(key)] = value;
            })
        }

        $.each(this.stream.filter.getFilterMap(), function(key, value) {
            data[that.buildRequestDataKey(key)] = value;
        });

        return data;
    };

    StreamRequest.prototype.buildRequestDataKey = function(key) {
        return 'StreamQuery['+key+']';
    };

    StreamRequest.prototype.isLastEntryResponse = function () {
        return !this.isSingleEntryRequest() && object.isEmpty(this.response.content);
    };

    StreamRequest.prototype.initRequestOptions = function () {
        var options = {};

        if(this.isSingleEntryRequest()) {
            options.limit = 1;
        } else {
            options.limit = this.options.limit;
            options.from = this.options.from;
            options.sort = this.options.sort;
            options.suppressionsOnly = this.options.suppressionsOnly;
        }

        options.prepend = object.isDefined(this.options.prepend) ? this.options.prepend : false;
        return options;
    };

    StreamRequest.prototype.getResultHtml = function() {
        var result = '';
        this.forEachResult(function(key, entry, output) {
            result += output;
        });
        return result;
    };

    StreamRequest.prototype.forEachResult = function(handler) {
        var that = this;
        $.each(this.response.contentOrder, function (i, key) {
            handler.call(null, key, that.response.content[key], that.response.content[key].output);
        });
    };

    StreamRequest.prototype.isSingleEntryRequest = function() {
        return !!this.contentId;
    };

    module.export = StreamRequest;
});
