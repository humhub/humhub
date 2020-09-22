humhub.module('directory', function (module, require, $) {

    var init = function () {

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