humhub.module('live.poll', function (module, require, $) {
    var client = require('client');
    var event = require('event');
    var object = require('util').object;

    var DEFAULT_MIN_INTERVAL = 10;
    var DEFAULT_MAX_INTERVAL = 45;

    var DEFAULT_IDLE_FACTOR = 0.1;
    var DEFAULT_IDLE_INTERVAL = 20;

    var EVENT_TYPE_REQUEST = 'request';
    var EVENT_TYPE_FOCUS = 'focus';
    var EVENT_TYPE_UPDATE = 'update';

    var counter = {
        requests: 0,
        updates: 0
    };

    var PollClient = function (options) {
        if (!options) {
            module.log.error('Could not initialize PollClient. No options given!');
            return;
        }

        this.options = options;
        this.options.minInterval = options.minInterval || DEFAULT_MIN_INTERVAL;
        this.options.maxInterval = options.maxInterval || DEFAULT_MAX_INTERVAL;
        this.options.idleFactor = options.idleFactor || DEFAULT_IDLE_FACTOR;
        this.options.idleInterval = options.idleDelay || DEFAULT_IDLE_INTERVAL;
        this.options.initTime = options.initTime || Date.now();

        this.init();
    };

    PollClient.prototype.init = function () {
        if (!this.options.url) {
            module.log.error('Could not initialize PollClient. No url option given!');
            return;
        }

        this.subscriberId = this.generateSubscriberId();
        this.focus = true;
        this.delay = this.options.minInterval;
        this.call = this.update.bind(this);
        this.handle = this.handleUpdate.bind(this);
        this.handleError = this.handleUpdateError.bind(this);
        this.lastTs = this.options.initTime;

        $(window)
            .on('blur', this.onWindowBlur.bind(this))
            .on('focus',this.onWindowFocus.bind(this));

        var that = this;
        $(document).on('mousemove keydown mousedown touchstart', object.debounce(function() {
            that.stopIdle();
        }, 200));

        this.resetPollTimeout();
        this.startIdleTimer();

        this.initBroadCast();
    };

    PollClient.prototype.generateSubscriberId = function () {
        return  '_' + Math.random().toString(36).substr(2, 9);
    };

    PollClient.prototype.initBroadCast = function () {
        if(!window.BroadcastChannel) {
            return;
        }

        this.channel = new BroadcastChannel('live.poll');

        var that = this;
        this.channel.onmessage = function(evt) {
            if(!evt.data) {
                return;
            }

            if(evt.data.subscriberId === that.subscriberId) {
                // We triggered the event, so nothing todo
                return;
            }

            if(!that.focus) {
                // Seems this is an inactive tab, so let others do the job...
                that.resetPollTimeout(that.options.maxInterval);
            }

            switch (evt.data.type) {
                case EVENT_TYPE_REQUEST:
                    // Another tab just started a request, so delay the timeout
                    that.resetPollTimeout();
                    break;
                case EVENT_TYPE_FOCUS:
                    // Another tab was focused, so increase delay and reset timeout
                    that.resetPollTimeout(that.options.maxInterval);
                    break;
                case EVENT_TYPE_UPDATE:
                    // We received a response from another tab
                    that.handleUpdate(evt.data);
                    break;
            }
        }
    };

    /**
     * Handler called once the window was focused
     */
    PollClient.prototype.onWindowFocus = function () {
        this.focus = true;
        this.stopIdle();
        this.broadCast(EVENT_TYPE_FOCUS);
    };

    /**
     * Handler called once the window was blurred
     */
    PollClient.prototype.onWindowBlur = function () {
        this.focus = false;
        this.updateIdle();
    };

    /**
     * Resets current timeout if set and starts polling with current delay
     */
    PollClient.prototype.resetPollTimeout = function (delay) {
        clearTimeout(this.timeout);

        if(delay) {
            this.setDelay(delay);
        }

        this.timeout = setTimeout(this.call, this.getDelay());
    };

    /**
     * Resets current timeout if set and starts polling with current delay
     */
    PollClient.prototype.startIdleTimer = function () {
        setInterval($.proxy(this.updateIdle, this), (this.options.idleInterval * 1000));
    };

    /**
     * Stops the idle behavior by resetting the delay to the min delay
     */
    PollClient.prototype.stopIdle = function () {
        this.setDelay(this.options.minInterval);

        // Make sure we do not have to wait too long after idle end.
        if (new Date() - this.lastTs > this.options.minInterval) {
            this.resetPollTimeout();
        }
    };

    /**
     * Updates the delay by means of the idleFactor.
     */
    PollClient.prototype.updateIdle = function () {
        if (this.delay < this.options.maxInterval) {
            this.setDelay(Math.ceil(this.delay + (this.delay * this.options.idleFactor)));
        }

        if (this.delay > this.options.maxInterval) {
            this.setDelay(this.options.maxInterval);
        }
    };

    /**
     * Runs an live update call and resets the timeout.
     */
    PollClient.prototype.update = function () {
        this.broadCast(EVENT_TYPE_REQUEST);
        counter.requests++;

        client.get(this.getCallOptions())
            .then(this.handle)
            .catch(this.handleError);

    };

    /**
     * Returns the ajax call options
     */
    PollClient.prototype.getCallOptions = function () {
        return {
            url: this.options.url,
            data: {
                last: this.lastTs
            }
        };
    };

    /**
     * Handles the live update response.
     */
    PollClient.prototype.handleUpdate = function (response) {

        if(this.lastTs >= response.queryTime) {
            // We already have a more recent update
            return;
        }

        if(this.subscriberId === response.subscriberId) {
            // Just to make sure we do not handle our own broadcast event
            return;
        }

        // used for debugging only
        counter.updates++;

        this.lastTs = response.queryTime;

        this.resetPollTimeout();

        if(!response.subscriberId) {
            // If subscriberId is present, this data was already sent
            this.broadCast(EVENT_TYPE_UPDATE, {
                queryTime: response.queryTime,
                events: response.events
            });
        }

        this.triggerEventUpdates(response);
    };

    PollClient.prototype.triggerEventUpdates = function(response) {
        if(object.isObject(response.events)) {
            var events = this.groupEvents(response.events);

            $.each(events, function (type, events) {
                try {
                    // humhub.module.bla -> humhub:module:bla
                    event.trigger(type.replace(/\./g, ':'), [events, response]);
                } catch (e) {
                    module.log.error(e);
                }
            });

            this.lastIds = Object.keys(response.events);
        }
    };

    PollClient.prototype.broadCast = function (type, data) {
        data = data || {};

        if(!this.channel || data.subscriberId) {
            return;
        }

        data.subscriberId = this.subscriberId;
        data.type = type;

        this.channel.postMessage(data);
    };

    /**
     * Groups the liveEvents by type and filters out duplicates.
     */
    PollClient.prototype.groupEvents = function (events) {
        var result = {};
        var that = this;
        $.each(events, function (id, liveEvent) {
            // Filter out already triggered events.
            if(that.lastIds && that.lastIds.indexOf(id) > -1) {
                return; // continue
            }

            if (!result[liveEvent.type]) {
                result[liveEvent.type] = [liveEvent];
            } else {
                result[liveEvent.type].push(liveEvent);
            }
        });

        return result;
    };

    PollClient.prototype.handleUpdateError = function (e) {
        if(!navigator.onLine) {
            this.resetPollTimeout(this.options.maxInterval);
            module.log.info('Poll request blocked due to offline status');
        } else {
            module.log.error(e);
        }
    };

    /**
     * Returns the delay in milliseconds.
     * @returns {Number}
     */
    PollClient.prototype.getDelay = function () {
        return this.delay * 1000;
    };

    /**
     * Sets the delay in seconds
     * @returns {Number}
     */
    PollClient.prototype.setDelay = function (seconds) {
        this.delay = seconds;
    };

    module.export({
        PollClient: PollClient
    });
});
