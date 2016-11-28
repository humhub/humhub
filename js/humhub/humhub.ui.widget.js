/**
 * This module manages UI-Additions registered by other modules. Additions can be applied to DOM elements 
 * and are used to add a specific behaviour or manipulate the element itself. e.g: Richtext, Autosize input...
 * 
 * An addition can be registered for a specific selector e.g: <input data-addition-richtext ... />
 * It is possible to register multiple additions for the same selector.
 */
humhub.module('ui.widget', function(module, require, $) {

    var additions = require('ui.additions');

    var Widget = function(node, options, name) {
        this.$ = (node instanceof $) ? node : $(node);
        this.errors = [];
        this.options = this.initOptions(options || {});

        if (!this.validate()) {
            module.log.warn('Could not initialize widget.', this.errors);
        } else {
            this.init(this.$.data('ui-init'));
            this.$.data(name, this);
        }
    };

    /**
     * Defines the data attribute used for identification of the widget and widget class.
     * This can be overwritten in case some widgets are using a more descriptive mark.
     */
    Widget.widget = 'ui-widget';

    /**
     * This value should be overwritten by widget to allow different widgets on the same node.
     * This is used to receive the widget instance by calling $node.data(Widget.widgetData);
     */
    Widget.widgetData = 'humhub-widget';

    /**
     * Can be overwritten by widget to provide some default values for a widget.
     * @returns key/value pair opject
     */
    Widget.prototype.getDefaultOptions = function() {
        return {};
    };

    /**
     * Can be overwritten to set some widget rules e.g node type checks or data/option checks.
     * @returns {Boolean}
     */
    Widget.prototype.validate = function() {
        return true;
    };

    /**
     * Can be overwritten to initialize the widget.
     * @returns {undefined}
     */
    Widget.prototype.init = function() {
        // Abstract;
    };

    Widget.prototype.initOptions = function(options) {
        return $.extend(this.getDefaultOptions(options), this.$.data(), options);
    };

    Widget.prototype.on = function(event, handler) {
        this.$.on(event, handler);
    };

    Widget.prototype.one = function(event, handler) {
        this.$.one(event, handler);
    };

    Widget.prototype.off = function(event) {
        this.$.off(event);
    };

    Widget.prototype.statusError = function(title) {
        var msg = title || 'Error:';
        msg += '<br /><br /><ul style="list-style:none;">';

        $.each(this.errors, function(i, error) {
            msg += '<li>' + error[0] + '</li>';
        });

        msg += '</ul>';
        module.log.error(msg, true);
        this.errors = [];
    };

    Widget.instance = function(node, options) {
        var $node = (node instanceof $) ? node : $(node);

        if (!$node.length) {
            return;
        }

        var ns = $node.data(Widget.widget);

        var WidgetClass = (ns) ? require(ns) : this;

        if (!WidgetClass) {
            module.log.error('No valid widget class found for given node: ' + ns, this, true);
            return;
        } else if ($node.data(WidgetClass.widgetData)) {
            return $node.data(WidgetClass.widgetData);
        } else {
            return _createInstance(WidgetClass, node, options);
        }
    };

    var _createInstance = function(WidgetClass, node, options) {
        var instance = new WidgetClass();
        Widget.call(instance, node, options, WidgetClass.widgetData);
        return instance;
    };

    Widget.prototype.show = function() {
        this.$.show();
    };

    Widget.prototype.hide = function() {
        this.$.hide();
    };

    Widget.prototype.fadeOut = function() {
        var that = this;
        return new Promise(function(resolve, reject) {
            if (that.$.is(':visible')) {
                that.$.fadeOut('fast', function() {
                    resolve(that);
                });
            }
        });
    };

    Widget.prototype.fadeIn = function() {
        var that = this;
        return new Promise(function(resolve, reject) {
            if (!that.$.is(':visible')) {
                that.$.fadeIn('fast', function() {
                    resolve(that);
                });
            }
        });
    };
    
    Widget.exists = function(ns) {
        return $('[data-ui-widget="'+ns+'"]').length > 0;
    };


    var init = function() {
        additions.registerAddition('[data-ui-init]', function($match) {
            $match.each(function(i, node) {
                Widget.instance(node);
            });
        });
    };

    module.export({
        Widget: Widget,
        init: init
    });
});