humhub.module('live', function(module, require, $) {    
    var liveClient;

    var init = function() {
        try {
            var clientType = require(module.config.client.type);
            if(clientType) {
                liveClient = new clientType(module.config.client.options);
            } else {
                module.log.warn("Invalid live client configuration detected, live client could not be initialized.", module.config);
            }
        } catch(e) {
            module.log.warn("Invalid live client configuration detected, live client could not be initialized.", module.config);
            module.log.error(e);
        }
    };

    module.export({
        init: init
    });
});