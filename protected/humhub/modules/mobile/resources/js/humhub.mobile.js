humhub.module('mobile', function (module, require, $) {

    var init = function() {

    };

    var close = function(evt) {
        module.closed = true;
    };

    module.export({
        init: init,
        close: close,
        closed: false
    });
});