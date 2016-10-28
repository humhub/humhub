/**
 * Thid module can be used by humhub sub modules for registering handlers and serves as core module for executing actions triggered in the gui.
 * A module can either register global handler by using the registerHandler and registerAjaxHandler functions or use the content mechanism.
 */
humhub.initModule('action', function (module, require, $) {
    var _handler = {};
    var object = require('util').object;
    var string = require('util').string;
    var client = require('client');
    var loader = require('ui.loader');

    module.initOnPjaxLoad = false;

    var DATA_COMPONENT = 'action-component';
    var DATA_COMPONENT_SELECTOR = '[data-' + DATA_COMPONENT + ']';

    var Component = function (container) {
        if (!container) {
            return;
        }
        this.$ = (object.isString(container)) ? $('#' + container) : container;
        this.base = this.$.data(DATA_COMPONENT);
    };

    Component.prototype.data = function (dataSuffix) {
        var result = this.$.data(dataSuffix);
        if (!result) {
            var parentComponent = this.parent();
            if (parentComponent) {
                return parentComponent.data(dataSuffix);
            }
        }
        return result;
    };

    Component.prototype.parent = function () {
        var $parent = this.$.parent().closest(DATA_COMPONENT_SELECTOR);
        if ($parent.length) {
            try {
                var ParentType = require($parent.data(DATA_COMPONENT));
                return new ParentType($parent);
            } catch (err) {
                console.error('Could not instantiate parent component: ' + $parent.data(DATA_COMPONENT), err);
            }
        }
    };

    Component.prototype.children = function () {
        var result = [];
        this.$.find(DATA_COMPONENT_SELECTOR).each(function () {
            var component = Component.getInstance($(this));
            if (component) {
                result.push(component);
            }
        });
        return result;
    };

    Component.prototype.hasAction = function (action) {
        return this.actions().indexOf(action) >= 0;
    };

    Component.prototype.actions = function () {
        return [];
    };

    Component.getInstance = function ($node) {
        //Determine closest component node (parent or or given node)
        $node = (object.isString($node)) ? $('#' + $node) : $node;
        var $componentRoot = ($node.data(DATA_COMPONENT)) ? $node : Component.getClosestComponentNode($node);

        var componentType = $componentRoot.data(DATA_COMPONENT);

        var ComponentType = require(componentType);
        if (ComponentType) {
            return new ComponentType($componentRoot);
        } else {
            module.log.error('Tried to instantiate component with invalid type: ' + componentType);
        }
    };

    Component.getClosestComponentNode = function ($element) {
        return $element.closest(DATA_COMPONENT_SELECTOR);
    };

    /**
     * Handles the given componentAction event. The event should provide the following properties:
     * 
     *  $trigger (required) : the trigger node of the event
     *  handler (required)  : the handler functionn name to be executed on the component
     *  type (optoinal)     : the event type 'click', 'change',...
     * 
     * @param {object} event - event object
     * @returns {Boolean} true if the componentAction could be executed else false
     */
    Component.handleAction = function (event) {
        var component = Component.getInstance(event.$trigger);
        if (component) {
            //Check if the content instance provides this actionhandler
            if (event.handler && component[event.handler]) {
                component[event.handler](event);
                return true;
            }
        }
        return false;
    };

    /**
     * Constructor for initializing the module.
     */
    module.init = function () {
        //Binding default action types
        this.bindAction(document, 'click', '[data-action-click]');
        this.bindAction(document, 'dblclick', '[data-action-dblclick]');
        this.bindAction(document, 'change', '[data-action-mouseout]');

        updateBindings();

        //Add addition for loader buttons
        require('ui.additions').registerAddition('[data-action-load-button]', function () {
            var that = this;
            this.on('click.humhub-action-load-button', function (evt) {
                if (!that.find('.action-loader').length) {
                    that.append('<span class="action-loader"><i class="fa fa-spinner fa-pulse"></i></span>');
                }
            });
        });
    };

    /**
     * Registers a given handler with the given id.
     * 
     * This handler will be called e.g. after clicking a button with the handler id as
     * data-action-click attribute.
     * 
     * The handler can access additional event information through the argument event.
     * The this object within the handler will be the trigger of the event.
     * 
     * @param {string} id handler id should contain the module namespace
     * @param {function} handler function with one event argument
     * @returns {undefined}
     */
    module.registerHandler = function (id, handler) {
        if (!id) {
            return;
        }

        if (handler) {
            _handler[id] = handler;
        }
    };



    /**
     * Registers an ajax eventhandler.
     * The function can either be called with four arguments (id, successhandler, errorhandler, additional config)
     * or with two (id, cfg) where tha handlers are contained in the config object itself.
     * 
     * The successhandler will be called only if the response does not contain any errors or errormessages.
     * So the errorhandler is called for application and http errors.
     * 
     * The config can contain additional ajax settings.
     * 
     * @param {type} id
     * @param {type} success
     * @param {type} error
     * @param {type} cfg
     * @returns {undefined}
     */
    module.registerAjaxHandler = function (id, success, error, cfg) {
        cfg = cfg || {};
        if (!id) {
            return;
        }

        if (object.isFunction(success)) {
            cfg.success = success;
            cfg.error = error;
        } else {
            cfg = success;
        }

        if (success) {
            _handler[id] = function (event) {
                var path = $(this).data('action-url-' + event.type) || $(this).data('action-url');
                client.ajax(path, cfg);
            };
        }
    };

    var actionBindings = [];

    var updateBindings = function () {
        module.log.debug('Update bindings');
        $.each(actionBindings, function (i, binding) {
            var $targets = $(binding.selector);
            $targets.off(binding.event).on(binding.event, function (evt) {
                module.log.debug('Handle direct trigger action', evt);
                return binding.handle(evt, $(this));
            });
            $targets.data('action-'+binding.event, true);
        });
    };

    /**
     * ActionBinding instances are used to store the binding settings and handling
     * binding events.
     * 
     * @param {type} cfg
     * @returns {humhub_action_L5.ActionBinding}
     */
    var ActionBinding = function (cfg) {
        cfg = cfg || {};
        this.parent = cfg.parent;
        this.eventType = cfg.type;
        this.event = cfg.event;
        this.selector = cfg.selector;
        this.directHandler = cfg.directHandler;
    };

    /**
     * Handles an action event for the given $trigger node.
     * 
     * This handler searches for a valid handler, by checking the following handler types in the given order:
     * 
     *  - Direct-ActionHandler is called if a directHandler was given when binding the action.
     *  - Component-ActionHandler is called if $trigger is part of a component and the component handler can be resolved
     *  - Global-ActionHandler is called if we find a handler in the _handler array. See registerHandler, registerAjaxHandler
     *  - Namespace-ActionHandler is called if we can resolve an action by namespace e.g: data-action-click="myModule.myAction"
     *  
     * @param {type} evt the originalEvent
     * @param {type} $trigger the jQuery node which triggered the event
     * @returns {undefined}
     */
    ActionBinding.prototype.handle = function (evt, $trigger) {
        module.log.debug('Handle Action', this);
        evt.preventDefault();
        
        var event = this.createActionEvent(evt, $trigger);

        // Search and execute a stand alone handler or try to call the content action handler
        try {
            // Check for a direct action handler
            if (object.isFunction(this.directHandler)) {
                this.directHandler.apply($trigger, [event]);
                return;
            }

            // Check for a component action handler
            if (Component.handleAction(event)) {
                return;
            }

            // Check for global registered handlers
            if (_handler[this.handler]) {
                _handler[this.handler].apply($trigger, [event]);
                return;
            }

            // As last resort we try to call the action by namespace for handlers like humhub.modules.myModule.myAction
            var splittedNS = this.handler.split('.');
            var handlerAction = splittedNS[splittedNS.length - 1];
            var target = require(string.cutsuffix(this.handler, '.' + handlerAction));

            if (object.isFunction(target)) {
                target[handlerAction](event);
            } else {
                module.log.error('actionHandlerNotFound', this, true);
            }
        } catch (e) {
            module.log.error('default', e, true);
            _removeLoaderFromEventTarget(evt);
        } finally {
            // Just to get sure the handler is not called twice.
            if(evt.originalEvent) {
                evt.originalEvent.actionHandled = true;
            }
        }
    };

    ActionBinding.prototype.createActionEvent = function (evt, $trigger) {
        var event = $.Event(this.eventType);
        event.originalEvent = evt;

        // Add some additional action related data to our event.
        event.$trigger = $trigger;

        // If the trigger contains an url setting we add it to the event object, and prefer the typed url over the global data-action-url
        event.url = $trigger.data('action-' + this.eventType + '-url') || $trigger.data('action-url');
        event.params = $trigger.data('action-' + this.eventType + '-params') || $trigger.data('action-params');

        //Get the handler id, either a stand alone handler or a content handler function e.g: 'edit' 
        event.handler = $trigger.data('action' + '-' + this.eventType);

        if ($trigger.is(':submit')) {
            event.$form = $trigger.closest('form');
        }

        event.finish = function () {
            _removeLoaderFromEventTarget(evt);
        };

        return event;
    };

    var _removeLoaderFromEventTarget = function (evt) {
        if (evt && evt.target) {
            loader.reset(evt.target);
        }
    };


    /**
     * Binds an delegate wrapper event handler to the parent node. This is used to detect action handlers like 
     * data-action-click events and map the call to either a stand alone handler or a content
     * action handler. The trigger of a contentAction has to be contained in a data-content-base node.
     * 
     * This function uses the jQuery event delegation:
     * 
     *  $(parent).on(type, selector, function(){...});
     * 
     * This assures the event binding for dynamic content (ajax content etc..)
     * 
     * @see {@link humhub.modules.content.handleAction}
     * @param {Node|jQuery} parent - the event target
     * @param {string} type - event type e.g. click, change,...
     * @param {string} selector - jQuery selector 
     * @param {string} selector - jQuery selector 
     */
    module.bindAction = function (parent, type, selector, directHandler) {
        parent = parent || document;
        var $parent = parent.jquery ? parent : $(parent);
        var actionEvent = type + '.humhub-action';

        var actionBinding = new ActionBinding({
            parent: parent,
            type: type,
            event: actionEvent,
            selector: selector,
            directHandler: directHandler
        });

        // Add new ActionBinding with given settings.
        actionBindings.push(actionBinding);

        $parent.on(actionEvent, selector, function (evt) {
            evt.preventDefault();

            // Get sure we don't call the handler twice if the event was already handled by trigger.
            if ($(this).data('action-'+actionBinding.event) 
                    || evt.originalEvent && evt.originalEvent.actionHandled) {
                return;
            }

            module.log.debug('Detected unhandled action', actionBinding);
            updateBindings();
            actionBinding.handle(evt, $(this));
        });

        return;
    };

    module.export({
        Component: Component
    });
});