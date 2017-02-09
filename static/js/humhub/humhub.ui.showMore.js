humhub.module('ui.showMore', function (module, require, $) {
    var additions = require('ui.additions');

    var CollapseContent = function (node, options) {
        this.options = options || {};
        this.$ = node instanceof $ ? node : $(node);
        this.collapseAt = this.$.data('collapse-at') || 310;
        this.options.readMoreText = this.$.data('read-more-text') || module.text('readMore');
        this.options.readLessText = this.$.data('read-less-text') || module.text('readLess');
        this.init();
    };

    CollapseContent.prototype.init = function () {
        var that = this;
        var height = this.$.outerHeight();
        this.$collapseButton = this.$.siblings('.showMore');

        // If height expands the max height we init the collapse post logic
        if (height > this.collapseAt) {
            if (!this.$collapseButton.length) {
                this.$collapseButton = $(module.templates.showMore);
                that.$.after(this.$collapseButton);
            }

            // Init collapse button
            this.$collapseButton.on('click', function (evt) {
                evt.preventDefault();
                if (that.$.data('state') === 'collapsed') {
                    that.expand();
                } else {
                    that.collapse();
                }
            }).show();

            // Set init state
            if (this.$.data('state') !== 'expanded') {
                that.collapse();
            } else {
                that.expand();
            }
        }
    };

    CollapseContent.prototype.collapse = function () {
        this.$.css({'display': 'block', 'max-height': this.collapseAt + 'px'});
        this.$collapseButton.html('<i class="fa fa-arrow-down"></i> ' + this.options.readMoreText);
        this.$.data('state', 'collapsed');
    };

    CollapseContent.prototype.expand = function () {
        this.$.css('max-height', '');
        this.$collapseButton.html('<i class="fa fa-arrow-up"></i> ' + this.options.readLessText);
        this.$.data('state', 'expanded');
    };

    var init = function () {
        additions.register('showMore', '[data-ui-show-more]', function ($match) {
            $match.each(function () {
                new CollapseContent(this);
            });
        });
    };

    module.templates = {
        showMore: '<a href="#" style="display:block;margin: 5px 0;"></a>'
    };

    module.export({
        init: init,
        CollapseContent
    });
});