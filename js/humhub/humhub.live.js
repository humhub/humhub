humhub.module('live', function(module, require, $) {
    var object = require('util').object;
    var space = require('space', true);
    var user = require('user', true);

    var instances = [];

    var _delay = 11;

    var _idleFactor = 0;

    var _defaultOptions = {
        id: 'unknown',
        min: 20,
        max: 45,
        idle: true,
        idleFactor: 1,
        active: true
    };

    var LiveUpdate = function(topic, options) {
        this.topic = (arguments.length === 1) ? topic.topic : topic;
        this.options = (arguments.length === 1) ? topic.options : options;
        this.options = $.extend(_defaultOptions, this.options);

        this.id = this.options.id;
    };

    LiveUpdate.prototype.on = function() {
        this.options.active = true;
    };

    LiveUpdate.prototype.off = function() {
        this.options.active = false;
    };

    LiveUpdate.prototype.isExpired = function() {
        if(!this.options.active) {
            return false;
        } else if(!this.lastUpdate) {
            return true;
        }

        var minDelay = this.options.min + (this.options.min * (_idleFactor * this.options.idleFactor));

        if(minDelay > this.options.max) {
            minDelay = this.options.max;
        }

        return (this.lastUpdate + minDelay) >= Date.now();
    };
    
    LiveUpdate.prototype.handleResult = function(result) {
        
    }

    LiveUpdate.prototype.validate = function() {
        return !((this.topic.user && user.isGuest()) || (this.topic.space && !space.guid()));
    };

    var register = function(topic, options) {
        var instance = new LiveUpdate(topic, options);
        instances.push(instance);
        return instance;
    };

    var init = function() {
        setTimeout(_run, _delay);
    };

    var _run = function() {
        var topics = [];
        var lastUpdate = Date.now();
        instances.forEach(function(update) {
            if(update.isExpired() && update.validate()) {
                var topic = _getServerTopic(update);
                module.log.debug('Topic update:' + update.id, topic);
                topics.push(topic);
                update.lastUpdate = lastUpdate;
            }
        });

        _send().then(function(result) {
            instances.forEach(function(liveUpdate) {
                liveUpdate.handleResult(result);
            });
        }).catch(function(err) {
            // Silent error log
            module.log.error(err);
        });
    }

    var _getServerTopic = function(update) {
        var result = {};

        if(update.topic.user === true) {
            result.uguid = user.guid();
        } else if(update.topic.uguid) {
            result.uguid = update.topic.uguid;
        }

        if(update.topic.space) {
            result.sguid = space.guid();
        } else if(update.topic.sguid) {
            result.sguid = update.topic.sguid;
        }

        if(update.topic.suffix) {
            result.suffix = update.topic.suffix;
        }

        if(update.topic.module) {
            result.module = update.topic.module;
        }

        if(update.lastResult) {
            result.last = update.lastResult;
        }

        return result;
    };

    var update = new LiveUpdate({
        topic: {
            'user': true,
            'space': true,
            'module': 'mail',
            'suffix': 'whatever'
        },
        options: {
            min: 10,
            max: 30,
            idle: true,
            idleFactor: 1
        }
    });

    /**
     LiveUpdate
     Settings
     LastUpdate -> Date this specific update was requested the last time.
     (Register To) --> function or static object
     - User
     - Space
     - Module
     (Live Settings)
     - Min Duration
     - Idle Factor
     (Callback)
     - Callback
     
     -On/Off
     */
    module.export({
        init: init,
        register: register
    });
});