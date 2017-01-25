humhub.module('live.poll', function (module, require, $) {
    var client = require('client');
    var event = require('event');

    var DEFAULT_MIN_INTERVAL = 10;
    var DEFAULT_MAX_INTERVAL = 45;
    var DEFAULT_INIT_DELAY = 5;

    var PollClient = function (options) {
        if (!options) {
            module.log.error('Could not initialize PollClient. No options given!');
            return;
        }
        this.options = options;
        this.options.minInterval = options.minInterval || DEFAULT_MIN_INTERVAL;
        this.options.maxInterval = options.maxInterval || DEFAULT_MAX_INTERVAL;
        this.options.initDelay = options.initDelay || DEFAULT_INIT_DELAY;
        this.init();
    };

    PollClient.prototype.init = function () {
        if (!this.options.url) {
            module.log.error('Could not initialize PollClient. No url option given!');
            return;
        }

        this.delay = this.options.minInterval;
        this.lastCall = Date.now();
        this.call = $.proxy(this.update, this);
        this.handle = $.proxy(this.handleUpdate, this);
        setTimeout(this.call, DEFAULT_INIT_DELAY);
    };

    PollClient.prototype.update = function () {
        setTimeout(this.call, this.getDelay());
        client.get(this.getCallOptions())
                .then(this.handle)
                .catch(_handleUpdateError);
    };

    PollClient.prototype.handleUpdate = function (response) {
        this.lastTs = response.queryTime;
        this.lastCall = Date.now();
        var events = _groupEvents(response.events);
        $.each(events, function (type, events) {
            try {
                event.trigger(type, [events]);
            } catch (e) {
                module.log.error(e);
            }
        });

    };

    var _groupEvents = function (events) {
        var result = {};
        $.each(events, function (id, liveEvent) {
            if (!result[liveEvent.type]) {
                result[liveEvent.type] = [liveEvent];
            } else {
                result[liveEvent.type].push(liveEvent);
            }
        });

        return result;
    };

    var _handleUpdateError = function (e) {
        module.log.error(e);
    };

    PollClient.prototype.getCallOptions = function () {
        return {
            url: this.options.url,
            data: {
                last: this.lastTs
            }
        };
    };

    PollClient.prototype.getDelay = function () {
        return this.delay * 1000;
    };

    module.export({
        PollClient: PollClient
    });
});