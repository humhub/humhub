/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
humhub.module('notification', function (module, require, $) {
    var util = require('util');
    var object = util.object;
    var string = util.string;
    var Widget = require('ui.widget').Widget;
    var event = require('event');
    var client = require('client');
    var view = require('ui.view');
    var user = require('user');

    module.initOnPjaxLoad = true;

    var NotificationDropDown = function (node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(NotificationDropDown, Widget);

    NotificationDropDown.prototype.init = function (update) {
        this.isOpen = false;
        this.lastEntryLoaded = false;
        this.lastEntryId = 0;
        this.originalTitle = document.title;
        this.initDropdown();
        this.handleResult(update);
        //this.sendDesktopNotifications(update);

        var that = this;
        event.on('humhub:modules:notification:live:NewNotification', function (evt, events, update) {
            var count = (that.$.data('notification-count')) ? parseInt(that.$.data('notification-count')) + events.length : events.length;
            that.updateCount(count);
            that.sendDesktopNotifications(events, update.lastSessionTime);
        });
    };

    NotificationDropDown.prototype.initDropdown = function () {
        this.$entryList = this.$.find('ul.media-list');
        this.$dropdown = this.$.find('#dropdown-notifications');

        var that = this;
        this.$entryList.scroll(function () {
            var containerHeight = that.$entryList.height();
            var scrollHeight = that.$entryList.prop("scrollHeight");
            var currentScrollPosition = that.$entryList.scrollTop();

            // load more activites if current scroll position is near scroll height
            if (currentScrollPosition >= (scrollHeight - containerHeight - 1)) {
                if (!that.lastEntryLoaded) {
                    that.loadEntries();
                }
            }
        });
    };

    NotificationDropDown.prototype.toggle = function (evt) {
        // Always reset the loading settings so we reload the whole dropdown.
        this.lastEntryLoaded = false;
        this.lastEntryId = 0;

        // Since the handler will be called before the bootstrap trigger it's an open event if the dropdown is not visible yet
        this.isOpen = !this.$dropdown.is(':visible');
        if (this.isOpen) {
            this.$entryList.empty().hide();
            this.loadEntries();
        }
    };

    NotificationDropDown.prototype.loadEntries = function () {
        if (this.loading) {
            return;
        }

        var that = this;
        this.loader();
        client.get(module.config.loadEntriesUrl, {data: {from: this.lastEntryId}})
                .then($.proxy(this.handleResult, this))
                .catch(_errorHandler)
                .finally(function () {
                    that.loader(false);
                    that.loading = false;
                });
    };

    NotificationDropDown.prototype.handleResult = function (response) {
        if (!response.counter) {
            this.$entryList.append(string.template(module.templates.placeholder, {'text': module.text('placeholder')}));
        } else {
            this.lastEntryId = response.lastEntryId;
            this.$entryList.append(response.output);
            $('span.time').timeago();
        }

        this.updateCount(parseInt(response.newNotifications));
        this.lastEntryLoaded = (response.counter < 6);
        this.$entryList.fadeIn('fast');
    };

    NotificationDropDown.prototype.updateCount = function ($count) {
        if (this.$.data('notification-count') === $count) {
            if(!$count) {
                $('#badge-notifications').hide();
            }
            return;
        }

        $('#badge-notifications').hide();

        if (!$count) {
            updateTitle(false);
            $('#badge-notifications').html('0');
            $('#mark-seen-link').hide();
            $('#icon-notifications .fa').removeClass("animated swing");
        } else {
            updateTitle($count);
            $('#badge-notifications').html($count);
            $('#mark-seen-link').show();
            $('#badge-notifications').fadeIn('fast');

            // Clone icon to retrigger animation
            var $icon = $('#icon-notifications .fa');
            var $clone = $icon.clone();
            $clone.addClass("animated swing");
            $icon.replaceWith($clone);
        }

        this.$.data('notification-count', $count);
    };

    NotificationDropDown.prototype.sendDesktopNotifications = function (response, lastSessionTime) {
        if (!response) {
            return;
        }

        if (!module.config.sendDesktopNotifications) {
            return;
        }

        if (response.text) { // Single Notification
            module.sendDesktopNotifiaction(response.text);
        } else if (response.notifications) { // Multiple Notifications
            var $notifications = response.notifications;
            for (var i = 0; i < $notifications.length; i++) {
                module.sendDesktopNotifiaction($notifications[i]);
            }
        } else if (object.isArray(response)) { // Live events
            $.each(response, function (i, liveEvent) {
                if (lastSessionTime && lastSessionTime > liveEvent.data.ts) {
                    return; // continue
                }

                if (liveEvent.data && liveEvent.data.text) {
                    module.sendDesktopNotifiaction(liveEvent.data.text);
                }
            });
        }

    };

    var sendDesktopNotifiaction = function (body, icon) {
        icon = icon || module.config.icon;
        if (body && body.length) {
            notify.createNotification("Notification", {body: body, icon: icon});
        }
    };

    var _errorHandler = function (e) {
        module.log.error(e, true);
    };

    NotificationDropDown.prototype.loader = function (show) {
        if (show !== false) {
            this.$.find('#loader_notifications').show();
        } else {
            this.$.find('#loader_notifications').hide();
        }

    };

    NotificationDropDown.prototype.markAsSeen = function (evt) {
        var that = this;
        return client.post(evt).then(function (response) {
            $('#badge-notifications').hide();
            $('#mark-seen-link').hide();
            that.updateCount(0);
        }).catch(function (e) {
            module.log.error(e, true);
        });
    };

    /**
     * Global action handler (used in overview page).
     * 
     * @param {type} evt
     * @returns {undefined}
     */
    var markAsSeen = function (evt) {
        var widget = NotificationDropDown.instance('#notification_widget');
        widget.markAsSeen(evt).then(function () {
            location.reload();
        });
    };

    var updateTitle = function ($count) {
        if ($count) {
            document.title = '(' + $count + ') ' + view.getState().title;
        } else if ($count === false) {
            document.title = view.getState().title;
        }
    };

    module.templates = {
        placeholder: '<li class="placeholder">{text}</li>'
    };

    var init = function ($pjax) {
        if (user.isGuest()) {
            return;
        }
        
        updateTitle($('#notification_widget').data('notification-count'));
        initOverviewPage();
        if (!$pjax) {
            $("#dropdown-notifications ul.media-list").niceScroll({
                cursorwidth: "7",
                cursorborder: "",
                cursorcolor: "#555",
                cursoropacitymax: "0.2",
                nativeparentscrolling: false,
                railpadding: {top: 0, right: 3, left: 0, bottom: 0}
            });

           $("#dropdown-notifications ul.media-list").on('touchmove', function(evt) {
                evt.preventDefault();
           });
        }

        module.menu = NotificationDropDown.instance('#notification_widget');
    };

    var initOverviewPage = function () {
        if ($('#notification_overview_list').length) {
            if (!$('#notification_overview_list li.new').length) {
                $('#notification_overview_markseen').hide();
            }
        }
    };

    module.export({
        init: init,
        markAsSeen: markAsSeen,
        sendDesktopNotifiaction: sendDesktopNotifiaction,
        NotificationDropDown: NotificationDropDown
    });
});

