/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Simple stream is used for
 * @type Function
 */

humhub.module('stream.wall', function (module, require, $) {

    var stream = require('stream');
    var Stream = stream.Stream;
    var Component = require('action').Component;
    var Widget = require('ui.widget').Widget;
    var event = require('event');
    var Filter = require('ui.filter').Filter;
    var string = require('util').string;
    var topic = require('topic');
    var view = require('ui.view');
    var loader = require('ui.loader');
    var container = require('content.container');
    var user = require('user');

    var DATA_STREAM_TOPIC = 'stream-topic';

    /**
     * Stream implementation for main wall streams.
     *
     * @param {type} container
     * @param {type} cfg
     * @returns {undefined}
     */
    var WallStream = Stream.extend(function (container, options) {
        options = options || {};

        options.scrollSupport = true;
        options.scrollOptions = { root: null, rootMargin: "300px" };
        options.filter =  Component.instance($('#wall-stream-filter-nav'), {stream : this});
        options.pinSupport = !this.isDashboardStream();

        Stream.call(this, container, options);

        if (module.config.horizontalImageScrollOnMobile && /Android|webOS|iPhone|iPad|iPod|BlackBerry|BB|PlayBook|IEMobile|Windows Phone|Kindle|Silk|Opera Mini/i.test(navigator.userAgent)) {
            this.$.addClass('mobile');
        }
    });

    WallStream.prototype.loadInit = function () {
        var initTopic = this.$.data(DATA_STREAM_TOPIC);
        if(initTopic) {
            topic.setTopics([initTopic]);
            this.$.data(DATA_STREAM_TOPIC, null);
        }

        return  this.super('loadInit');
    };

    WallStream.prototype.initEvents = function () {
        var that = this;
        this.on('humhub:stream:beforeLoadEntries.wallStream', function () {
            $('#btn-load-more').hide();
        }).on('humhub:stream:afterAddEntries.wallStream', function (evt, resp, res) {
            $.each(resp.contentSuppressions, function (key, contentSuppression) {
                var entry = that.entry(key);
                if(entry) {
                    contentSuppression.key = key;
                    $(string.template(WallStream.template.loadSuppressedButton, contentSuppression)).insertAfter(entry.$).fadeIn('fast');
                }
            });

            if(!resp.isLast) {
                $('#btn-load-more').show();
            }
        }).on('humhub:stream:lastEntryLoaded.wallStream', function () {
            $('#btn-load-more').hide();
        });

        event.on('humhub:content:beforeSubmit.wallStream', function (evt, html) {
            that.submitLock = true;
        });

        event.on('humhub:content:afterSubmit.wallStream', function (evt, html) {
            that.prependEntry(html, true).then(function() {
                that.submitLock = false;
            });
        });

        event.on('humhub:content:afterMove.wallStream', function (evt, response) {
            var entry = that.entry(response.id);
            if(entry) {
                if(that.isDashboardStream()) {
                    entry.reload();
                } else {
                    setTimeout($.proxy(entry.remove, entry), 1000);
                }
            }
        });
    };

    /**
     * TODO: Create own dasboard stream subclass
     * @returns {boolean}
     * @since 1.5
     */
    WallStream.prototype.isDashboardStream = function() {
        return view.getState().moduleId === 'dashboard';
    };

    /**
     * @returns {boolean}
     * @since 1.5
     */
    WallStream.prototype.isUserStream = function() {
        return view.getState().moduleId === 'user';
    };

    /**
     * @returns {boolean}
     * @since 1.5
     */
    WallStream.prototype.isSpaceStream = function() {
        return view.getState().moduleId === 'space';
    };

    WallStream.prototype.isUpdateAvailable = function (events) {
        var that = this;

        // We currently only support updates on dashboard and space stream
        var isDashboard = this.isDashboardStream();
        var isContainer = this.isSpaceStream() || this.isUserStream();



        if (!isDashboard && !isContainer) {
            return false;
        }

        var updatesAvailable = false;
        events.forEach(function (event) {
            if (that.entry(event.data.contentId)) {
                return;
            }

            if (event.data.streamChannel !== 'default') {
                return;
            }

            if (!event.data.insert) {
                return;
            }

            // Prevent edge-cases where live event was faster than content submission
            if (that.submitLock && event.data.originator === user.guid()) {
                return;
            }

            if (isDashboard) {
                updatesAvailable = true;
            } else if (container.guid() === event.data.sguid || container.guid() === event.data.uguid) {
                updatesAvailable = true;
            }
        });

        return updatesAvailable;
    };

    WallStream.prototype.onUpdateAvailable = function() {
        if(this.submitLock || $('#streamUpdateBadge').length) {
            return;
        }

        this.renderUpdateBadge();
    };

    WallStream.prototype.renderUpdateBadge = function() {
        var that = this;
        var appendToStreamTimeout;

        var $badge =  $(WallStream.template.updateBadge);

        $('body').append($badge);

        var appendToStream = function() {
            $('#wallStream').prepend($badge.css({'position': '', 'display': 'block'}));
            $badge.data('appended', true);
        };

        var topOffset = view.getContentTop();
        var top = topOffset + 20;
        var left = this.$.offset().left + (that.$.width() / 2) - ($badge.find('span').width() / 2);

        $badge.css({
            'position': 'fixed',
            'top': top +'px',
            'left': left+'px',
            'display':'inline-block',
            'text-align': 'center',
            //'width': '100%',
            'z-index': '9999',
            'margin-top': '15px',
            'margin-bottom': '15px'
        }).on('click', function() {
            if(appendToStreamTimeout) {
                clearTimeout(appendToStreamTimeout);
            }

            var load = function() {
                loader.set($badge, {css: {padding: '4px'}});
                that.loadUpdate().finally(function() {
                    $badge.remove();
                });
            };

            if($badge.data('appended')) {
                load();
            } else {
                $('html').animate({ scrollTop: 0 }, 'slow', function() {
                    appendToStream();
                    load();
                });
            }
        });

        if(($(window).scrollTop() + topOffset - this.$.position().top) < 0) {
           appendToStream();
        } else {
             appendToStreamTimeout = setTimeout(function() {
              //   appendToStream();
             }, 10000);
        }

    };

    WallStream.template = {
        loadSuppressedButton: '<div class="load-suppressed" style="display:none;"><a href="#" data-action-click="loadSuppressed" data-entry-key="{key}" data-action-block="manual" data-ui-loader><i class="fa fa-chevron-down"></i>&nbsp;&nbsp;{message}&nbsp;&nbsp;<span class="badge">{contentName}</span></a></div>',
        updateBadge: '<div id="streamUpdateBadge" class="animated bounceIn"><span class="label label-info" style="cursor:pointer"><i class="fa fa-arrow-circle-up"></i> New Updates Available!</span></div>'
    };

    WallStream.prototype.loadSuppressed = function(evt) {
        var key = evt.$trigger.data('entry-key');
        var entry = this.entry(key);

        this.load({
            'insertAfter': entry.$,
            'from': key,
            'suppressionsOnly': true
        }).then(function (resp) {
            evt.$trigger.closest('.load-suppressed').remove();
        }).finally(function() {
            evt.finish();
        });
    };

    WallStream.prototype.onClear = function() {
        this.$.find('.back_button_holder').hide();
    };

    WallStream.prototype.onSingleEntryStream = function () {
        this.super('onSingleEntryStream');
        this.$.find('.back_button_holder').show();
    };

    var unload = function() {
        event.off('.wallStream');
        $('#streamUpdateBadge').remove();
        $(window).off('.wallStream');
    };

    var WallStreamFilter = Filter.extend();

    WallStreamFilter.prototype.init = function() {
        this.super('init');
        this.stream = this.options.stream;

        var that = this;

        this.$.find('.wall-stream-filter-toggle').off('click').on('click', function (evt) {
            evt.preventDefault();
            evt.stopImmediatePropagation();
            that.toggleFilterPanel();
        });

        this.$.find('.wall-stream-filter-head').off('click').on('click', function (evt) {
            if (!$(evt.target).closest('a').length) {
                evt.preventDefault();
                that.toggleFilterPanel();
            }
        });

        event.on('humhub:topic:updated.wallStream', $.proxy(this.onTopicUpdated, this));

       this.initTopicPicker();
       this.initContentTypePicker();
    };

    WallStreamFilter.prototype.initTopicPicker = function() {
        var that = this;
        var topicPicker = this.getTopicPicker();
        if(topicPicker) {
            topicPicker.$.on('change', function() {
                var topics = [];
                $.each(that.getTopicPicker().map(), function(key, value) {
                    topics.push({id:key, name: value})
                });

                // Note the stream init is triggered by the humhub:topic:updated event
                topic.setTopics(topics);
            });
        }
    };

    WallStreamFilter.prototype.initContentTypePicker = function() {
        var that = this;
        var contentTypePicker = this.getContentTypePicker();
        if(contentTypePicker) {
            contentTypePicker.$.on('change', function() {
                var $filterBar = that.getFilterBar();
                $filterBar.find('.content-type-remove-label').remove();
                Widget.instance($(this)).data().forEach(function(contentType) {
                    $(string.template(WallStreamFilter.template.removeContentTypeLabel, contentType)).appendTo($filterBar);
                });
            });
        }
    };

    WallStreamFilter.prototype.onTopicUpdated = function(evt, topics) {
        // prevent double execution
        if(this.topicUpdate) {
            return;
        }

        try {
            this.topicUpdate = true;
            var topicPicker =  this.getTopicPicker();
            var $filterBar = this.getFilterBar();

            topicPicker.setSelection(topics, function(topic) {
               return {
                   id: topic.id,
                   text: topic.name,
                   image: topic.icon
               };
            });

            var selectors = [];

            topics.forEach(function(topic) {
                var selector = '[data-topic-id="'+topic.id+'"]';
                selectors.push(selector);
                if(!$filterBar.find(selector).length) {
                    topic.$label.clone().prependTo($filterBar);
                }
            });

            var toRemove = selectors.length ? '[data-topic-id]:not('+selectors.join(',')+')' : '[data-topic-id]';

            $filterBar.find(toRemove).fadeOut('fast', function() {
                $(this).remove();
            });
        } finally {
            this.topicUpdate = false;
        }
    };

    WallStreamFilter.prototype.getTopicPicker = function() {
        return Widget.instance($('#stream-topic-picker'));
    };

    WallStreamFilter.prototype.removeContentTypeFilter = function(evt) {
        this.getContentTypePicker().remove(evt.$trigger.data('typeId'));
    };

    WallStreamFilter.prototype.triggerChange = function() {
        this.super('triggerChange');
        this.updateFilterCount();
    };

    WallStreamFilter.prototype.updateFilterCount = function () {
        var count = this.getActiveFilterCount({exclude:'sort'});

        var $filterToggle = this.$.find('.wall-stream-filter-toggle');
        var $filterCount = $filterToggle.find('.filterCount');

        if(count) {
            if(!$filterCount.length) {
                $filterCount = $('<small class="filterCount"></small>').insertBefore($filterToggle.find('.caret'));
            }
            $filterCount.html(' <b>('+count+')</b> ');
        } else if($filterCount.length) {
            $filterCount.remove();
        }
    };

    WallStreamFilter.prototype.getContentTypePicker = function() {
        return Widget.instance($('#stream_filter_content_type'));
    };

    WallStreamFilter.prototype.getFilterBar = function() {
        return  this.$.find('.wall-stream-filter-bar');
    };

    WallStreamFilter.prototype.toggleFilterPanel = function() {
        this.$.find('.wall-stream-filter-body').slideToggle();
    };

    WallStreamFilter.template = {
        removeContentTypeLabel: '<a href="#" class="content-type-remove-label" data-action-click="removeContentTypeFilter" data-type-id="{id}"><span class="label label-default animated bounceIn"><i class="fa {image}"></i> {text}</span></a>'
    };

    module.export({
        WallStream: WallStream,
        WallStreamFilter: WallStreamFilter,
        unload: unload
    });
});
