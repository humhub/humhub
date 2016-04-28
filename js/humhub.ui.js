humhub.initModule('ui', function(module, require, $) {
    var additions = require('additions');
    module.init = function() {
        additions.registerAddition('.autosize', function($match) {
            $match.autosize();
        });
    };
});