/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Core module for managing Streams and StreamItems
 * @type Function
 */

humhub.module('stream.Stream', function (module, require, $) {

    var util = require('util');
    var object = util.object;
    var string = util.string;
    var Widget = require('ui.widget').Widget;
    var additions = require('ui.additions');
    var StreamEntry =  require('stream').StreamEntry;
    var Filter =  require('ui.filter').Filter;
    var StreamRequest =  require('stream').StreamRequest;
    var loader = require('ui.loader');
    var event = require('event');

    var EVENT_AFTER_ADD_ENTRIES = 'humhub:stream:afterAddEntries';
    var EVENT_BEFORE_ADD_ENTRIES = 'humhub:stream:beforeAddEntries';
    var EVENT_INITIALIZED = 'humhub:stream:initialized';

    /**
     * Number of initial stream entries loaded when stream is initialized.
     * @type Number
     */
    var STREAM_INIT_COUNT = 8;

    /**
     * Number of stream entries loaded with each request (except initial request)
     * @type Number
     */
    var STREAM_LOAD_COUNT = 4;


    /**
     * If a data-stream-contentid is set on the stream root only one entry will
     * be loaded. e.g. for permalinks
     * @type String
     */
    var DATA_STREAM_CONTENTID = 'stream-contentid';

    var StreamState = function (stream) {
        this.stream = stream;
        this.lastContentId = 0;
        this.lastEntryLoaded = false;
        this.loading = false;
    };

    var StreamLoader = function (stream) {
        this.stream = stream;
    };

    StreamLoader.prototype.show = function (show) {
        if (show !== false && !this.stream.$content.find('.loader').length) {
            loader.remove(this.stream.$content);
            loader.append(this.stream.$content);
        } else if (!show) {
            loader.remove(this.stream.$content);
        }
    };

    /**
     * Generic Stream implementation.
     *
     * @param {type} container id or jQuery object of the stream container
     * @returns {undefined}
     */
    var Stream = Widget.extend();

    Stream.prototype.onClear = function () {/* abstract onClear function */};

    Stream.prototype.initScroll = function () {
        if (window.IntersectionObserver && this.options.scrollSupport) {

            var options = { root: this.$content[0], rootMargin: "50px" };
            options = this.options.scrollOptions ? $.extend(options, this.options.scrollOptions) : options;
            var $streamEnd = $('<div class="stream-end"></div>');
            this.$content.append($streamEnd);

            var that = this;
            var observer = new IntersectionObserver(function (entries) {
                if (that.preventScrollLoading()) {
                    return;
                }

                if (entries.length && entries[0].isIntersecting) {
                    that.load().finally(function () {
                        that.state.scrollLock = false;
                    });
                }

            }, options);

            observer.observe($streamEnd[0]);
        }
    };

    Stream.prototype.preventScrollLoading = function () {
        return this.state.scrollLock || !this.canLoadMore() || !this.state.lastRequest || this.state.firstRequest.isSingleEntryRequest()
    };

    Stream.prototype.initEvents = function () {/* abstract initScroll function */};

    Stream.prototype.onUpdateAvailable = function (events) {
        var that = this;
        if (this.options.autoUpdate) {
            that.loadUpdate();
        }
    };

    Stream.prototype.initDefaultEvents = function () {
        var that = this;
        event.on('humhub:modules:content:live:NewContent.stream', function (evt, events) {
            if (!events
                || !that.state.initialized
                || !events.length
                || that.hasActiveFilters()
                || (that.state.firstRequest && that.state.firstRequest.isSingleEntryRequest())
                || !that.isUpdateAvailable(events)) {
                return;
            }

            that.onUpdateAvailable();
        });

        this.on(EVENT_INITIALIZED, function () {
            that.initScroll();
        });
    };

    Stream.prototype.isUpdateAvailable = function (events) {
        return false;
    };

    /**
     * Initializes the stream configuration with default values.
     *
     * @returns {object}
     */
    Stream.prototype.getDefaultOptions = function () {
        return {
            contentSelector: "[data-stream-content]",
            streamEntryClass: StreamEntry,
            loadCount: STREAM_LOAD_COUNT,
            initLoadCount: STREAM_INIT_COUNT
        };
    };

    /**
     * Initializes the stream by clearing the stream state and dom and reloading initial stream entries,
     * this should be called if any filter/sort settings are changed or the stream needs to be reloaded.
     *
     * @returns {Promise}
     */
    Stream.prototype.init = function () {
        if (this.state) {
            // When reloading the stream we ignroe the content id
            this.$.data(DATA_STREAM_CONTENTID, null);
        }

        this.state = new StreamState(this);

        if (!this.$content) {
            this.initWidget();
        }

        return this.clear()
            .show()
            .loadInit()
            .then($.proxy(this.handleResponse, this))
            .then($.proxy(this.updateTop, this))
            .then($.proxy(this.triggerInitEvent, this))
            .catch($.proxy(this.handleLoadError, this));
    };

    /**
     * Refreshes the first loaded entry. Note this entry should only be updated after initialization or when
     * loading updates to the top. Do not update this value when appending new content created by the user itself, e.g post form!
     *
     * @param response
     * @returns {*}
     */
    Stream.prototype.updateTop = function (response) {
        if (response) {
            this.topEntry = this.firstEntry(true);
        }
        return response;
    };

    Stream.prototype.triggerInitEvent = function (response) {
        this.trigger(EVENT_INITIALIZED, this);
        return response;
    };

    Stream.prototype.initWidget = function () {
        this.$content = this.$.find(this.options.contentSelector);
        this.loader = this.options.loader || new StreamLoader(this);
        this.initDefaultEvents();
        this.initEvents();
        this.initFilter();
    };

    Stream.prototype.initFilter = function () {
        this.filter = this.options.filter || new Filter();

        var that = this;
        this.filter.on('afterChange', function () {
            that.init();
        })
    };

    Stream.prototype.loadInit = function () {
        // content Id data is only relevant for the first request
        var contentId = this.$.data(DATA_STREAM_CONTENTID);

        this.state.firstRequest = new StreamRequest(this, {
            contentId: contentId,
            limit: this.options.initLoadCount
        });

        return this.state.firstRequest.load();
    };

    Stream.prototype.handleResponse = function (request) {
        // If request is undefined the request was blocked @see canLoadMore
        if (!request) {
            return Promise.resolve();
        }

        if (request.isLastEntryResponse()) {
            return Promise.resolve(this.handleLastEntryLoaded());
        } else if (request.options.insertAfter) {
            return this.handleInsertAfterResponse(request);
        } else if (request.options.prepend) {
            return this.prependResponseEntries(request);
        } else {
            return this.handleLoadMoreResponse(request);
        }
    };

    Stream.prototype.handleLoadError = function(err) {
        if (err.errorThrown === 'abort') {
            module.log.warn('Stream request aborted!');
        } else {
            module.log.error(err, true);
            this.$content.append('Stream could not be initialized!');
        }
    };

    /**
     * Loads a single stream entry by a given content id.
     *
     * @param {type} contentId
     * @returns {undefined}
     */
    Stream.prototype.loadEntry = function (contentId) {
        return new StreamRequest(this, {contentId: contentId}).load();
    };

    Stream.prototype.canLoadMore = function () {
        return !this.isLoading() && !this.state.lastEntryLoaded;
    };

    Stream.prototype.isLoading = function () {
        return this.state.loading === true;
    };

    Stream.prototype.lastEntryLoaded = function () {
        return this.state.lastEntryLoaded === true;
    };

    Stream.prototype.loadUpdate = function () {
        var topEntry = (this.topEntry) ? this.topEntry : Widget.instance(this.$.find(StreamEntry.SELECTOR+':first'));
        var from = topEntry ? topEntry.getKey() : 0;
        return this.load({
            'to': from,
            'prepend': true,
            'respectPinned': true,
            'loader': false,
            'limit': 20
        }).then($.proxy(this.updateTop, this));
    };

    Stream.prototype.load = function (options) {
        return new StreamRequest(this, options).load()
            .then($.proxy(this.handleResponse, this))
            .catch($.proxy(this.handleLoadError, this));
    };

    /**
     * @deprecated since v1.3 use load() instead
     * @param options
     */
    Stream.prototype.loadEntries = function (options) {
        return this.load(options);
    };

    /**
     * Clears the stream content.
     *
     * @returns {undefined}
     */
    Stream.prototype.clear = function () {
        this.hide();
        this.$content.empty();
        this.loader.show(false);
        this.trigger('humhub:stream:clear', this);
        this.onClear();
        return this;
    };

    Stream.prototype.handleLastEntryLoaded = function() {
        this.state.lastEntryLoaded = true;
        this.trigger('humhub:stream:lastEntryLoaded', [this]);
        this.onChange('afterLoadEntries');
    };

    Stream.prototype.handleLoadMoreResponse = function(request) {
        this.state.lastEntryLoaded = request.response.isLast;
        this.state.lastContentId = request.response.lastContentId;
        return this.addResponseEntries(request, request.options);
    };

    Stream.prototype.handleInsertAfterResponse = function(request) {
        this.addResponseEntries(request);
    };

    Stream.prototype.appendResponseEntries = function (request, options) {
        return this.addResponseEntries(request, options);
    };

    Stream.prototype.prependResponseEntries = function (request) {
        return this.addResponseEntries(request, {prepend : true});
    };

    Stream.prototype.insertResponseEntriesAfter = function (request, entryId) {
        return this.addResponseEntries(request, {insertAfter: entryId});
    };


    /**
     * Adds entries contained in a StreamRequest response.
     *
     * The way the result is added to the stream is defined by the following options:
     *
     *  - prepend: Prepends the entries of the response
     *  - insertAfter: Inserts the result after an already existing entry with the given contentId
     *
     *  If no specific options is set, the entries are just appended to the bottom of the stream content.
     *
     * @param {type} response
     * @returns {unresolved}
     */
    Stream.prototype.addResponseEntries = function (request, options) {
        options = $.extend(request.options, options || {});
        var that = this;
        var result = '';

        this.removeResponseEntries(request);
        var $result = $(request.getResultHtml());

        if(!$result.length) {
            return Promise.resolve();
        }

        this.$.trigger(EVENT_BEFORE_ADD_ENTRIES, [request.response, request, $result]);

        var promise;

        if (options.prepend) {
            promise = this.prependEntry($result, options.respectPinned);
        } else if (options.insertAfter) {
            promise = this.after($result, options.insertAfter);
        } else {
            promise = this.appendEntry($result);
        }

        return promise.then(function () {
            that.trigger(EVENT_AFTER_ADD_ENTRIES, [request.response, request, $result]);
            return request;
        });
    };

    /**
     * Removes already loaded entries from the stream which are also contained in the response.
     *
     * @param request
     */
    Stream.prototype.removeResponseEntries = function (request) {
        var that = this;
        request.forEachResult(function(key) {
            var $entry = that.entry(key);
            if ($entry) {
                $entry.remove();
            }
        });
    };

    /**
     * Prepends the given entry html to the stream and respects pinned posts if the respectPinnedPosts is set to true.
     *
     * @param html
     * @param respectPinned
     */
    Stream.prototype.prependEntry = function (html, respectPinned) {
        // Some streams do not support pinned posts order e.g. dashboard, in this case we can ignore pinned post order
        respectPinned = respectPinned && this.options.pinSupport;
        if (respectPinned) {
            var $pinned = this.$.find('[data-stream-pinned="1"]:last');
            if ($pinned.length) {
                return this.after(html, $pinned);
            }
        }

        return this._streamEntryAnimation(html, function ($html) {
            this.$content.prepend($html);
        });
    };

    /**
     * Appends the given entry html to the stream after the given entryNode the entry node can either.
     *
     * @param html
     * @param $entryNode
     */
    Stream.prototype.after = function (html, $entryNode) {
        return this._streamEntryAnimation(html, function ($html) {
            $entryNode.after($html);
        });
    };

    /**
     * Appends an entry html to the end of the stream content.
     * @param html
     */
    Stream.prototype.appendEntry = function (html) {
        var that = this;
        return this._streamEntryAnimation(html, function ($html) {
            var $streamEnd = that.$content.find('.stream-end:first');
            if ($streamEnd.length) {
                $streamEnd.before($html)
            } else {
                this.$content.append($html);
            }
        });
    };

    /**
     * Triggers an stream entry fade animation.
     *
     * @param html
     * @param insert
     * @returns {Promise}
     * @private
     */
    Stream.prototype._streamEntryAnimation = function (html, insert) {
        var that = this;

        return new Promise(function (resolve, reject) {

            var $html = $(html);

            // Filter out all script/links and text nodes
            var $elements = $html.not('script, link').filter(function () {
                return this.nodeType === 1; // filter out text nodes
            });

            // We use opacity because some additions require the actual size of the elements.
            $elements.css('opacity', 0);

            // call insert callback
            insert.call(that, $html);

            // apply additions to elements and fade them in.
            additions.applyTo($elements);

            $elements.imagesLoaded(function () {
                $.when($elements.hide().css('opacity', 1).fadeIn('fast')).then(function () {
                    that.onChange();
                    resolve();
                });
            });
        });

    };

    /**
     * Reloads a given entry either by providing the contentId or a StreamEntry instance.
     * This function returns a Promise instance.
     *
     * @param {string|StreamEntry} entry
     * @returns {Promise}
     */
    Stream.prototype.reloadEntry = function (entry) {
        var that = this;
        return new Promise(function (resolve, reject) {
            entry = (object.isString(entry)) ? that.entry(entry) : entry;

            if (!entry) {
                reject('Attempt to reload non existing entry');
                return;
            }

            entry.loader();

            that.loadEntry(entry.getKey()).then(function (request) {
                var $entryNode = $(request.getResultHtml());
                // If no entry was returned it means it is not visible in the current scope
                if (!$entryNode || !$entryNode.length) {
                    entry.remove();
                    resolve(entry);
                } else {
                    entry.replace($entryNode).then(resolve);
                }
            }, reject).finally(function () {
                entry.loader(false);
            });
        });
    };

    Stream.prototype.onChange = function () {
        var hasEntries = this.hasEntries();

        this.$.find('.streamMessage').remove();

        if(!hasEntries && this.isShowSingleEntry()) {
            // e.g. after content deletion in single entry stream
            var that = this;
            setTimeout(function() {that.init()}, 50);
        } else if (!hasEntries) {
            this.onEmptyStream();
        } else if (this.isShowSingleEntry()) {
            this.onSingleEntryStream();
        } else {
            this.filter.show();
        }
    };

    Stream.prototype.hasFilter = function (filter) {
        return this.filter.hasFilter(filter);
    };

    Stream.prototype.onEmptyStream = function () {
        var hasActiveFilters = this.hasActiveFilters();
        this.$.find('.streamMessage').remove();

        if(!this.isShowSingleEntry()) {
            this.$content.append(string.template(this.static('templates').streamMessage, {
                message: (hasActiveFilters) ? this.options.streamEmptyFilterMessage : this.options.streamEmptyMessage,
                cssClass: (hasActiveFilters) ? this.options.streamEmptyFilterClass : this.options.streamEmptyClass,
            }));
        }

        if(!hasActiveFilters) {
            this.filter.hide();
        } else {
            this.filter.show();
        }
    };

    Stream.prototype.onSingleEntryStream = function () {
        this.filter.hide();
    };

    Stream.templates = {
        streamMessage: '<div class="streamMessage {cssClass}"><div class="panel"><div class="panel-body">{message}</div></div></div>'
    };

    /**
     * Checks if the stream is single entry mode.
     * @returns {boolean}
     */
    Stream.prototype.isShowSingleEntry = function () {
        return this.state.lastRequest
            && this.$.data(DATA_STREAM_CONTENTID) === this.state.lastRequest.contentId
            && this.state.lastRequest.isSingleEntryRequest();
    };

    /**
     * Checks if the stream has entries loaded.
     *
     * @returns {boolean}
     */
    Stream.prototype.hasEntries = function () {
        return this.getEntryCount() > 0;
    };

    Stream.prototype.hasActiveFilters = function () {
        return this.filter && this.filter.getActiveFilterCount({exclude: 'sort'}) > 0;
    };

    /**
     * Returns the count of loaded stream entries.
     *
     * @returns {humhub_stream_L5.Stream.$.find.length}
     */
    Stream.prototype.getEntryCount = function () {
        return this.$.find(StreamEntry.SELECTOR).length;
    };

    /**
     * Returns all stream entry nodes.
     *
     * @returns {unresolved}
     */
    Stream.prototype.getEntryNodes = function () {
        return this.$.find(StreamEntry.SELECTOR);
    };

    Stream.prototype.updateFilterCount = function () {
        var count = this.$.data('filters') ? this.$.data('filters').length : 0;
        count += $('#stream_filter_content_type').val() ? $('#stream_filter_content_type').val().length : 0;
        count += $('#stream_filter_topic').val() ? $('#stream_filter_topic').val().length : 0;

        var $filterCount = $('#stream-filter-toggle').find('.filterCount');

        if(count) {
            if(!$filterCount.length) {
                $filterCount = $('<small class="filterCount"></small>').insertBefore($('#stream-filter-toggle').find('.caret'));
            }
            $filterCount.html(' <b>('+count+')</b> ');
        } else if($filterCount.length) {
            $filterCount.remove();
        }
    };

    /**
     * Adds a given filterId to the filter array.
     *
     * @param {type} filterId
     * @returns {undefined}
     */
    Stream.prototype.setFilter = function (filterId) {
        var filters = this.$.data('filters') || [];
        if (filters.indexOf(filterId) < 0) {
            filters.push(filterId);
        }
        this.$.data('filters', filters);
        return this;
    };

    /**
     * Clears a given filter.
     *
     * @param {type} filterId
     * @returns {undefined}
     */
    Stream.prototype.unsetFilter = function (filterId) {
        var filters = this.$.data('filters') || [];
        var index = filters.indexOf(filterId);
        if (index > -1) {
            filters.splice(index, 1);
        }
        this.$.data('filters', filters);
        return this;
    };

    /**
     * Returns a StreamEntry instance for a given content id.
     * @returns StreamEntry
     * @param ignorePinned
     */
    Stream.prototype.firstEntry = function(ignorePinned) {
        return ignorePinned
            ? this.entry(this.$.find('[data-stream-entry]:not([data-stream-pinned="1"]):first'))
            : this.entry(this.$.find('[data-stream-entry]:first'));
    };

    /**
     * Returns a StreamEntry instance for a given content id.
     * @param {type} key
     * @returns StreamEntry
     */
    Stream.prototype.entry = function (key) {
        var $entryNode = object.isString(key) || object.isNumber(key)
            ? this.$.find(StreamEntry.SELECTOR + '[data-content-key="' + key + '"]')
            : $(key);

        if(!$entryNode.length) {
            return null;
        }

        return new this.options.streamEntryClass($entryNode);
    };

    /**
     * Creates a new StreamEntry out of the given childNode.
     * @param {type} $childNode
     * @returns StreamEntry
     * @deprecated since 1.5 use entry() instead
     */
    Stream.prototype.getEntryByNode = function ($childNode) {
        return new this.cfg.streamEntryClass($childNode.closest(StreamEntry.SELECTOR));
    };

    Stream.prototype.actionLoadMore = function (evt) {
        this.loadEntries().finally(function () {
            evt.finish();
        });
    };

    module.export = Stream;
});
