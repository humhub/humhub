humhub.module('post', function(module, require, $) {
    var Widget = require('ui.widget').Widget;
    var object = require('util').object;

    var Post = function(node, options) {
        Widget.call(this, node, options);
    };

    object.inherits(Post, Widget);
    
    module.export({
        Post: Post
    });

});