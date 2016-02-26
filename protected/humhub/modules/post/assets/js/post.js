alert('asdf');
humhub.modules.post = (function(module, $) {
    humhub.modules.registerHandler('humhub.modules.post.create', function(json) {
        humhub.getStream();
    }, function(error) {
        
    });
    return module;
    
    humhub.ui.richtext.register()
})(humhub.modules.post || {}, $);