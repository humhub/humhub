/**
 * This module manages UI-Additions registered by other modules. Additions can be applied to DOM elements 
 * and are used to add a specific behaviour or manipulate the element itself. e.g: Richtext, Autosize input...
 * 
 * An addition can be registered for a specific selector e.g: <input data-addition-richtext ... />
 * It is possible to register multiple additions for the same selector.
 */
humhub.initModule('ui.additions', function (module, require, $) {

    var event = require('event');

    module.initOnPjaxLoad = false;

    var _additions = {};

    /**
     * Registers an addition for a given jQuery selector. There can be registered
     * multiple additions for the same selector.
     * 
     * @param {string} selector jQuery selector
     * @param {function} addition addition function
     * @returns {undefined}
     */
    var registerAddition = function (selector, addition) {
        if (!_additions[selector]) {
            _additions[selector] = [];
        }

        _additions[selector].push(addition);

        // Make sure additions registrated after humhub:ready also affect element
        if (humhub.initialized) {
            apply($('body'), selector, addition);
        }
    };

    /**
     * Applies all matched additions to the given element and its children
     * @param {type} element
     * @returns {undefined}
     */
    var applyTo = function (element) {
        var $element = (element instanceof $) ? element : $(element);
        $.each(_additions, function (selector, additions) {
            $.each(additions, function (i, addition) {
                try {
                    apply($element, selector, addition);
                    var $match = $element.find(selector).addBack(selector);
                    addition.apply($match, [$match, $element]);
                } catch (e) {
                    module.log.error('Error while applying addition on selector ' + selector, e);
                }
            });
        });
    };

    /**
     * Applies a given addition to all matches of the given $element.
     * @param {type} $element
     * @param {type} selector
     * @param {type} addition
     * @returns {undefined}
     */
    var apply = function ($element, selector, addition) {
        var $match = $element.find(selector).addBack(selector);
        addition.apply($match, [$match, $element]);
    };

    var init = function () {
        event.on('humhub:ready', function (evt) {
            module.applyTo($('body'));
        });

        // Autosize textareas
        this.registerAddition('.autosize', function ($match) {
            $match.autosize();
        });

        // Show tooltips on elements
        this.registerAddition('.tt', function ($match) {
            $match.tooltip({
                html: false,
                container: 'body'
            });
        });

        // Show popovers on elements
        this.registerAddition('.po', function ($match) {
            $match.popover({html: true});
        });

        // Activate placeholder text for older browsers (specially IE)
        this.registerAddition('input, textarea', function ($match) {
            $match.placeholder();
        });

        // Replace the standard checkbox and radio buttons
        this.registerAddition(':checkbox, :radio', function ($match) {
            $match.flatelements();
        });

        // Deprecated!
        this.registerAddition('a[data-loader="modal"], button[data-loader="modal"]', function ($match) {
            $match.loader();
        });

        //TODO: apply to html on startup, the problem is this could crash legacy code.
    };

    var switchButtons = function (outButton, inButton, animation) {
        animation = animation || 'bounceIn';
        var $out = (outButton instanceof $) ? outButton : $(outButton);
        var $in = (inButton instanceof $) ? inButton : $(inButton);

        $out.hide();
        $in.addClass('animated ' + animation).show();
    };

    module.export({
        init: init,
        applyTo: applyTo,
        apply: apply,
        registerAddition: registerAddition,
        switchButtons: switchButtons
    });
});