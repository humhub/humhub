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
        if (!this.options.url || !this.options.jwt) {
            module.log.error('Could not initialize Mercure Push Client. Some options are not configured!');
            return;
        }

        const url = new URL(this.options.url);
        url.searchParams.set('jwt', this.options.jwt);
        (this.options.topics || []).forEach(topic => {
            url.searchParams.append('topic', topic);
        });

        const source = new EventSource(url.toString());

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
