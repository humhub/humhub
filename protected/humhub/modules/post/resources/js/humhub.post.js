humhub.module('post', function(module, require, $) {
    var Widget = require('ui.widget').Widget;

    var Post = Widget.extend();

    Post.prototype.init = function() {
        var that = this;

        this.$.find('[data-ui-richtext]').on('afterRender', function() {
            var $rtContent = $(this).children();
            var $first = $rtContent.first();

            if($rtContent.length === 1 && $first.is('p') && $first.text().length < 150 && !$first.find('br').length) {
                that.$.addClass('post-short-text');
            }
        })
    };

    module.export({
        Post: Post
    });
});
