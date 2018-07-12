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
    var BaseStream = stream.Stream;
    var StreamRequest = stream.StreamRequest;
    var Component = require('action').Component;
    var Widget = require('ui.widget').Widget;
    var event = require('event');
    var Filter = require('ui.filter').Filter;
    var string = require('util').string;
    var topic = require('topic');
    var view = require('ui.view');

    var DATA_STREAM_TOPIC = 'stream-topic';

    /**
     * Stream implementation for main wall streams.
     *
     * @param {type} container
     * @param {type} cfg
     * @returns {undefined}
     */
    var WallStream = BaseStream.extend(function (container, options) {
        options = options || {};

        options.filter =  Component.instance($('#wall-stream-filter-nav'), {stream : this});

        BaseStream.call(this, container, options);

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

        event.on('humhub:content:newEntry.wallStream', function (evt, html) {
            that.prependEntry(html, true);
        });

        event.on('humhub:content:afterMove.wallStream', function (evt, response) {
            var entry = that.entry(response.id);
            if(entry) {
                if(view.getState().moduleId === 'dashboard') {
                    entry.reload();
                } else {
                    setTimeout($.proxy(entry.remove, entry), 1000);
                }
            }
        });
    };

    WallStream.template = {
        loadSuppressedButton: '<div class="load-suppressed" style="display:none;"><a href="#" data-action-click="loadSuppressed" data-entry-key="{key}" data-action-block="manual" data-ui-loader><i class="fa fa-chevron-down"></i>&nbsp;&nbsp;{message}&nbsp;&nbsp;<span class="badge">{contentName}</span></a></div>'
    };

    WallStream.prototype.loadSuppressed = function(evt) {
        var key = evt.$trigger.data('entry-key');
        var entry = this.entry(key);

        this.loadEntries({
            'insertAfter': entry.$,
            'from': key,
            'suppressionsOnly': true
        }).then(function (resp) {
            evt.$trigger.closest('.load-suppressed').remove();
        }).finally(function() {
            evt.finish();
        });
    }

    WallStream.prototype.initScroll = function() {
        var that = this;
        $(window).off('scroll.wallStream').on('scroll.wallStream', function () {
            if(that.state.scrollLock || !that.canLoadMore() || !that.state.lastRequest || that.state.firstRequest.isSingleEntryRequest()) {
                return;
            }

            var $window = $(window);
            var windowHeight = $window.height();
            var windowBottom = $window.scrollTop() + windowHeight;
            var elementBottom = that.$.offset().top + that.$.outerHeight();
            var remaining = elementBottom - windowBottom;
            if (remaining <= 300) {
                that.state.scrollLock = true;
                $('#btn-load-more').hide();
                setTimeout(function () {
                    that.loadEntries().finally(function() {
                        that.state.scrollLock = false;
                    });
                });
            }
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
        event.off('humhub:content:newEntry.wallStream');
        event.off('humhub:content:afterMove.wallStream');
        event.off('humhub:topic:updated.wallStream');
        $(window).off('scroll.wallStream');
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
