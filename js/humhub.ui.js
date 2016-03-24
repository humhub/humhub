humhub.ui = (function (module, $) {
    //Init default additions
    humhub.additions.registerAddition('.autosize', function($match) {
        $match.autosize();
    });
    
    return module;
})(humhub.ui || {}, $);