humhub.module('directory', function(module, require, $) {
    var status = require('ui.status');

    var init = function() {
        var viewState = status.getState();
        if(viewState.moduleId === 'directory') {
            $(".knob").knob();
            $(".knob-container").css("opacity", 1);
        }
    };
    
    module.export({
        init: init
    });
});