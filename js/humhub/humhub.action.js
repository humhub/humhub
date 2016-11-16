/**
 * Thid module can be used by humhub sub modules for registering handlers and serves as core module for executing actions triggered in the gui.
 * A module can either register global handler by using the registerHandler functions or use the component mechanism.
 */
humhub.initModule('action', function (module, require, $) {
    var _handler = {};
    var object = require('util').object;
    var string = require('util').string;
    var loader = require('ui.loader');
    
    var BLOCK_NONE = 'none';
    var BLOCK_SYNC = 'sync';
    var BLOCK_ASYNC = 'async';

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

        if(!componentType) {
            return;
        }

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
     * @param {object} event - event object
     * @returns {Boolean} true if the componentAction could be executed else false
     */
    Component.handleAction = function (event) {

        var component = Component.getInstance(event.$target);

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
    var init = function ($isPjax) {
        if(!$isPjax) {
            //Binding default action types
            this.bindAction(document, 'click', '[data-action-click]');
            this.bindAction(document, 'dblclick', '[data-action-dblclick]');
            this.bindAction(document, 'change', '[data-action-change]');
        }

        updateBindings();
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
    var registerHandler = function (id, handler) {
        if (!id) {
            return;
        }

        if (handler) {
            _handler[id] = handler;
        }
    };

    var actionBindings = [];

    var updateBindings = function () {
        module.log.debug('Update action bindings');
        $.each(actionBindings, function (i, binding) {
            var $targets = $(binding.selector);
            $targets.off(binding.event).on(binding.event, function (evt) {
                module.log.debug('Handle direct trigger action', evt);
                return binding.handle(evt, $(this));
            });
            $targets.data('action-' + binding.event, true);
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
     *  - Global-ActionHandler is called if we find a handler in the _handler array. See registerHandler
     *  - Namespace-ActionHandler is called if we can resolve an action by namespace e.g: data-action-click="myModule.myAction"
     *  
     * Once triggered the handler will block the event for this actionbinding until the actionevents .finish is called.
     * This is used to prevent multiple triggering of actions. This behaviour can be disabled by setting:
     * 
     *  data-action-prevent-block or data-action-prevent-block-<eventType>
     *  
     * @param {type} evt the originalEvent
     * @param {type} $trigger the jQuery node which triggered the event
     * @returns {undefined}
     */
    ActionBinding.prototype.handle = function (originalEvent, $trigger) {
        if (originalEvent) {
            originalEvent.preventDefault();
        }
        
        if (this.isBlocked($trigger)) {
            return;
        }
        
        if(this.isBlockAction($trigger)) {
            this.block($trigger);
        }

        module.log.debug('Handle Action', this);

        var event = this.createActionEvent(originalEvent, $trigger);

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
            if (_handler[event.handler]) {
                _handler[event.handler].apply($trigger, [event]);
                return;
            }

            // As last resort we try to call the action by namespace for handlers like humhub.modules.myModule.myAction
            var splittedNS = event.handler.split('.');
            var handlerAction = splittedNS[splittedNS.length - 1];
            var target = require(string.cutsuffix(event.handler, '.' + handlerAction));

            if (object.isFunction(target[handlerAction])) {
                target[handlerAction](event);
            } else {
                module.log.error('actionHandlerNotFound', event.handler, true);
            }
        } catch (e) {
            module.log.error('error.default', e, true);
            event.finish();
        } finally {
            // Just to get sure the handler is not called twice.
            if (originalEvent) {
                originalEvent.actionHandled = true;
            }

            if (this.isBlockType($trigger, BLOCK_SYNC)) {
                event.finish();
            }
        }
    };
    
    /**
     * data-action-click-url vs data-action-url-click
     * data-action-click-block vs data-action-block-click
     * @param {type} $trigger
     * @param {type} name
     * @param {type} def
     * @returns {unresolved}
     */
    ActionBinding.prototype.data = function($trigger, name, def) {
        var result = $trigger.data('action-'+this.eventType+'-'+name);
        
        if(!result) {
            result = $trigger.data('action-'+name);
        }
        
        return result || def;
    };
    
    ActionBinding.prototype.getUrl = function($trigger) {
        return this.data($trigger, 'url');
    };
    
    /**
     * Checks if the trigger should be blocked before running the action.
     * 
     * @param {type} $trigger
     * @returns {Boolean}
     */
    ActionBinding.prototype.isBlockAction = function($trigger) {
        return !this.isBlockType($trigger, BLOCK_NONE);
    };
    
    /**
     * Checks the given block data setting of $trigger agains a blocktype.
     * 
     * @param {type} $trigger
     * @param {type} type
     * @returns {Boolean}
     */
    ActionBinding.prototype.isBlockType = function($trigger, type) {
        var defaultBlockType = (this.isSubmit($trigger) || this.getUrl($trigger))  ? BLOCK_ASYNC : BLOCK_SYNC;
        var blockType = this.data($trigger, 'block', defaultBlockType);
        
        return type === blockType;
    };
    
    ActionBinding.prototype.isSubmit = function($trigger) {
        return $trigger.is(':submit') || $trigger.data('action-submit');
    };
    
    /**
     * Checks if $trigger is currently blocked.
     * 
     * @param {type} $trigger
     * @returns {unresolved}
     */
    ActionBinding.prototype.isBlocked = function($trigger) {
        return this.data($trigger, 'blocked');
    };
    
    /**
     * Blocks $trigger, which will disable further action calls.
     * 
     * @param {type} $trigger
     * @returns {undefined}
     */
    ActionBinding.prototype.block = function($trigger) {
        $trigger.data('action-'+this.eventType+'-blocked', true);
    };
    
    ActionBinding.prototype.unblock = function($trigger) {
        $trigger.data('action-'+this.eventType+'-blocked', false);
    };

    ActionBinding.prototype.createActionEvent = function (evt, $trigger) {
        var event = $.Event(this.eventType);
        event.originalEvent = evt;

        // Add some additional action related data to our event.
        event.$trigger = $trigger;

        event.$target = this.data($trigger, 'target', $trigger);

        // If the trigger contains an url setting we add it to the event object, and prefer the typed url over the global data-action-url
        event.url = this.data($trigger, 'url');
        event.params = this.data($trigger, 'params', {});

        //Get the handler id, either a stand alone handler or a content handler function e.g: 'edit' 
        event.handler = $trigger.data('action' + '-' + this.eventType);

        if (this.isSubmit($trigger)) {
            event.$form = $trigger.closest('form');
        }

        var that = this;
        var eventType = this.eventType;
        event.finish = function () {
            _removeLoaderFromEventTarget(evt);
            that.unblock($trigger);
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
    var bindAction = function (parent, type, selector, directHandler) {
        parent = parent || document;
        var $parent = (parent instanceof $) ? parent : $(parent);
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

        $parent.off(actionEvent).on(actionEvent, selector, function (evt) {
            evt.preventDefault();
            // Get sure we don't call the handler twice if the event was already handled by trigger.
            if ($(this).data('action-' + actionBinding.event)
                    || (evt.originalEvent && evt.originalEvent.actionHandled)) {
                module.log.info('Blocked action event ' + actionEvent, actionBinding);
                module.log.info('Blocked event triggered by', $(this));
                return;
            }

            module.log.debug('Detected unhandled action', actionBinding);
            updateBindings();
            actionBinding.handle(evt, $(this));
        });

        return;
    };

    /**
     * This function can be called to manually trigger an action event of the given $trigger.
     * This can be used for example for additional event types without actually binding the
     * event to $trigger.
     * 
     * e.g manually trigger a custom data-action-done action of an ui component.
     * 
     * @param {type} $trigger
     * @param {type} type
     * @param {type} originalEvent
     * @returns {undefined}
     */
    var trigger = function ($trigger, type, originalEvent, block) {
        if(block === false) {
            $trigger.data('action-block', BLOCK_NONE);
        } else if(object.isString(block)) {
            $trigger.data('action-block', block);
        }
        
        if (!$trigger.data('action-' + type)) {
            return;
        }

        new ActionBinding({
            type: type,
            event: type
        }).handle(originalEvent, $trigger);
    };

    module.export({
        init: init,
        bindAction: bindAction,
        registerHandler: registerHandler,
        Component: Component,
        trigger: trigger,
        BLOCK_NONE: BLOCK_NONE,
        BLOCK_SYNC: BLOCK_SYNC,
        BLOCK_ASYNC: BLOCK_ASYNC
    });
});