humhub.module('live.push', function (module, require, $) {
    var client = require('client');
    var event = require('event');
    var object = require('util').object;

    var PushClient = function (options) {
        if (!options) {
            module.log.error('Could not initialize PushClient. No options given!');
            return;
        }
        this.options = options;
        this.init();
    };

    PushClient.prototype.init = function () {
        if (!this.options.url) {
            module.log.error('Could not initialize PushClient. No url option given!');
            return;
        }

        var that = this;
        var socket = io.connect(this.options.url);
        socket.on('connect', function () {
            socket.emit('authenticate', {token: that.options.jwt});
        });
        socket.on('error', function (err) {
            module.log.error(err);
        });
        socket.on('message', function (data) {
            message = JSON.parse(data);
            event.trigger(message.type.replace(/\./g, ':'), [[message]]);
        });
    };

    var _handleUpdateError = function (e) {
        module.log.error(e);
    };

    module.export({
        PushClient: PushClient
    });
});