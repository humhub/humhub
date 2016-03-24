/**
 * This can should be used as parent class for all content implementations
 * @type undefined|Function
 */
humhub.additions = (function (module, $) {
    var _additions = {};
    
    var registerAddition = function (selector, addition) {
        if(!_additions[selector]) {
            _additions[selector] = [];
        }
        
        _additions[selector].push(addition);
    };
    
    var applyTo = function(element) {
        var $element = $(element);
        $.each(_additions, function(selector, additions) {
            $.each(additions, function(i, addition) {
                $.each($element.find(selector).addBack(selector), function() {
                    var $match = $(this);
                    addition.apply($match, [$match, $element]);
                });
            });
        });
    }
    
    return {
        registerAddition: registerAddition,
        applyTo: applyTo
    };
})(humhub.additions || {}, $);