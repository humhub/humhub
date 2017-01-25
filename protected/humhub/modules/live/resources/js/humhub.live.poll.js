humhub.module('live.poll', function (module, require, $) {
    var client = require('client');
    var event = require('event');

    var DEFAULT_MIN_INTERVAL = 15;
    var DEFAULT_MAX_INTERVAL = 45;

    var DEFAULT_IDLE_FACTOR = 0.1;

    var DEFAULT_IDLE_INTERVAL = 20;

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
        
        
        var that = this;
        $(document).on('mousemove keydown mousedown touchstart', function () {
            that.delay = that.options.minInterval;

            // Make sure we do not have to wait too long after idle end.
            if (that.delay > 25) {
                clearTimeout(that.timeout);
                that.timeout = setTimeout(that.call, that.getDelay());
            }
        });

        this.timeout = setTimeout(this.call, this.getDelay());
        setInterval($.proxy(this.updateIdle, this), (this.options.idleInterval * 1000));
    };

    /**
     * Updates the delay by means of the idleFactor.
     */
    PollClient.prototype.updateIdle = function () {
        if (this.delay < this.options.maxInterval) {
            this.delay = Math.ceil(this.delay + (this.delay * this.options.idleFactor));
        }

        if (this.delay > this.options.maxInterval) {
            this.delay = this.options.maxInterval;
        }
        
        console.log('Updated delay to: '+this.delay);
    };

    /**
     * Runs an live update call and resets the timeout.
     */
    PollClient.prototype.update = function () {
        console.log('DELAY: '+ this.delay);
        this.timeout = setTimeout(this.call, this.getDelay());
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

    /**
     * Groupes the liveEvents by type.
     */
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

    /**
     * Returns the delay in milliseconds.
     * @returns {Number}
     */
    PollClient.prototype.getDelay = function () {
        return this.delay * 1000;
    };
    
    PollClient.prototype.setDelay = function (value) {
        this.delay = value;
    };

    module.export({
        PollClient: PollClient
    });
});