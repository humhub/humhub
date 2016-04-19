humhub.modules.post = (function(module, $) {
    humhub.modules.registerAjaxHandler('humhub.modules.post.create', function(json) {
        humhub.modules.stream.getStream();
    }, function(error) {
        if(error)
        alert(error);
    });
    return module;
    
    humhub.ui.richtext.register()
})(humhub.modules.post || {}, $);