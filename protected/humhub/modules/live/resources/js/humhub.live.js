humhub.module('live', function (module, require, $) {
    var object = require('util').object;
    var user = require('user');
    var liveClient;

    var init = function () {
        if (user.isGuest()) {
            return;
        }
        
        try {
            var clientType = require(module.config.client.type);
            if (clientType) {
                var tabsHandler = require(module.config.client.handler);
                liveClient = new clientType(module.config.client.options, new tabsHandler());
            } else {
                module.log.warn("Invalid live client configuration detected, live client could not be initialized.", module.config);
            }
        } catch (e) {
            module.log.warn("Invalid live client configuration detected, live client could not be initialized.", module.config);
            module.log.error(e);
        }
    };

    var setDelay = function (value) {
        if (object.isFunction(liveClient.setDelay)) {
            liveClient.setDelay(value);
        }
    };

    module.export({
        init: init,
        setDelay: setDelay
    });
});