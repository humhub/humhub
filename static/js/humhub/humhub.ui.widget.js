/**
 * This module manages UI-Additions registered by other modules. Additions can be applied to DOM elements 
 * and are used to add a specific behaviour or manipulate the element itself. e.g: Richtext, Autosize input...
 * 
 * An addition can be registered for a specific selector e.g: <input data-addition-richtext ... />
 * It is possible to register multiple additions for the same selector.
 */
humhub.module('ui.widget', function(module, require, $) {

    var additions = require('ui.additions');
    var Component = require('action').Component;
    var object = require('util').object;
    var string = require('util').string;
    var action = require('action');
    var event = require('event');

    // Add selector for component detection so we can use data-ui-widget instead of data-action-component
    Component.addSelector('ui-widget');

    var Widget = function(node, options) {
        Component.call(this, (node instanceof $) ? node : $(node), options);
        this.errors = [];
        this.options = this.initOptions(options || {});

        this.initWidgetEvents();

        if(!this.validate()) {
            module.log.warn('Could not initialize widget.', this.errors);
        } else {
            var initData = this.$.data('ui-init');
            this.fire('beforeInit', [this, initData]);
            this.init(initData);

            if(this.options.widgetFadeIn) {
                this.$.fadeIn(this.options.widgetFadeIn);
            }

            this.fire('afterInit', [this]);
        }
    };

    object.inherits(Widget, Component);


    Widget.prototype.initWidgetEvents = function() {
        // Use internal event object for handling widget events.
        event.sub(this);
        
        var that = this;
        
        // Bind dom events to widget events actions.
        $.each(this.options, function(key, value) {
            if(string.startsWith(key, 'widgetAction')) {
                var eventType = string.cutPrefix(key, 'widgetAction').toLowerCase();
                that.$.on(eventType+'.humhub:widget:events', function() {
                    that.fire(eventType);
                });
            }
        });
    };
    
    Widget.prototype.fire = function(event, args, triggerDom) {
        // Trigger action if there is an action handler set
        var widgetAction = 'widgetAction' + string.capitalize(event);
        if(this.options[widgetAction]){
            var handler = this.options[widgetAction];
            if(string.startsWith(handler, 'this.')) {
                handler = string.cutPrefix(handler, 'this.');
                var handlerFunc = object.resolve(this, handler);
                if(object.isFunction(handlerFunc)) {
                    handlerFunc.apply(this);
                }
            } else {
                action.trigger(this.$, event, {handler: handler, params: args});
            }
        }
        
        // Trigger internal widget event
        this.trigger(event, args);
        
        // If required, trigger dom event
        if(triggerDom !== false) {
            this.$.trigger(event, args);
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
    Widget.componentData = 'humhub-widget';

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

    Widget.prototype.isVisible = function() {
        return this.$.is(':visible');
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

    Widget.prototype.statusError = function(title) {
        var msg = title || module.text('error.title');;
        msg += '<br /><br /><ul style="list-style:none;">';

        $.each(this.errors, function(i, error) {
            if(error && !object.isArray(error)) {
                msg += '<li>' + error + '</li>';
            } else if(!error[0]) {
                msg += '<li>' + module.text('error.unknown') + '</li>';
            } else {
                msg += '<li>' + error[0] + '</li>';
            }
        });

        msg += '</ul>';
        module.log.error(msg, true);
        this.errors = [];
    };
    
    Widget.prototype.statusInfo = function(infos, title) {
        var msg = title || module.text('info.title');
        msg += '<br /><br /><ul style="list-style:none;">';

        $.each(infos, function(i, error) {
            if(error && !object.isArray(error)) {
                msg += '<li>' + error + '</li>';
            } else if(!error[0]) {
                msg += '<li>' + module.text('error.unknown') + '</li>';
            } else {
                msg += '<li>' + error[0] + '</li>';
            }
        });

        msg += '</ul>';
        module.log.info(msg, true);
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
            if(that.$.is(':visible')) {
                that.$.fadeOut('fast', function() {
                    resolve(that);
                });
            }
        });
    };

    Widget.prototype.fadeIn = function() {
        var that = this;
        return new Promise(function(resolve, reject) {
            if(!that.$.is(':visible')) {
                that.$.fadeIn('fast', function() {
                    resolve(that);
                });
            }
        });
    };

    Widget.exists = function(ns) {
        return $('[data-ui-widget="' + ns + '"]').length > 0;
    };


    var init = function() {
        additions.register('ui.widget', '[data-ui-init]', function($match) {
            $match.each(function(i, node) {
                Widget.instance(node);
            });
        });
    };

    module.export({
        Widget: Widget,
        init: init,
    });
});