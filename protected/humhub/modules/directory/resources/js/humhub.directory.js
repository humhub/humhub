humhub.module('directory', function (module, require, $) {
    var status = require('ui.status');

    var init = function () {
        var viewState = status.getState();

        if ($('.knob-container').length) {
            $(".knob").knob();
            $(".knob-container").css("opacity", 1);
        }
    };

    module.export({
        init: init,
        initOnPjaxLoad: true
    });
});