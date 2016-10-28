/**
 * Core module for managing Streams and StreamItems
 * @type Function
 */
humhub.initModule('stream', function (module, require, $) {

    var util = require('util');
    var object = util.object;
    var string = util.string;
    var client = require('client');
    var Content = require('content').Content;
    var Component = require('action').Component;
    var loader = require('ui.loader');
    var event = require('event');
    var log = require('log').module(module);

    /**
     * Number of initial stream enteis loaded when stream is initialized.
     * @type Number
     */
    var STREAM_INIT_COUNT = 8;

    /**
     * Number of stream entries loaded with each request (except initial request)
     * @type Number
     */
    var STREAM_LOAD_COUNT = 4;

    /**
     * Set on the stream root node to identify a stream. The value of this data
     * attribute contains the stream url for loading new entries.
     * @type String
     */
    var DATA_STREAM_SELECTOR = '[data-stream]';

    /**
     * Number of stream entries loaded with each request (except initial request)
     * @type Number
     */
    var DATA_WALL_STREAM_SELECTOR = '#wallStream';

    /**
     * Set on a stream entry root node to identify stream-entries.
     * @type String
     */
    var DATA_STREAM_ENTRY_SELECTOR = '[data-stream-entry]';

    /**
     * If a data-stream-contentid is set on the stream root only one entry will
     * be loaded. e.g. for permlinks
     * @type String
     */
    var DATA_STREAM_CONTENTID = 'stream-contentid';

    /**
     * If a data-stream-contentid is set on the stream root only one entry will
     * be loaded. e.g. for permlinks
     * @type String
     */
    var DATA_STREAM_ENTRY_ID_SELECTOR = 'content-key';


    var streams = {};

    /**
     * Represents an stream entry within a stream.
     * @param {type} id
     * @returns {undefined}
     */
    var StreamEntry = function (id) {
        Content.call(this, id);
    };

    object.inherits(StreamEntry, Content);

    StreamEntry.prototype.actions = function () {
        return ['delete', 'edit'];
    };

    StreamEntry.prototype.delete = function () {
        var content = this.getContentComponent();
        if (content && content.delete) {
            content.delete();
        } else {
            StreamEntry._super.delete.call(this);
        }
    };

    StreamEntry.prototype.getContentComponent = function () {
        var children = this.children();
        return children.length ? children[0] : undefined;
    };

    StreamEntry.prototype.reload = function () {
        return getStream().reloadEntry(this);
    };

    StreamEntry.prototype.edit = function (evt) {
        var that = this;
        this.loader();
        client.get(evt.url, {
            dataType: 'html',
            success: function (response) {
                var $content = that.$.find('.content:first');
                var $oldContent = $content.clone();
                $content.replaceWith(response.html);
                that.$.data('oldContent', $oldContent);
                that.$.find('input[type="text"], textarea, [contenteditable="true"]').first().focus();
                that.unsetLoader();
            },
            error: function(e) {
                //TODO: handle error
                that.unsetLoader();
            }
        });

        // Listen to click events outside of the stream entry and cancel edit.
        $('body').off('click.humhub:modules:stream:edit').on('click.humhub:modules:stream:edit', function (e) {
            if (!$(e.target).closest('[data-content-key="' + that.getKey() + '"]').length) {
                var $editContent = that.$.find('.content_edit:first');
                if ($editContent && that.$.data('oldContent')) {
                    $editContent.replaceWith(that.$.data('oldContent'));
                    that.$.data('oldContent', undefined);
                }
                $('body').off('click.humhub:modules:stream:edit');
            }
        });
    };

    StreamEntry.prototype.loader = function (selector) {
        //selector = selector || '.content:first';
        selector = selector || '.entry-loader';
        loader.set(this.$.find(selector), {
            'position': 'left',
            'size': '8px',
            'css': {
                'padding': '0px'
            }
        });
    };
    
    StreamEntry.prototype.unsetLoader = function (selector) {
        //selector = selector || '.content:first';
        selector = selector || '.entry-loader';
        loader.reset(this.$.find(selector));
    };

    StreamEntry.prototype.editSubmit = function (evt) {
        var that = this;
        client.submit(evt.$form, {
            url: evt.url,
            dataType: 'html',
            beforeSend: function () {
                //that.loader('.content_edit:first');
                that.loader();
            },
            success: function (response) {
                that.$.html(response.html);
            }
        });
    };

    StreamEntry.prototype.stick = function (evt) {
        var that = this;
        this.loader();
        var stream = that.getStream();
        client.post(evt.url).done(function (data) {
            if (data.success) {
                that.remove().then(function () {
                    stream.loadEntry(that.getKey(), {'prepend': true});
                });
            }
        });
    };

    StreamEntry.prototype.unstick = function (evt) {
        this.loader();
        client.post(evt.url).done(function (data) {
            module.init();
        });
    };

    StreamEntry.prototype.archive = function (evt) {
        var that = this;
        this.loader();
        client.post(evt.url).then(function (response) {
            if (response.success) {
                that.reload().then(function () {
                    log.info(module.text('info.archive.success'), true);
                });
            }
        }).catch(function (e) {
            log.error(e, true);
        });
    };

    StreamEntry.prototype.unarchive = function (evt) {
        var that = this;
        this.loader();
        client.post(evt.url).then(function (response) {
            if (response.success) {
                that.reload().then(function () {
                    log.info(module.text('info.unarchive.success'), true);
                });
            }
        }).catch(function (e) {
            log.error('Unexpected error', e, true);
        });
    };

    StreamEntry.prototype.getStream = function () {
        // Just return the parent stream component.
        return this.parent();
    };

    /**
     * Generic Stream implementation.
     * 
     * @param {type} container id or jQuery object of the stream container
     * @returns {undefined}
     */
    var Stream = function (container, cfg) {
        Component.call(this, container);
        this.cfg = this.initConfig(cfg);

        //If a contentId is set on the stream, the root we will only show a single entry
        if (this.$.data(DATA_STREAM_CONTENTID)) {
            this.contentId = parseInt(this.$.data(DATA_STREAM_CONTENTID));
        }

        this.$stream = this.$;

        //Cache some stream relevant data/nodes
        this.url = this.$.data('stream');
        this.$loader = this.$stream.find(this.cfg['loaderSelector']);
        this.$content = this.$stream.find(this.cfg['contentSelector']);
        this.$filter = this.cfg['filterPanel'];

        //TODO: make this configurable
        this.filters = [];
        this.sort = "c";
    };

    object.inherits(Stream, Component);

    /**
     * Initializes the stream configuration with default values.
     * 
     * @param {type} cfg
     * @returns {humhub_stream_L5.Stream.prototype.initConfig.cfg}
     */
    Stream.prototype.initConfig = function (cfg) {
        cfg = cfg || {};
        cfg['filterPanel'] = cfg['filterPanel'] || $('<div></div>');
        cfg['loaderSelector'] = cfg['loaderSelector'] || ".streamLoader";
        cfg['filterSelector'] = cfg['filterSelector'] || ".wallFilterPanel";
        cfg['contentSelector'] = cfg['contentSelector'] || "[data-stream-content]";
        cfg['loadInitialCount'] = cfg['loadInitialCount'] || STREAM_INIT_COUNT;
        cfg['loadCount'] = cfg['loadCount'] || STREAM_LOAD_COUNT;
        cfg['streamEntryClass'] = cfg['streamEntryClass'] || StreamEntry;
        return cfg;
    };

    /**
     * The stream itself does not provide any content actions.
     * 
     * @returns {Array}
     */
    Stream.prototype.getContentActions = function () {
        return [];
    };

    /**
     * Initializes the stream, by clearing the stream and reloading initial stream entries,
     * this should be called if any filter/sort settings are changed or the stream
     * needs an reload.
     * 
     * @returns {humhub.stream_L5.Stream.prototype}
     */
    Stream.prototype.init = function () {
        this.clear();
        this.$stream.show();

        if (this.isShowSingleEntry()) {
            this.loadEntry(this.contentId);
        } else {
            this.loadEntries({'limit': this.cfg['loadInitialCount']}).then(function () {
                /**
                 * TODO: REWRITE OLD INITPLUGINS!!!
                 */
                initPlugins();
            });
        }

        return this;
    };

    /**
     * Clears the stream content.
     * 
     * @returns {undefined}
     */
    Stream.prototype.clear = function () {
        this.lastEntryLoaded = false;
        this.loading = false;
        this.$content.empty();
        this.$stream.hide();
        //this.$.find(".s2_single").hide();
        this.hideLoader();
        this.$filter.hide();
        this.$.trigger('humhub:modules:stream:clear', this);
    };

    /**
     * Loads a single stream entry by a given content id.
     * 
     * @param {type} contentId
     * @returns {undefined}
     */
    Stream.prototype.loadEntry = function (contentId, cfg) {
        cfg = cfg || {};
        cfg['contentId'] = contentId;

        var that = this;

        return new Promise(function (resolve, reject) {
            that.loadEntries(cfg).then(function ($entryNode) {
                resolve($entryNode);
            }).catch(reject);
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
            entry = (object.isString(entry)) ? that.getEntry(entry) : entry;

            if (!entry) {
                log.warn('Attempt to reload non existing entry');
                return reject();
            }

            var contentId = entry.getKey();
            that.loadEntry(contentId, {'preventInsert': true}).then(function ($entryNode) {
                if (!$entryNode || !$entryNode.length) {
                    entry.remove();
                    resolve(entry);
                } else {
                    entry.$.fadeOut();
                    entry.$.replaceWith($entryNode);
                    $entryNode.fadeIn(function () {
                        resolve(entry);
                    });
                }

            }, reject);
        });
    };

    /**
     * Loads new entries to a stream by the given stream settings.
     * 
     * @param {type} limit
     * @param {type} from
     * @param {type} filter
     * @param {type} sort
     * @returns {Promise|undefined}
     */
    Stream.prototype.loadEntries = function (cfg) {
        // Overwrite the default stream settings if provided
        cfg = this.initLoadConfig(cfg);

        this.$.trigger('humhub:modules:stream:beforeLoadEntries', [this, cfg]);

        var that = this;
        return new Promise(function (resolve, reject) {
            var $result;
            // Don't proceed if stream is already loading
            if (that.loading || that.lastEntryLoaded) {
                resolve();
                return;
            }

            that.showLoader();
            that.loading = true;
            that._load(cfg).then(function (response) {
                that.hideLoader();

                // If its not a single entry load and we get no content, we expect last entry is loaded
                // This may have to be change if we require to reload multiple elements.
                if (!cfg['contentId'] && object.isEmpty(response.content)) {
                    that.lastEntryLoaded = true;
                    that.$.trigger('humhub:modules:stream:lastEntryLoaded');
                } else {
                    that.lastEntryLoaded = response.isLast;
                    $result = that.addEntries(response, cfg['prepend']);
                }

                that.loading = false;
                that.onChange();
                that.$.trigger('humhub:modules:stream:afterLoadEntries', this);
                resolve($result);
            }).catch(function (err) {
                //TODO: handle error
                that.loading = false;
                that.hideLoader();
                reject(err);
            });
        });
    };

    Stream.prototype.initLoadConfig = function (cfg) {
        cfg = cfg || {};
        if (!object.isDefined(cfg['contentId'])) {
            cfg['limit'] = object.isDefined(cfg['limit']) ? cfg['limit'] : this.loadCount;
            cfg['from'] = object.isDefined(cfg['from']) ? cfg['from'] : this.getLastContentId();
            cfg['sort'] = cfg['sort'] || this.sort;
        } else {
            cfg['limit'] = 1;
        }

        cfg['filter'] = cfg['filter'] || this.getFilterString();

        cfg['prepend'] = object.isDefined(cfg['prepend']) ? cfg['prepend'] : false;
        return cfg;
    }

    Stream.prototype.showLoader = function () {
        loader.append(this.$content);
    };

    Stream.prototype.hideLoader = function () {
        this.$content.find('.humhub-ui-loader').remove();
    };

    Stream.prototype._load = function (cfg) {
        cfg = cfg || {}
        return client.ajax(this.url, {
            data: {
                filters: cfg.filter,
                sort: cfg.sort,
                from: cfg.from,
                limit: cfg.limit,
                id: cfg.contentId
            }
        });
    };

    /**
     * Returns the content id of the last entry loaded.
     * @returns {unresolved}
     */
    Stream.prototype.getLastContentId = function () {
        var $lastEntry = this.$stream.find(DATA_STREAM_ENTRY_SELECTOR).last();
        if ($lastEntry.length) {
            return $lastEntry.data(DATA_STREAM_ENTRY_ID_SELECTOR);
        }
    };

    Stream.prototype.prependEntry = function (html) {
        var $html = $(html).hide();
        this.$content.prepend($html);
        $html.fadeIn();
    };

    Stream.prototype.appendEntry = function (html) {
        var $html = $(html).hide();
        this.$content.append($html);
        $html.fadeIn();
    };


    /**
     * Appends all entries of a given stream response to the stream content.
     * 
     * @param {type} response
     * @returns {unresolved}
     */
    Stream.prototype.addEntries = function (response, cfg) {
        var that = this;
        var result = '';
        $.each(response.contentOrder, function (i, key) {
            var $entry = that.getEntry(key);
            if ($entry.length) {
                $entry.remove();
            }
            result += response.content[key].output;
        });


        var $result = $(result).hide();

        if (cfg['preventInsert']) {
            return $result;
        }

        this.$.trigger('humhub:modules:stream:beforeAddEntries', [response, result]);

        if (cfg['prepend']) {
            this.prependEntry($result);
        } else {
            this.appendEntry($result);
        }

        this.$.trigger('humhub:modules:stream:afterAddEntries', [response, result]);
        $result.fadeIn('fast');
        return $result;
    };

    /**
     * Fired when new entries are shown
     */
    Stream.prototype.onChange = function () {
        var hasEntries = this.hasEntries();
        if (!hasEntries && !this.hasFilter()) {
            this.$.find('.emptyStreamMessage').show();
            this.$filter.hide();
        } else if (!hasEntries) {
            this.$.find('.emptyFilterStreamMessage').hide();
        } else if (!this.isShowSingleEntry()) {
            this.$filter.show();
            this.$.find('.emptyStreamMessage').hide();
            this.$.find('.emptyFilterStreamMessage').hide();
        }

        this.$entryCache = this.getEntryNodes();
    };

    /**
     * Checks if the stream is single entry mode.
     * @returns {boolean}
     */
    Stream.prototype.isShowSingleEntry = function () {
        return object.isDefined(this.contentId);
    };

    /**
     * Checks if the stream has entries loaded.
     * 
     * @returns {boolean}
     */
    Stream.prototype.hasEntries = function () {
        return this.getEntryCount() > 0;
    };

    /**
     * Returns the count of loaded stream entries.
     * 
     * @returns {humhub_stream_L5.Stream.$.find.length}
     */
    Stream.prototype.getEntryCount = function () {
        return this.$.find(DATA_STREAM_ENTRY_SELECTOR).length;
    };

    /**
     * Returns all stream entry nodes.
     * 
     * @returns {unresolved}
     */
    Stream.prototype.getEntryNodes = function () {
        return this.$.find(DATA_STREAM_ENTRY_SELECTOR);
    };

    /**
     * Checks if a stream has filter settings.
     * @returns {boolean}
     */
    Stream.prototype.hasFilter = function () {
        return this.filters.length > 0;
    };

    /**
     * Creates a filter string out of the filter array.
     * @returns {string}
     */
    Stream.prototype.getFilterString = function () {
        var result = '';
        $.each(this.filters, function (i, filter) {
            result += filter + ',';
        });

        return string.cutsuffix(result, ',');
    };

    /**
     * Adds a given filterId to the filter array.
     * 
     * @param {type} filterId
     * @returns {undefined}
     */
    Stream.prototype.setFilter = function (filterId) {
        if (this.filters.indexOf(filterId) < 0) {
            this.filters.push(filterId);
        }
    };

    /**
     * Clears a given filter.
     * 
     * @param {type} filterId
     * @returns {undefined}
     */
    Stream.prototype.unsetFilter = function (filterId) {
        var index = this.filters.indexOf(filterId);
        if (index > -1) {
            this.filters.splice(index, 1);
        }
    };

    /**
     * Returns a StreamEntry instance for a iven content id.
     * @param {type} key
     * @returns {humhub_stream_L5.StreamEntry}
     */
    Stream.prototype.getEntry = function (key) {
        return new this.cfg.streamEntryClass(this.$.find(DATA_STREAM_ENTRY_SELECTOR + '[data-content-key="' + key + '"]'));
    };

    /**
     * Creates a new StreamEntry out of the given childNode.
     * @param {type} $childNode
     * @returns {humhub_stream_L5.StreamEntry}
     */
    Stream.prototype.getEntryByNode = function ($childNode) {
        return new this.cfg.streamEntryClass($childNode.closest(DATA_STREAM_ENTRY_SELECTOR));
    };

    /**
     * Stream implementation for main wall streams.
     * 
     * @param {type} container
     * @param {type} cfg
     * @returns {undefined}
     */
    var WallStream = function (container, cfg) {
        cfg = cfg || {};
        cfg['filterPanel'] = $('.wallFilterPanel');
        Stream.call(this, container, cfg);

        var that = this;
        this.$.on('humhub:modules:stream:clear', function () {
            that.$.find(".emptyStreamMessage").hide();
            that.$.find(".emptyFilterStreamMessage").hide();
            that.$.find('.back_button_holder').hide();
        });

        this.$.on('humhub:modules:stream:afterAppendEntries', function (evt, stream) {
            if (that.isShowSingleEntry()) {
                that.$.find('.back_button_holder').show();
            }
        });

        this.$.on('humhub:modules:stream:lastEntryLoaded', function () {
            $('#btn-load-more').hide();
        });

    };

    object.inherits(WallStream, Stream);

    var getStream = function ($selector) {
        $selector = $selector || DATA_WALL_STREAM_SELECTOR;
        if (!streams[$selector]) {
            var $stream = (!$selector) ? $(DATA_WALL_STREAM_SELECTOR) : $($selector).first();
            return streams[$selector] = $stream.length ? new WallStream($stream) : undefined;
        }
        return streams[$selector];
    };

    var getEntry = function (id) {
        return module.getStream().getEntry(id);
    };

    /**
     * Initializes wall stream
     * @returns {undefined}
     */
    var init = function () {
        streams = {};

        var stream = getStream();
        if (!stream) {
            console.log('Non-Stream Page!');
            return;
        }

        stream.init();

        event.on('humhub:modules:content:newEntry', function (evt, html) {
            stream.prependEntry(html);
        });

        $(window).scroll(function () {
            if (stream.isShowSingleEntry()) {
                return;
            }
            var $window = $(window);
            var scrollTop = $window.scrollTop();
            var windowHeight = $window.height();
            if (scrollTop === ($(document).height() - $window.height())) {
                if (stream && !stream.loading && !stream.isShowSingleEntry() && !stream.lastEntryLoaded) {
                    stream.loadEntries();
                }
            }

            /* 
             
             This can be used to trace the currently visible entries
             
             var lastKey;
             // Defines our base y position for changing the current entry
             var yLimit = scrollTop + (windowHeight / 2);
             
             // Get id of current scroll item
             //TODO: chache the entry nodes !
             var matchingNodes = stream.$entryCache.map(function () {
             var $this = $(this);
             if ($this.offset().top < yLimit) {
             return $this;
             }
             });
             
             // Get the id of the current element 
             var $current = matchingNodes[matchingNodes.length - 1];
             var currentKey = $current && $current.length ? $current.data('content-key') : "";
             
             if (lastKey !== currentKey) {
             lastKey = currentKey;
             // Set/remove active class
             }
             */
        });

        stream.$.on('click', '.singleBackLink', function () {
            stream.contentId = undefined;
            stream.init();
            $(this).hide();
        });

        initFilterNav();
    };

    var initFilterNav = function () {
        $(".wallFilter").click(function () {
            var $filter = $(this);
            var checkboxi = $filter.children("i");
            checkboxi.toggleClass('fa-square-o').toggleClass('fa-check-square-o');
            if (checkboxi.hasClass('fa-check-square-o')) {
                getStream().setFilter($filter.attr('id').replace('filter_', ''));
            } else {
                getStream().unsetFilter($filter.attr('id').replace('filter_', ''));
            }
            getStream().init();
        });

        $(".wallSorting").click(function () {
            var newSortingMode = $(this).attr('id');

            // uncheck all sortings
            $(".wallSorting").find('i')
                    .removeClass('fa-check-square-o')
                    .addClass('fa-square-o');

            // check current sorting mode
            $("#" + newSortingMode).children("i")
                    .removeClass('fa-square-o')
                    .addClass('fa-check-square-o');

            // remove sorting id append
            newSortingMode = newSortingMode.replace('sorting_', '');

            // Switch sorting mode and reload stream
            getStream().sort = newSortingMode;
            getStream().init();
        });
    };

    module.export({
        StreamEntry: StreamEntry,
        Stream: Stream,
        WallStream: WallStream,
        getStream: getStream,
        getEntry: getEntry,
        init: init
    });
});

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