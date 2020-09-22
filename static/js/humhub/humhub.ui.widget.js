/**
 * This module manages UI-Additions registered by other modules. Additions can be applied to DOM elements
 * and are used to add a specific behaviour or manipulate the element itself. e.g: Richtext, Autosize input...
 *
 * An addition can be registered for a specific selector e.g: <input data-addition-richtext ... />
 * It is possible to register multiple additions for the same selector.
 */
humhub.module('ui.widget', function (module, require, $) {

    var additions = require('ui.additions');
    var Component = require('action').Component;
    var object = require('util').object;
    var string = require('util').string;
    var action = require('action');
    var event = require('event');
    var loader = require('ui.loader');

    // Add selector for component detection so we can use data-ui-widget instead of data-action-component
    Component.addSelector('ui-widget');

    var Widget = Component.extend(function (node, options) {
        Component.call(this, (node instanceof $) ? node : $(node), options);
        this.errors = [];
        this.options = this.initOptions(options || {});

        this.initWidgetEvents();

        if (!this.validate()) {
            module.log.warn('Could not initialize widget.', this.errors);
        } else {
            var initData = this.$.data('ui-init');
            this.fire('beforeInit', [this, initData]);
            this.init(initData);

            if (this.options.widgetFadeIn) {
                this.$.fadeIn(this.options.widgetFadeIn);
            }

            this.fire('afterInit', [this]);
        }
    });

    Widget.prototype.loader = function (show) {
        var $loaderNode = $(this.options.widgetLoader || this.$);

        if (show !== false) {
            // Either show an existing loader node marked with data-widget-loader or use loader.set on the given loader root node
            if ($loaderNode.is('[data-widget-loader]')) {
                $loaderNode.show();
            } else {
                loader.set($loaderNode);
            }
        } else {
            if ($loaderNode.is('[data-widget-loader]')) {
                $loaderNode.show();
            } else {
                loader.reset($loaderNode);
            }
        }
    };

    Widget.prototype.reload = function (options) {
        options = options || [];
        var that = this;

        if (!this.options.widgetReloadUrl) {
            return;
        }

        if (this.reloadXhr) {
            this.reloadXhr.abort();
        }

        this.fire('beforeReload');

        this.loader();

        var reloadOptions = $.extend({}, this.getReloadOptions(), options);
        reloadOptions.beforeSend = function (xhr) {
            that.reloadXhr = xhr;
        };
        reloadOptions.type = reloadOptions.method = 'POST';

        return require('client').ajax(this.options.widgetReloadUrl, reloadOptions).then(function (response) {
            that.reloadXhr = undefined;

            that.replace(response.output ? response.output : response.html);

            that.fire('afterReload', [response]);
            return response;
        }).catch(function(err) {
            if(err.errorThrown !== 'abort') {
                module.log.error(err, true);
            }
        }).finally(function() {
            that.loader(false);
        });
    };

    Widget.prototype.getReloadOptions = function () {
        return [];
    };

    Widget.prototype.replace = function (dom) {
        this.fire('beforeReplace');
        var $newDom = $(dom);
        var $oldDom = this.$;
        this.$.replaceWith($newDom);
        this.$ = $newDom;
        additions.applyTo(this.$);
        this.fire('afterReplace', [$oldDom]);
    };


    Widget.prototype.initWidgetEvents = function () {
        // Use internal event object for handling widget events.
        event.sub(this);

        var that = this;

        // Bind dom events to widget events actions.
        $.each(this.options, function (key, value) {
            if (string.startsWith(key, 'widgetAction')) {
                var eventType = string.lowerCaseFirstLetter(string.cutPrefix(key, 'widgetAction'));
                that.$.on(eventType + '.humhub:widget:events ' + eventType.toLowerCase + '.humhub:widget:events', function () {
                    that.fire(eventType, null, false);
                });
            }
        });
    };

    Widget.prototype.fire = function (event, args, triggerDom) {
        // Trigger action if there is an action handler set
        var widgetAction = 'widgetAction' + string.capitalize(event);
        if (this.options[widgetAction]) {
            var handler = this.options[widgetAction];
            if (string.startsWith(handler, 'this.')) {
                handler = string.cutPrefix(handler, 'this.');
                var handlerFunc = object.resolve(this, handler);
                if (object.isFunction(handlerFunc)) {
                    handlerFunc.apply(this);
                }
            } else {
                action.trigger(this.$, event, {handler: handler, params: args});
            }
        }

        // Trigger internal widget event
        this.trigger(event, args);

        // If required, trigger dom event
        if (triggerDom !== false) {
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
    Widget.prototype.getDefaultOptions = function () {
        return {};
    };

    /**
     * Can be overwritten to set some widget rules e.g node type checks or data/option checks.
     * @returns {Boolean}
     */
    Widget.prototype.validate = function () {
        return true;
    };

    Widget.prototype.isVisible = function () {
        return this.$.is(':visible');
    };

    /**
     * Can be overwritten to initialize the widget.
     * @returns {undefined}
     */
    Widget.prototype.init = function () {
        // Abstract;
    };

    Widget.prototype.initOptions = function (options) {
        var data = {};
        $.each(this.$.data(), function(key, value) {
            data[key] = (value === '') ? true : value;
        });
        return $.extend(this.getDefaultOptions(options), data, options);
    };

    Widget.prototype.statusError = function (title) {
        var msg = title || module.text('error.title');
        msg += '<br /><br /><ul style="list-style:none;">';

        $.each(this.errors, function (i, error) {
            if (error && !object.isArray(error)) {
                msg += '<li>' + error + '</li>';
            } else if (!error[0]) {
                msg += '<li>' + module.text('error.unknown') + '</li>';
            } else {
                msg += '<li>' + error[0] + '</li>';
            }
        });

        msg += '</ul>';
        module.log.error(msg, true);
        this.errors = [];
    };

    Widget.prototype.statusInfo = function (infos, title) {
        var msg = title || module.text('info.title');
        msg += '<br /><br /><ul style="list-style:none;">';

        $.each(infos, function (i, error) {
            if (error && !object.isArray(error)) {
                msg += '<li>' + error + '</li>';
            } else if (!error[0]) {
                msg += '<li>' + module.text('error.unknown') + '</li>';
            } else {
                msg += '<li>' + error[0] + '</li>';
            }
        });

        msg += '</ul>';
        module.log.info(msg, true);
    };

    Widget.prototype.show = function () {
        this.$.show();
        return this;
    };

    Widget.prototype.hide = function () {
        this.$.hide();
        return this;
    };

    Widget.prototype.fadeOut = function () {
        var that = this;
        return new Promise(function (resolve, reject) {
            if (that.$.is(':visible')) {
                that.$.fadeOut('fast', function () {
                    resolve(that);
                });
            }
        });
    };

    Widget.prototype.fadeIn = function () {
        var that = this;
        return new Promise(function (resolve, reject) {
            if (!that.$.is(':visible')) {
                that.$.fadeIn('fast', function () {
                    resolve(that);
                });
            }
        });
    };

    Widget.exists = function (ns) {
        return $('[data-ui-widget="' + ns + '"]').length > 0;
    };


    var init = function () {
        additions.register('ui.widget', '[data-ui-init]', function ($match) {
            $match.each(function (i, node) {
                Widget.instance(node);
            });
        });
    };

    module.export({
        Widget: Widget,
        init: init,
        sortOrder: 100,
    });
});
