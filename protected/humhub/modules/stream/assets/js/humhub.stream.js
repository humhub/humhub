/**
 * Core module for managing Streams and StreamItems
 * @type Function
 */
humhub.module('stream', function(module, require, $) {

    var util = require('util');
    var object = util.object;
    var string = util.string;
    var client = require('client');
    var Content = require('content').Content;
    var Component = require('action').Component;
    var loader = require('ui.loader');
    var event = require('event');
    var additions = require('ui.additions');

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
     * The data-stream attribute of the stream root contains the stream url used for loading
     * stream entries.
     * 
     * @type String
     */
    var DATA_STREAM_URL = 'stream';

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


    var FILTER_INCLUDE_ARCHIVED = 'entry_archived';

    var streams = {};

    /**
     * Represents a stream entry within a stream.
     * You can receive a StreamEntry instance by calling
     * 
     * var entry = humhub.modules.stream.getStream().entry($myEntryContentId);
     * 
     * @param {type} id
     * @returns {undefined}
     */
    var StreamEntry = function(id) {
        Content.call(this, id);
        // Set the stream so we have it even if the entry is detached.
        this.stream();
    };

    object.inherits(StreamEntry, Content);

    StreamEntry.prototype.actions = function() {
        return ['delete', 'edit'];
    };

    StreamEntry.prototype.delete = function() {
        // Either call delete of a nestet content component or call default content delete
        var content = this.contentComponent();
        var promise = (content && content.delete) ? content.delete() : this.super('delete');

        var stream = this.stream();
        promise.then(function($confirm) {
            if($confirm) {
                module.log.success('success.delete');
            }
        }).catch(function(err) {
            module.log.error(err, true);
        }).finally(function() {
            stream.onChange();
        });
    };

    StreamEntry.prototype.contentComponent = function() {
        var children = Component.find(this.getContent(), '[data-content-component]', true);
        return children.length ? children[0] : undefined;
    };

    StreamEntry.prototype.reload = function() {
        return this.stream().reloadEntry(this);
    };

    StreamEntry.prototype.replaceContent = function(html) {
        var that = this;
        return new Promise(function(resolve, reject) {
            that.getContent().replaceWith(html);
            resolve(that);
        });
    };

    StreamEntry.prototype.edit = function(evt) {
        var that = this;

        that.loader();
        client.html(evt).then(function(response) {
            that.$.find('.stream-entry-edit-link').hide();
            that.$.find('.stream-entry-cancel-edit-link').show();
            that.setEditContent(response.html);
        }).catch(function(e) {
            module.log.error(e, true);
        }).finally(function() {
            that.loader(false);
        });
    };

    StreamEntry.prototype.setEditContent = function(content) {
        this.replaceContent(content);
        this.$.find('.stream-entry-addons > .hideOnEdit').remove();
        this.apply();
        this.$.find('input[type="text"]:visible, textarea:visible, [contenteditable="true"]:visible').first().focus();
    };

    StreamEntry.prototype.cancelEdit = function() {
        var $that = this.$;
        this.loader();
        this.reload().then(function() {
            $that.find('.stream-entry-edit-link').show();
            $that.find('.stream-entry-cancel-edit-link').hide();
        });
    };

    StreamEntry.prototype.apply = function() {
        additions.applyTo(this.$);
    };

    /**
     * Edit submit action event.
     * 
     * @param {type} evt
     * @returns {undefined}
     */
    StreamEntry.prototype.editSubmit = function(evt) {
        var that = this;
        client.submit(evt, {
            url: evt.url,
            dataType: 'html',
        }).status({
            200: function(response) {
                that.$.html(response.html);
                module.log.success('success.edit');
                that.apply();
                that.highlight();
            },
            400: function(response) {
                that.replaceContent(response.html);
            }
        }).catch(function(e) {
            module.log.error(e, true);
        });
    };

    StreamEntry.prototype.loader = function($show) {
        var $loader = this.$.find('.stream-entry-loader');
        if($show === false) {
            loader.reset($loader);
            this.$.find('.preferences').show();
            return;
        }

        this.$.find('.preferences').hide();
        loader.set($loader, {
            'position': 'left',
            'size': '8px',
            'css': {
                'padding': '0px',
                 width: '60px' 
            }
        });
    };

    StreamEntry.prototype.getContent = function() {
        return this.$.find('.content, .content_edit').first();
    };

    StreamEntry.prototype.highlight = function() {
        additions.highlight(this.getContent());
    };

    StreamEntry.prototype.stick = function(evt) {
        var that = this;
        this.loader();
        var stream = this.stream();
        client.post(evt.url, evt).then(function(data) {
            if(data.success) {
                that.remove().then(function() {
                    stream.loadEntry(that.getKey(), {'prepend': true});
                });
                module.log.success('success.stick');
            } else if(data.info) {
                module.log.info(data.info, true);
            } else {
                module.log.error(data.error, true);
            }
        }, evt).catch(function(e) {
            module.log.error(e, true);
        }).finally(function() {
            that.loader(false);
        });
    };

    StreamEntry.prototype.replace = function(newEntry) {
        var that = this;
        return new Promise(function(resolve, reject) {
            var $newEntry = $(newEntry).hide();
            that.$.fadeOut(function() {
                that.$.replaceWith($newEntry);
                // Sinc the response does not only include the node itself we have to search it.
                that.$ = $newEntry.find(DATA_STREAM_ENTRY_SELECTOR)
                        .addBack(DATA_STREAM_ENTRY_SELECTOR);
                $newEntry.fadeIn(resolve);
                that.apply();
            });

        });
    };


    StreamEntry.prototype.unstick = function(evt) {
        var that = this;
        this.loader();
        client.post(evt.url).then(function(data) {
            that.stream().init();
            module.log.success('success.unstick');
        }).catch(function(e) {
            module.log.error(e, true);
            that.loader(false);
        });
    };

    StreamEntry.prototype.archive = function(evt) {
        var that = this;
        this.loader();
        client.post(evt.url).then(function(response) {
            if(response.success) {
                // Either just remove entry or reload it in case the stream includes arhcived entries
                if(that.stream().hasFilter(FILTER_INCLUDE_ARCHIVED)) {
                    that.reload().then(function() {
                        module.log.success('success.archive', true);
                    });
                } else {
                    that.remove().then(function() {
                        module.log.success('success.archive', true);
                    });
                }
            } else {
                module.log.error(response, true);
            }
        }).catch(function(e) {
            module.log.error(e, true);
            that.loader(false);
        });
    };

    StreamEntry.prototype.remove = function() {
        var stream = this.stream();
        return this.super('remove')
                .then($.proxy(stream.onChange, stream));
    };

    StreamEntry.prototype.unarchive = function(evt) {
        var that = this;
        this.loader();
        client.post(evt.url).then(function(response) {
            if(response.success) {
                that.reload().then(function() {
                    module.log.success('success.unarchive', true);
                });
            }
        }).catch(function(e) {
            module.log.error('Unexpected error', e, true);
            that.loader(false);
        });
    };

    StreamEntry.prototype.stream = function() {
        // Just return the parent stream component.
        if(!this.$.data('stream')) {
            return this.$.data('stream', this.parent());
        }

        return this.$.data('stream');
    };

    /**
     * Generic Stream implementation.
     * 
     * @param {type} container id or jQuery object of the stream container
     * @returns {undefined}
     */
    var Stream = function(container, cfg) {
        Component.call(this, container);
        this.cfg = this.initConfig(cfg);

        //If a contentId is set on the stream, the root we will only show a single entry
        if(this.$.data(DATA_STREAM_CONTENTID)) {
            this.contentId = parseInt(this.$.data(DATA_STREAM_CONTENTID));
        }

        this.$stream = this.$;

        //Cache some stream relevant data/nodes
        this.url = this.$.data(DATA_STREAM_URL);
        this.$content = this.$.find(this.cfg['contentSelector']);
        this.$filter = this.cfg['filterPanel'];

        //TODO: make this configurable
        this.sort = "c";
    };

    object.inherits(Stream, Component);

    /**
     * Initializes the stream configuration with default values.
     * 
     * @param {type} cfg
     * @returns {humhub_stream_L5.Stream.prototype.initConfig.cfg}
     */
    Stream.prototype.initConfig = function(cfg) {
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
    Stream.prototype.getContentActions = function() {
        return [];
    };

    /**
     * Initializes the stream, by clearing the stream and reloading initial stream entries,
     * this should be called if any filter/sort settings are changed or the stream
     * needs an reload.
     * 
     * @returns {humhub.stream_L5.Stream.prototype}
     */
    Stream.prototype.init = function() {
        var that = this;
        return new Promise(function(resolve, reject) {
            that.clear();
            that.$.show();
            that._init().then(function() {
                resolve(that);
            }).catch(function(err) {
                module.log.error(err, true);
                that.$content.append('Stream could not be initilaized!');
                reject(err);
            });
        });
    };

    Stream.prototype._init = function() {
        var promise;
        if(this.isShowSingleEntry()) {
            promise = this.loadEntry(this.contentId);
        } else {
            promise = this.loadEntries({'limit': this.cfg['loadInitialCount'], loader: true});
        }

        var that = this;
        this.$.off('click').on('click', '.singleBackLink', function() {
            that.contentId = undefined;
            that.init();
            $(this).hide();
        });

        return promise;
    };

    /**
     * Clears the stream content.
     * 
     * @returns {undefined}
     */
    Stream.prototype.clear = function() {
        this.lastEntryLoaded = false;
        this.loading = false;
        this.$content.empty();
        this.$.hide();
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
    Stream.prototype.loadEntry = function(contentId, cfg) {
        cfg = cfg || {};
        cfg['contentId'] = contentId;

        var that = this;

        return new Promise(function(resolve, reject) {
            that.loadEntries(cfg).then(function($entryNode) {
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
    Stream.prototype.reloadEntry = function(entry) {
        var that = this;
        return new Promise(function(resolve, reject) {
            entry = (object.isString(entry)) ? that.entry(entry) : entry;

            if(!entry) {
                module.log.warn('Attempt to reload non existing entry');
                return reject();
            }

            var contentId = entry.getKey();
            that.loadEntry(contentId, {'preventInsert': true}).then(function($entryNode) {
                // If no entry was returned it means it is not visible in the current scope
                if(!$entryNode || !$entryNode.length) {
                    entry.remove();
                    resolve(entry);
                } else {
                    entry.replace($entryNode).then(resolve);
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
    Stream.prototype.loadEntries = function(cfg) {
        // Overwrite the default stream settings if provided
        cfg = this.initLoadConfig(cfg);

        this.$.trigger('humhub:modules:stream:beforeLoadEntries', [this, cfg]);

        var that = this;
        return new Promise(function(resolve, reject) {
            var $result;
            // Don't proceed if stream is already loading
            if(that.loading || that.lastEntryLoaded) {
                resolve();
                return;
            }

            if(cfg.loader) {
                that.showLoader();
            }
            that.loading = true;
            that._load(cfg).then(function(response) {
                if(cfg.loader) {
                    that.hideLoader();
                }

                // If its not a single entry load and we get no content, we expect last entry is loaded
                // This may have to be change if we require to reload multiple elements.
                if(!cfg['contentId'] && object.isEmpty(response.content)) {
                    that.lastEntryLoaded = true;
                    that.$.trigger('humhub:modules:stream:lastEntryLoaded');
                    //We call onChange here, since we want to display empty messages in case its the first call
                    that.onChange();
                } else if(!cfg['contentId']) {
                    that.lastEntryLoaded = response.isLast;
                    $result = that.addEntries(response, cfg);
                } else {
                    $result = that.addEntries(response, cfg);
                }

                that.loading = false;
                that.$.trigger('humhub:modules:stream:afterLoadEntries', this);
                resolve($result);
            }).catch(function(err) {
                that.loading = false;
                if(cfg.loader) {
                    that.hideLoader();
                }
                reject(err);
            });
        });
    };

    Stream.prototype.initLoadConfig = function(cfg) {
        cfg = cfg || {};
        if(!object.isDefined(cfg['contentId'])) {
            cfg['limit'] = object.isDefined(cfg['limit']) ? cfg['limit'] : this.loadCount;
            cfg['from'] = object.isDefined(cfg['from']) ? cfg['from'] : this.getLastContentId();
            cfg['sort'] = cfg['sort'] || this.sort;
        } else {
            cfg['limit'] = 1;
        }

        cfg['filter'] = cfg['filter'] || this.getFilterString();

        cfg['prepend'] = object.isDefined(cfg['prepend']) ? cfg['prepend'] : false;
        return cfg;
    };

    Stream.prototype.showLoader = function() {
        loader.remove(this.$content);
        loader.append(this.$content);
    };

    Stream.prototype.hideLoader = function() {
        this.$content.find('.humhub-ui-loader').remove();
    };

    Stream.prototype._load = function(cfg) {
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
    Stream.prototype.getLastContentId = function() {
        var $lastEntry = this.$.find(DATA_STREAM_ENTRY_SELECTOR).last();
        if($lastEntry.length) {
            return $lastEntry.data(DATA_STREAM_ENTRY_ID_SELECTOR);
        }
    };

    Stream.prototype.prependEntry = function(html) {
        var $html = $(html).hide();
        this.$content.prepend($html);
        additions.applyTo($html);
        $html.fadeIn();
        this.onChange();
    };

    Stream.prototype.appendEntry = function(html) {
        var $html = $(html).hide();
        this.$content.append($html);
        additions.applyTo($html);
        $html.fadeIn();
        this.onChange();
    };


    /**
     * Appends all entries of a given stream response to the stream content.
     * 
     * @param {type} response
     * @returns {unresolved}
     */
    Stream.prototype.addEntries = function(response, cfg) {
        var that = this;
        var result = '';
        $.each(response.contentOrder, function(i, key) {
            var $entry = that.entry(key);
            if($entry.length) {
                $entry.remove();
            }
            result += response.content[key].output;
        });


        var $result = $(result).hide();

        if(cfg['preventInsert']) {
            return $result;
        }

        this.$.trigger('humhub:modules:stream:beforeAddEntries', [response, result]);

        if(cfg['prepend']) {
            this.prependEntry($result);
        } else {
            this.appendEntry($result);
        }

        this.$.trigger('humhub:modules:stream:afterAddEntries', [response, result]);
        $result.fadeIn('fast');
        return $result;
    };

    /**
     * Fired stream entries changed
     */
    Stream.prototype.onChange = function() {
        // abstract onChange function
    };

    /**
     * Checks if the stream is single entry mode.
     * @returns {boolean}
     */
    Stream.prototype.isShowSingleEntry = function() {
        return object.isDefined(this.contentId);
    };

    /**
     * Checks if the stream has entries loaded.
     * 
     * @returns {boolean}
     */
    Stream.prototype.hasEntries = function() {
        return this.getEntryCount() > 0;
    };

    /**
     * Returns the count of loaded stream entries.
     * 
     * @returns {humhub_stream_L5.Stream.$.find.length}
     */
    Stream.prototype.getEntryCount = function() {
        return this.$.find(DATA_STREAM_ENTRY_SELECTOR).length;
    };

    /**
     * Returns all stream entry nodes.
     * 
     * @returns {unresolved}
     */
    Stream.prototype.getEntryNodes = function() {
        return this.$.find(DATA_STREAM_ENTRY_SELECTOR);
    };

    /**
     * Checks if a stream has filter settings.
     * @returns {boolean}
     */
    Stream.prototype.hasFilter = function() {
        var filters = this.$.data('filters') || [];
        return filters.length > 0;
    };

    /**
     * Creates a filter string out of the filter array.
     * @returns {string}
     */
    Stream.prototype.getFilterString = function() {
        var result = '';
        $.each(this.$.data('filters'), function(i, filter) {
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
    Stream.prototype.setFilter = function(filterId) {
        var filters = this.$.data('filters') || [];
        if(filters.indexOf(filterId) < 0) {
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
    Stream.prototype.unsetFilter = function(filterId) {
        var filters = this.$.data('filters') || [];
        var index = filters.indexOf(filterId);
        if(index > -1) {
            filters.splice(index, 1);
        }
        this.$.data('filters', filters);
        return this;
    };

    /**
     * Returns a StreamEntry instance for a iven content id.
     * @param {type} key
     * @returns {humhub_stream_L5.StreamEntry}
     */
    Stream.prototype.entry = function(key) {
        return new this.cfg.streamEntryClass(this.$.find(DATA_STREAM_ENTRY_SELECTOR + '[data-content-key="' + key + '"]'));
    };

    /**
     * Creates a new StreamEntry out of the given childNode.
     * @param {type} $childNode
     * @returns {humhub_stream_L5.StreamEntry}
     */
    Stream.prototype.getEntryByNode = function($childNode) {
        return new this.cfg.streamEntryClass($childNode.closest(DATA_STREAM_ENTRY_SELECTOR));
    };

    /**
     * Stream implementation for main wall streams.
     * 
     * @param {type} container
     * @param {type} cfg
     * @returns {undefined}
     */
    var WallStream = function(container, cfg) {
        cfg = cfg || {};
        cfg['filterPanel'] = $('.wallFilterPanel');
        Stream.call(this, container, cfg);
        
        if (module.config.horizontalImageScrollOnMobile && /Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(navigator.userAgent)) {
            this.$.addClass('mobile');
        }

        var that = this;
        this.$.on('humhub:modules:stream:clear', function() {
            that.$.find(".emptyStreamMessage").hide();
            that.$.find(".emptyFilterStreamMessage").hide();
            that.$.find('.back_button_holder').hide();
        });

        this.$.on('humhub:modules:stream:afterAppendEntries', function(evt, stream) {
            if(that.isShowSingleEntry()) {
                that.$.find('.back_button_holder').show();
            }
        });

        this.$.on('humhub:modules:stream:lastEntryLoaded', function() {
            $('#btn-load-more').hide();
        });

    };

    object.inherits(WallStream, Stream);

    WallStream.prototype.onChange = function() {
        var hasEntries = this.hasEntries();
        if(!hasEntries && !this.hasFilter()) {
            this.$.find('.emptyStreamMessage').show();
            this.$filter.hide();
        } else if(!hasEntries) {
            this.$.find('.emptyFilterStreamMessage').hide();
        } else if(!this.isShowSingleEntry()) {
            this.$filter.show();
            this.$.find('.emptyStreamMessage').hide();
            this.$.find('.emptyFilterStreamMessage').hide();
        }

        this.$entryCache = this.getEntryNodes();
    };

    /**
     * Initializes wall stream
     * @returns {undefined}
     */
    var init = function() {
        streams = {};

        var stream = getStream();

        if(!stream) {
            module.log.info('Non-Wall-Stream Page!');
            _unload();
            return;
        } else {
            _initWallStream(stream);
            _initFilterNav();
        }
    };
    
    var _unload = function() {
        $(window).off('scroll.humhub:modules:stream');
    };

    var _initWallStream = function(stream) {
        if(!stream) {
            stream = getStream();
        }

        stream.init();

        event.on('humhub:modules:content:newEntry', function(evt, html) {
            stream.prependEntry(html);
        });

        $(window).off('scroll.humhub:modules:stream').on('scroll.humhub:modules:stream', function() {
            if(stream.isShowSingleEntry()) {
                return;
            }
            var $window = $(window);
            var scrollTop = $window.scrollTop();
            var windowHeight = $window.height();
            if(scrollTop === ($(document).height() - $window.height())) {
                if(stream && !stream.loading && !stream.isShowSingleEntry() && !stream.lastEntryLoaded) {
                    stream.loadEntries({loader:true});
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
    };

    var _initFilterNav = function() {
        $(".wallFilter").click(function() {
            var $filter = $(this);
            var checkboxi = $filter.children("i");
            checkboxi.toggleClass('fa-square-o').toggleClass('fa-check-square-o');
            if(checkboxi.hasClass('fa-check-square-o')) {
                getStream().setFilter($filter.attr('id').replace('filter_', ''));
            } else {
                getStream().unsetFilter($filter.attr('id').replace('filter_', ''));
            }
            getStream().init();
        });

        $(".wallSorting").click(function() {
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

    var getStream = function($selector) {
        $selector = $selector || DATA_WALL_STREAM_SELECTOR;
        if(!streams[$selector]) {
            var $stream = (!$selector) ? $(DATA_WALL_STREAM_SELECTOR) : $($selector).first();
            return streams[$selector] = $stream.length ? new WallStream($stream) : undefined;
        }
        return streams[$selector];
    };

    var getEntry = function(id) {
        return module.getStream().entry(id);
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