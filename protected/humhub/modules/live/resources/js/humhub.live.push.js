humhub.module('live.push', function (module, require, $) {
    var event = require('event');

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
            var message = JSON.parse(data);
            event.trigger(message.type.replace(/\./g, ':'), [[message]]);
        });

        window.addEventListener('securitypolicyviolation', function (event) {
            if (event.violatedDirective.includes('script-src')) {
                // The directive "script-src" can be violated when nonce value has been recreated
                // try to reload current page in order to solve the issue
                module.log.info('Force page reload. The directive "script-src" is violated because of nonce was changed.');
                window.location.reload();
            }
        });
    };

    module.export({
        PushClient: PushClient
    });
});
