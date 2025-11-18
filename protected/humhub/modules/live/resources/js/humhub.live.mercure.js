humhub.module('live.mercure', function (module, require, $) {
    const event = require('event');

    const MercureClient = function (options) {
        if (!options) {
            module.log.error('Could not initialize Mercure Push Client. No options given!');
            return;
        }
        this.options = options;
        this.init();
    };

    MercureClient.prototype.init = function () {
        if (!this.options.url || !this.options.jwt || !this.options.topic) {
            module.log.error('Could not initialize Mercure Push Client. Some options are not configured!');
            return;
        }

        const source = new EventSource(this.options.url
            + '?jwt=' + encodeURIComponent(this.options.jwt)
            + '&topic=' + encodeURIComponent(this.options.topic));

        source.onerror = function (err) {
            module.log.error('Mercure error', err);
        };

        source.onmessage = function (messageEvent) {
            try {
                const message = JSON.parse(messageEvent.data);
                const eventType = message.type ? message.type.replace(/\./g, ':') : 'mercure:update';
                event.trigger(eventType, [[message]]);
            } catch (e) {
                module.log.error('Mercure: Failed to parse message', e);
            }
        };
    };

    module.export({
        MercureClient
    });
});
