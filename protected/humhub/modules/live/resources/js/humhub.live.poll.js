humhub.module('live.poll', function (module, require, $) {
    var client = require('client');
    var event = require('event');
    var object = require('util').object;

    var DEFAULT_MIN_INTERVAL = 15;
    var DEFAULT_MAX_INTERVAL = 45;

    var DEFAULT_IDLE_FACTOR = 0.1;

    var DEFAULT_IDLE_INTERVAL = 20;

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

        this.delay = this.options.minInterval;
        this.call = this.update.bind(this);
        this.handle = this.handleUpdate.bind(this);
        this.lastTs = this.options.initTime;

        $(window)
            .on('blur', this.updateIdle.bind(this))
            .on('focus',this.stopIdle.bind(this));

        $(document).on('mousemove keydown mousedown touchstart', this.stopIdle.bind(this));

        this.resetPollTimeout();
        this.startIdleTimer();

        this.initBroadCast();
    };

    PollClient.prototype.initBroadCast = function () {
        if(!window.BroadcastChannel) {
            return;
        }

        this.channel = new BroadcastChannel('live.poll');

        this.channel.onmessage = (evt) => {
            if(!evt.data) {
                return;
            }

            if(this.idle) {
                // Seems this is an inactive tab, so let others do the job...
                this.setDelay(this.options.maxInterval);
            }

            if(evt.data.type === 'request') {
                // Another tab just started a request, so delay the timeout
                this.resetPollTimeout();
            } else if(this.lastTs < evt.data.queryTime) {
                // We received a response from another tab
                console.log('update from channel '+evt.data.queryTime);
                this.handleUpdate(evt.data);
            }
        }
    };

    /**
     * Resets current timeout if set and starts polling with current delay
     */
    PollClient.prototype.resetPollTimeout = function () {
        clearTimeout(this.timeout);
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
        this.idle = false;

        var currentDelay = this.delay;
        this.setDelay(this.options.minInterval);

        // Make sure we do not have to wait too long after idle end.
        if (currentDelay > 25) {
            this.resetPollTimeout();
        }
    };

    /**
     * Updates the delay by means of the idleFactor.
     */
    PollClient.prototype.updateIdle = function () {
        console.log(counter);
        this.idle = true;

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
        this.broadCast({type: 'request'});
        counter.requests++;
        client.get(this.getCallOptions())
                .then(this.handle)
                .catch(_handleUpdateError);
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
        // Do we already have a more recent update?
        if(this.lastTs >= response.queryTime) {
            return;
        }

        counter.updates++;
        this.lastTs = response.queryTime;

        this.resetPollTimeout();
        this.broadCast(response);

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

    PollClient.prototype.broadCast = function (response) {
        if(this.channel) {
            this.channel.postMessage({
                queryTime: response.queryTime,
                events: response.events
            });
        }
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

    var _handleUpdateError = function (e) {
        module.log.error(e);
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
