/**
 * Thid module can be used by humhub sub modules for registering handlers and serves as core module for executing actions triggered in the gui.
 * A module can either register global handler by using the registerHandler functions or use the component mechanism.
 */
humhub.module('action', function(module, require, $) {
    var _handler = {};
    var object = require('util').object;
    var string = require('util').string;
    var loader = require('ui.loader');
    var modal = require('ui.modal', true);

    var BLOCK_NONE = 'none';
    var BLOCK_SYNC = 'sync';
    var BLOCK_ASYNC = 'async';
    var BLOCK_MANUAL = 'manual';

    var DATA_COMPONENT = 'action-component';
    
    module.initOnPjax = true;
    
    var processes = {};

    var Component = function(node, options) {
        if(!node) {
            return;
        }

        if(node instanceof $) {
            this.$ = node;
        } else if(object.isString(node)) {
            this.$ = $(node);
            if(!this.$.length) {
                this.$ = $('#' + node);
            }
        }

        this.base = Component.getNameSpace(this.$);

        this.$.data(this.static('component'), this);
    };

    Component._selectors = [DATA_COMPONENT];
    Component.component = 'humhub-component';

    Component._buildSelector = function() {
        Component._selector = Component._selectors.map(function(selector) {
            return '[data-' + selector + ']';
        }).join(',');
    };

    Component.prototype.data = function(dataSuffix) {
        var result = this.$.data(dataSuffix);
        if(!result) {
            var parentComponent = this.parent();
            if(parentComponent) {
                return parentComponent.data(dataSuffix);
            }
        }
        return result;
    };

    Component.prototype.parent = function() {
        var $parent = this.$.parent().closest(Component._selector);
        return Component.closest($parent);
    };

    Component.find = function(node, selector, includeSelf) {
        var result = [];
        var $node = (node instanceof $) ? node : $(node);
        var $query = $node.find(Component._selector);

        if(includeSelf) {
            $query.addBack(selector);
        }

        $query.each(function() {
            var component = Component.instance($(this));
            if(component && (!selector || component.$.is(selector))) {
                result.push(component);
            }
        });
        return result;
    };

    Component.prototype.children = function(selector) {
        return Component.find(this.$, selector);
    };

    Component.prototype.hasAction = function(action) {
        return this.actions().indexOf(action) >= 0;
    };

    Component.prototype.actions = function() {
        return [];
    };

    /**
     * Finds the closest component of the given node (including the node itself).
     * 
     * @param {type} node
     * @param {type} options
     * @returns {undefined}
     */
    Component.closest = function(node, options) {
        //Determine closest component node (parent or or given node)
        var $node = _getNode(node);

        if(!$node.length) {
            return;
        }

        // Search the component root, which is either the node itself or a surrounding component
        var $componentRoot = Component.getComponentRoot($node);
        var ns = Component.getNameSpace($componentRoot);

        if(!$componentRoot.length || !ns) {
            return;
        }

        return Component._getInstance(require(ns), $componentRoot, options);
    };

    Component.getComponentRoot = function($node) {
        var ns = Component.getNameSpace($node);

        if(ns) {
            return $node;
        } else {
            return Component.getClosestComponentNode($node);
        }
    };

    Component.getNameSpace = function(node) {
        var $node = (node instanceof $) ? node : $(node);
        var base;
        $.each(Component._selectors, function(i, selector) {
            base = $node.data(selector);
            if(base) {
                return false; // leave foreach
            }
        });
        return base;
    };

    /**
     * Creates a component instance out of the given node.
     * @param {type} node
     * @param {type} options
     * @returns {undefined}
     */
    Component.instance = function(node, options) {
        //Determine closest component node (parent or or given node)
        var $node = _getNode(node);

        if(!$node.length) {
            return;
        }

        var ns = Component.getNameSpace($node);

        var ComponentClass = (ns) ? require(ns) : this;
        return Component._getInstance(ComponentClass, $node, options);
    };

    var _getNode = function(node) {
        var $node = (node instanceof $) ? node : $(node);

        if(!$node.length && object.isString(node)) {
            $node = $('#' + node);
        }
        return $node;
    };

    Component._getInstance = function(ComponentClass, $node, options) {
        if(!ComponentClass) {
            module.log.error('No valid component class found for given node ', $node, true);
            return;
        } else if($node.data(ComponentClass.component)) {
            return $node.data(ComponentClass.component);
        } else {
            return ComponentClass.createInstance(ComponentClass, $node, options);
        }
    };

    Component.createInstance = function(ComponentClass, node, options) {
        return new ComponentClass(node, options);
    };

    Component.addSelector = function(selector) {
        Component._selectors.push(selector);
        Component._buildSelector();
    };

    Component.getClosestComponentNode = function($element) {
        return $element.closest(Component._selector);
    };

    /**
     * Thes method will search for a sorrounding component and try to execute
     * the event handler action on this component.
     * 
     * If no component is found or the component does not provide the action handler
     * we'll return false else true.
     * 
     * @param {object} event - event object
     * @returns {Boolean} true if the component action could be executed else false
     */
    Component.handleAction = function(event) {
        var component = Component.closest(event.$target);
        return (component) ? _executeAction(component, event.handler, event) : false;
    };

    /**
     * Constructor for initializing the module.
     */
    var init = function($isPjax) {
        if(!$isPjax) {
            Component._buildSelector();
            //Binding default action types
            this.bindAction(document, 'click', '[data-action-click]');
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
    var registerHandler = function(id, handler) {
        if(!id) {
            return;
        }

        if(handler) {
            _handler[id] = handler;
        }
    };

    var actionBindings = [];

    var updateBindings = function() {
        module.log.debug('Update action bindings');
        $.each(actionBindings, function(i, binding) {
            var $targets = $(binding.selector);
            $targets.off(binding.event).on(binding.event, function(evt) {
                module.log.debug('Handle direct trigger action', evt);
                return binding.handle({originalEvent: evt, $trigger: $(this)});
            });
            $targets.data('action-' + binding.event, true);
        });
    };
    
    var getProcessTrigger = function(id) {
        return processes[id];
    };

    /**
     * ActionBinding instances are used to store the binding settings and handling
     * binding events.
     * 
     * @param {type} cfg
     * @returns {humhub_action_L5.ActionBinding}
     */
    var ActionBinding = function(options) {
        options = options || {};
        this.parent = options.parent;
        // e.g. click
        this.eventType = options.type;
        // namespaced event e.g. click.humhub-action
        this.event = options.event;
        this.selector = options.selector;
        this.directHandler = options.directHandler;
    };

    /**
     * Handles an action event for the given $trigger node.
     * 
     * This handler searches for a valid action handler, by checking the following handler types in the given order:
     * 
     *  - Direct-ActionHandler is called if a directHandler was given when binding the action.
     *  - Component-ActionHandler is called if $trigger is part of a component and the component handler can be resolved
     *  - Global-ActionHandler is called if we find a handler in the _handler array. See registerHandler
     *  - Namespace-ActionHandler is called if we can resolve an action by namespace e.g: data-action-click="myModule.myAction"
     * 
     * Once triggered the handler can be blocked to prevent multiple click events. The block logic can be configured by setting
     * the data-action-block or more specific data-action-<eventType>-block on the $trigger node. The following block values are available:
     * 
     *  - 'none': No blocking at all
     *  - 'sync': Synchronous blocking, the block will be removed after the actionhandler was executed.
     *  - 'async': Asynchronous the block has to be manually removed by calling event.finish.
     *  
     *  If the action is provided with an url or is an submit action (data-action-submit or type="submit") the block value is set to 'async' by default,
     *  otherwise its set to 'sync'.
     *  
     *  Note: When using humhub.modules.client for submitting a form or sending a request and providing the action event, the event.finish will be
     *  called for you after we receive you response, so you do not have to call it manually.
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
    ActionBinding.prototype.handle = function(options) {
        var options = options || {};
        var $trigger = options.$trigger;
        
        if(this.data($trigger, 'process')) {
            processes[this.data($trigger, 'process')] = $trigger;
        }

        

        if(options.originalEvent) {
            options.originalEvent.preventDefault();
        }

        module.log.debug('Handle Action', this);

        var event = this.createActionEvent(options);

        if(object.isDefined(this.data($trigger, 'confirm')) && !options.confirmed) {
            var that = this;
            modal.confirm($trigger).then(function(confirmed) {
                if(confirmed) {
                    options.confirmed = true;
                    that.handle(options);
                } else {
                    event.finish();
                }
            });
            return;
        }
        
        // Reset value just to get sure the options are not reused.
        options.confirmed = undefined;
        
        if(this.isBlocked($trigger)) {
            module.log.warn('Blocked action execution ', $trigger);
            return;
        }

        if(this.isBlockAction($trigger)) {
            this.block($trigger);
        }

        try {
            // Check for a direct action handler
            if(object.isFunction(this.directHandler)) {
                this.directHandler.apply($trigger, _getArgs(event));
                return;
            }
            
            // Check for a component action handler
            if(Component.handleAction(event)) {
                return;
            }

            // Check for global registered handlers
            if(_handler[event.handler]) {
                _handler[event.handler].apply($trigger, _getArgs(event));
                return;
            }

            // As last resort we try to call the action by namespace for handlers like humhub.modules.myModule.myAction
            var handlerAction = event.handler.split('.').pop();
            var target = require(string.cutSuffix(event.handler, '.' + handlerAction));

            if(!_executeAction(target, handlerAction, event)) {
                module.log.error('actionHandlerNotFound', event.handler, true);
            }
        } catch(e) {
            module.log.error('error.default', e, true);
            event.finish();
        } finally {
            // Just to get sure the handler is not called twice.
            if(options.originalEvent) {
                options.originalEvent.actionHandled = true;
            }

            if(this.isBlockType($trigger, BLOCK_SYNC)) {
                event.finish();
            }
        }
    };

    var _executeAction = function(target, handlerAction, event) {
        // first try actionMyhandler
        var handlerCapitalized = 'action' + string.capitalize(handlerAction);
        if(object.isFunction(target[handlerCapitalized])) {
            handlerAction = handlerCapitalized;
        }

        if(object.isFunction(target[handlerAction])) {
            // Handler arguments
            target[handlerAction].apply(target, _getArgs(event));
            return true;
        }

        return false;
    };

    var _getArgs = function(event) {
        if(!event.params) {
            return [event];
        }

        if(object.isArray(event.params)) {
            var args = event.params.slice();
            args.unshift(event);
            return args;
        } else {
            return [event, event.params];
        }
    };

    /**
     * Returns the value of data-action-click-<name> over data-action-<name>
     * e.g.:
     * 
     * If the $trigger sets a data-action-click-url and data-action-url and we call
     * 
     * $actioNBinding.data($trigger, 'url');
     * 
     * We'll receive the data-action-click-url.
     * 
     * If no data-action-click-url is set it will return the fallback data-action-url setting.
     * 
     * @param {type} $trigger
     * @param {type} name
     * @param {type} def
     * @returns {mixed}
     */
    ActionBinding.prototype.data = function($trigger, name, def) {
        var result = $trigger.data('action-' + this.eventType + '-' + name);

        if(!object.isDefined(result)) {
            result = $trigger.data('action-' + name);
        }

        return object.isDefined(result) ? result : def;
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
        var defaultBlockType = (this.isSubmit($trigger) || this.getUrl($trigger)) ? BLOCK_ASYNC : BLOCK_SYNC;
        var blockType = this.data($trigger, 'block', defaultBlockType);

        return type === blockType;
    };

    ActionBinding.prototype.isSubmit = function($trigger) {
        return $trigger.is('[type="submit"]') || object.isDefined($trigger.data('action-submit'));
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
        $trigger.data('action-' + this.eventType + '-blocked', true);
    };

    ActionBinding.prototype.unblock = function($trigger) {
        $trigger.data('action-' + this.eventType + '-blocked', false);
    };

    ActionBinding.prototype.createActionEvent = function(options) {
        var $trigger = options.$trigger;
        var event = $.Event(this.eventType);
        event.originalEvent = options.originalEvent;

        var settings = {
            $trigger : $trigger,
            $target: $(this.data($trigger, 'target', $trigger)),
            url: this.data($trigger, 'url'),
            params: this.data($trigger, 'params', {}),
            block: this.data($trigger, 'block'),
            handler: $trigger.data('action' + '-' + this.eventType)
        };

        if(this.isSubmit($trigger)) {
            // Either use closest form or data-action-target if provided
            settings.$form = $(this.data($trigger, 'target', $trigger.closest('form')));
        }

        $.extend(event, settings, options);

        var that = this;
        event.finish = function() {
            _removeLoaderFromEventTarget(event.originalEvent);
            that.unblock($trigger);
        };
        
        event.data = function(key, def) {
            return that.data($trigger, key, def);
        };

        return event;
    };

    var _removeLoaderFromEventTarget = function(evt) {
        if(evt && evt.target) {
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
    var bindAction = function(parent, type, selector, directHandler) {
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

        $parent.off(actionEvent).on(actionEvent, selector, function(evt) {
            evt.preventDefault();
            var $this = $(this);
            
            // Get sure we don't call the handler twice if the event was already handled by the directly attached handler.
            // We have to rebind the handler only if we detect an unbound handler!
            // Note, since jquery object loses data after removed from dom, we also check if the trigger is still in dom, if not we do not execute the action.
            if($this.data('action-' + actionBinding.event) || !$this.closest('body').length ||  (evt.originalEvent && evt.originalEvent.actionHandled)) {
                module.log.debug('Action Handler already executed by direct handler' + actionEvent, actionBinding);
                module.log.debug('Handler event triggered by', $this);
                return;
            }

            module.log.debug('Detected unhandled action', actionBinding);
            updateBindings();
            actionBinding.handle({originalEvent: evt, $trigger: $this});
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
    var trigger = function($trigger, type, options) {
        options.$trigger = $trigger;

        // For manually triggered action events we do not need a block in most cases.
        if(!options.block) {
            options.block = BLOCK_NONE;
        }

        new ActionBinding({
            type: type,
            event: type
        }).handle(options);
    };

    module.export({
        init: init,
        bindAction: bindAction,
        registerHandler: registerHandler,
        Component: Component,
        trigger: trigger,
        getProcessTrigger: getProcessTrigger,
        BLOCK_NONE: BLOCK_NONE,
        BLOCK_SYNC: BLOCK_SYNC,
        BLOCK_ASYNC: BLOCK_ASYNC,
        BLOCK_MANUAL: BLOCK_MANUAL
    });
});