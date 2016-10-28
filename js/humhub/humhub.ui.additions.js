/**
 * This module manages UI-Additions registered by other modules. Additions can be applied to DOM elements 
 * and are used to add a specific behaviour or manipulate the element itself. e.g: Richtext, Autosize input...
 * 
 * An addition can be registered for a specific selector e.g: <input data-addition-richtext ... />
 * It is possible to register multiple additions for the same selector.
 */
humhub.initModule('ui.additions', function (module, require, $) {

    var event = require('event');

    var _additions = {};

    /**
     * Registers an addition for a given jQuery selector. There can be registered
     * multiple additions for the same selector.
     * 
     * @param {string} selector jQuery selector
     * @param {function} addition addition function
     * @returns {undefined}
     */
    module.registerAddition = function (selector, addition) {
        if (!_additions[selector]) {
            _additions[selector] = [];
        }

        _additions[selector].push(addition);
    };

    /**
     * Applies all matched additions to the given element and its children
     * @param {type} element
     * @returns {undefined}
     */
    module.applyTo = function (element) {
        var $element = $(element);
        $.each(_additions, function (selector, additions) {
            $.each(additions, function (i, addition) {
                $.each($element.find(selector).addBack(selector), function () {
                    try {
                        var $match = $(this);
                        addition.apply($match, [$match, $element]);
                    } catch (e) {
                        console.error('Error while applying addition on selector ' + selector, e);
                    }
                });
            });
        });
    };

    module.init = function () {
        event.on('humhub:modules:client:pjax:afterPageLoad', function (evt, cfg) {
            module.applyTo(cfg.options.container);
        });
        
        event.on('humhub:afterInit', function (evt) {
            module.applyTo($('html'));
        });

        this.registerAddition('.autosize', function ($match) {
            $match.autosize();
        });

        //TODO: apply to html on startup, the problem is this could crash legacy code.
    };
});