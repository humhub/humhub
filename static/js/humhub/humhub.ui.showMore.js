humhub.module('ui.showMore', function (module, require, $) {
    var additions = require('ui.additions');

    var DEFAULT_COLLAPSE_AT = 380;

    var CollapseContent = function (node, options) {
        this.options = options || {};
        this.$ = node instanceof $ ? node : $(node);
        this.collapseAt = this.$.data('collapse-at') || DEFAULT_COLLAPSE_AT;
        this.options.readMoreText = this.$.data('read-more-text') || module.text('readMore');
        this.options.readLessText = this.$.data('read-less-text') || module.text('readLess');
        this.init();
    };

    CollapseContent.prototype.init = function () {
        var that = this;
        this.$.imagesLoaded(function() {
            that.check();
        });
    };

    CollapseContent.prototype.check = function () {
        var that = this;
        var height = this.$.outerHeight();
        this.$collapseButton = this.$.siblings('.showMore');

        // If the first or second node is we add some more space to our collapseAt
        var $firstChild = this.$.children(':first');
        var $secondChild = $firstChild.next();
        if($firstChild.is('.oembed_snippet') || $secondChild.is('.oembed_snippet')) {
            var collapseCandidate = this.$.find('.oembed_snippet:first').outerHeight() + 40;
            this.collapseAt = (collapseCandidate > this.collapseAt) ? collapseCandidate : this.collapseAt;
        }

        var diff = height - this.collapseAt;

        // If height expands the max height we init the collapse post logic
        if (height > this.collapseAt && diff > 70) {
            if (!this.$collapseButton.length) {
                this.$.after($(module.templates.showMore));
                this.$collapseButton = this.$.siblings('.showMore');
                this.$gradient = this.$.siblings('.showMoreGradient');
                this.$gradient.children().css({background: 'linear-gradient(rgba(251,251,251,0), '+determineBackground(this.$)+')'});
            }

            // Init collapse button
            this.$collapseButton.add(this.$gradient).on('click', function (evt) {
                evt.preventDefault();
                if (that.$.data('state') === 'collapsed') {
                    that.expand();
                } else {
                    that.collapse();
                }
            }).show();

            // Set init state
            if (this.$.data('state') !== 'expanded') {
                this.collapse();
            } else {
                this.expand();
            }
        }
    };

    var determineBackground = function($node) {
        var bc;
        var defColor = '#ffff';

        if(!$node || !$node.length) {
            return defColor;
        }

        while (isTransparent(bc = $node.css("background-color"))) {
            if ($node.is("body")) {
                return defColor;
            }
            $node = $node.parent();
        }

        return bc;
    };

    var isTransparent = function(color) {
        switch ((color || "").replace(/\s+/g, '').toLowerCase()) {
            case "transparent":
            case "":
            case "rgba(0,0,0,0)":
                return true;
            default:
                return false;
        }
    };

    CollapseContent.prototype.collapse = function () {
        this.$.css({'display': 'block', 'max-height': this.collapseAt + 'px'});
        this.$collapseButton.html('<i class="fa fa-arrow-down"></i> ' + this.options.readMoreText);
        this.$.data('state', 'collapsed');
        this.$gradient.show();
    };

    CollapseContent.prototype.expand = function () {
        this.$.css('max-height', '');
        this.$collapseButton.html('<i class="fa fa-arrow-up"></i> ' + this.options.readLessText);
        this.$.data('state', 'expanded');
        this.$gradient.hide();
    };

    var init = function () {
        additions.register('showMore', '[data-ui-show-more]', function ($match) {
            $match.each(function () {
                new CollapseContent(this);
            });
        });
    };

    module.templates = {
        showMore: '<div class="showMoreGradient" style="position:relative;cursor:pointer"><div style="bottom: 0;height: 40px;position: absolute;z-index: 30;width: 100%;"></div></div><a  class="showMore" href="#" style="display:block;margin: 5px 0;"></a>'
    };

    module.export({
        init: init,
        sortOrder: 100,
        CollapseContent: CollapseContent
    });
});
