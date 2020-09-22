humhub.module('ui.progress', function(module, require, $) {

    var util = require('util');
    var string = util.string;
    var object = util.object;
    var Widget = require('ui.widget').Widget;

    module.template = '<div class="progress-bar" role="progressbar"></div>';

    var Progress = function(node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Progress, Widget);

    Progress.component = 'humhub-ui-progress';

    Progress.prototype.getDefaultOptions = function() {
        return {
            valuenow: 0,
            valuemin: 0,
            valuemax: 100,
            progressContext: 'info'
        };
    };
    
    Progress.prototype.init = function() {
        this.$.addClass("progress");
        
        this.$progressBar = this.$.find('.progress-bar');
        
        if (!this.$progressBar.length) {
            this.$progressBar = $(module.template).addClass('progress-bar-' + this.options.progressContext);
            this.$.html(this.$progressBar);
        }
        
        this.$progressBar.attr({
            'aria-valuenow': this.options.valuenow,
            'aria-valuemin': this.options.valuemin,
            'aria-valuemax': this.options.valuemax
        });
        
        this.update(this.options.valuenow);
    };

    Progress.prototype.value = function() {
        var width = this.$progressBar[0].style.width;
        return (width) ? parseInt(string.cutSuffix(width, '%')) : 0;
    };

    Progress.prototype.reset = function() {
        this.update(0);
    };

    Progress.prototype.update = function(now, total) {
        var value = (arguments.length > 1) ? parseInt(now / total * 100, 10) : now;
        this.$progressBar.css('width', value + '%');
    };

    module.export({
        Progress: Progress,
    });
});