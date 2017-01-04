humhub.module('post', function(module, require, $) {
    var Widget = require('ui.widget').Widget;
    var object = require('util').object;

    var Post = function(node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Post, Widget);

    Post.prototype.getDefaultOptions = function() {
        return {
            collapse: 310,
            readMore: module.text('default.readMore'),
            readLess: module.text('default.readLess')
        };
    };

    Post.prototype.init = function() {
        this.initCollapse();
    };

    Post.prototype.initCollapse = function() {
        var that = this;

        var height = this.$.outerHeight();
        this.$collapseButton = this.$.siblings('.showMore');

        if(this.options.prevCollapse) {
            return;
        }

        // If height expands the max height we init the collapse post logic
        if(height > this.options.collapse) {
            if(!this.$collapseButton.length) {
                this.$collapseButton = $(Post.templates.showMore);
                this.$.after(this.$collapseButton);
            }
            
            // Init collapse button
            this.$collapseButton.on('click', function(evt) {
                evt.preventDefault();
                that.toggleCollapse();
            }).show();
            
            // Set init state
            if(this.data('state') !== 'expanded') {
                this.collapse();
            } else {
                this.expand();
            }
        }
    };

    Post.prototype.toggleCollapse = function() {
        debugger;
        if(this.$.data('state') === 'collapsed') {
            this.expand();
        } else {
            this.collapse();
        }
    };

    Post.prototype.collapse = function() {
        this.$.css({'display': 'block', 'max-height': this.options.collapse+'px'});
        this.$collapseButton.html('<i class="fa fa-arrow-down"></i> ' + this.options.readMore);
        this.$.data('state', 'collapsed');
    };

    Post.prototype.expand = function() {
        this.$.css('max-height', '');
        this.$collapseButton.html('<i class="fa fa-arrow-up"></i> ' + this.options.readLess);
        this.$.data('state', 'expanded');
    };
    
    Post.templates = {
        showMore : '<a href="#" style="display:block;margin: 5px 0;"></a>'
    };
    
    module.export({
        Post: Post
    });

});