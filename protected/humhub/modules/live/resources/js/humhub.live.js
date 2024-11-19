humhub.module('live', function (module, require, $) {
    var object = require('util').object;
    var liveClient;

    var init = function () {
        const that = this;

        if (!module.config.isActive) {
            return;
        }

        try {
            var clientType = require(module.config.client.type);
            if (clientType) {
                liveClient = new clientType(module.config.client.options);
            } else {
                module.log.warn("Invalid live client configuration detected, live client could not be initialized.", module.config);
            }
        } catch (e) {
            module.log.warn("Invalid live client configuration detected, live client could not be initialized.", module.config);
            module.log.error(e);
        }

        window.addEventListener('securitypolicyviolation', function (event) {
            // The directive "script-src" can be violated when nonce value has been recreated
            if (event.violatedDirective.includes('script-src') && typeof that.violationReloadTimeout === 'undefined') {
                module.log.info('Force page reload. The directive "script-src" is violated because nonce is obsolete.');
                // Reload the page to solve the issue of Content Security Policy,
                // but wait 1 second to avoid conflict with auto-redirect to login page
                that.violationReloadTimeout = setTimeout(function () {
                    window.location.reload();
                    that.violationReloadTimeout = undefined;
                }, 1000);
            }
        });
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
