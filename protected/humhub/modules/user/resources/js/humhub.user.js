humhub.module('user', function(module, require, $) {

    var isGuest = function() {
        return module.config.isGuest;
    };
    
    var guid = function() {
        return module.config.guid;
    };

    var getLocale = function() {
        return module.config.locale;
    };
    
    module.export({
        isGuest: isGuest,
        guid: guid,
        getLocale: getLocale
    });
});
