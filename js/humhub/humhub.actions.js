/**
 * Thid module can be used by humhub sub modules for registering handlers and serves as core module for executing actions triggered in the gui.
 * A module can either register global handler by using the registerHandler and registerAjaxHandler functions or use the content mechanism.
 */
humhub.initModule('actions', function (module, require, $) {
    var _handler = {};
    var object = require('util').object;
    var string = require('util').string;
    var client = require('client');
    
    var DATA_COMPONENT = 'action-component';
    var DATA_COMPONENT_SELECTOR  = '[data-'+DATA_COMPONENT+']';
   
    var Component = function(container) {
        if(!container) {
            return;
        }
        this.$ = (object.isString(container)) ? $('#' + container) : container;
        this.base = this.$.data(DATA_COMPONENT);
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
        var $parent = this.$.parent().closest(DATA_COMPONENT_SELECTOR);
        if($parent.length) {
            try {
                var ParentType = require($parent.data(DATA_COMPONENT));
                return new ParentType($parent);
            } catch(err) {
                console.error('Could not instantiate parent component: '+$parent.data(DATA_COMPONENT));
            }
        }
    };
    
    Component.prototype.children = function() {
        var result = [];
        this.$.find(DATA_COMPONENT_SELECTOR).each(function() {
            var component = Component.getInstance($(this));
            if(component) {
                result.push(component);
            }
        });
        return result;
    };
    
    Component.prototype.hasAction = function(action) {
        return this.actions().indexOf(action) >= 0;
    };
    
    Component.prototype.actions = function() {
        return [];
    };
    
    Component.getInstance = function($node) {
        //Determine closest component node (parent or or given node)
        $node = (object.isString($node)) ? $('#'+$node) : $node;
        var $componentRoot = ($node.data(DATA_COMPONENT)) ? $node : Component.getClosestComponentNode($node);
        
        var componentType = $componentRoot.data(DATA_COMPONENT);
        
        var ComponentType = require(componentType);
        if(ComponentType) {
            return new ComponentType($componentRoot);
        } else {
            console.error('Tried to instantiate component with invalid type: '+componentType);
        }
    };
    
    Component.getClosestComponentNode = function($element) {
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
    Component.handleAction = function(event) {
        var component = Component.getInstance(event.$trigger);
        if(component) {
            //Check if the content instance provides this actionhandler
            if(event.handler && component[event.handler]) {
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
        
        //Add addition for loader buttons
        require('additions').registerAddition('[data-action-load-button]', function () {
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
        $parent.on(type+'.humhub-action', selector, function (evt) {
            evt.preventDefault();
            //The element which triggered the action e.g. a button or link
            $trigger = $(this);

            //Get the handler id, either a stand alone handler or a content handler function e.g: 'edit' 
            var handlerId = $trigger.data('action' + '-' + type);
            var event = {type: type, $trigger: $trigger, handler: handlerId};
            
            event.finish = function() {
                _removeLoaderFromEventTarget(evt);
            };
            
            //TODO: handle with $.Event
            //var event = $.Event(type, {$trigger: $trigger});
            //event.originalEvent = evt;
            
            //Search and execute a stand alone handler or try to call the content action handler
            try {
                //Direct action handler
                if(object.isFunction(directHandler)) {
                    directHandler.apply($trigger, [event]);
                    return;
                }
                
                //Component handler
                if(Component.handleAction(event)) {
                    return;
                }
                
                //Registered handler
                if(_handler[handlerId]) {
                    //Registered action handler
                    var handler = _handler[handlerId];
                    handler.apply($trigger, [event]);
                    return;
                }
                
                //As last resort we try to call the action by namespace handler
                var splittedNS = handlerId.split('.');
                var handler = splittedNS[splittedNS.length - 1];
                var target = require(string.cutsuffix(handlerId, '.' + handler));
                if(object.isFunction(target)) {
                    target[handler]({type: type, $trigger: $trigger}); 
                } else {
                    console.error('Could not determine actionhandler for: '+handlerId);
                }
            } catch (e) {
                //TODO: handle error !
                console.error('Error while handling action event for handler "' + handlerId+'"', e);
                _removeLoaderFromEventTarget(evt);
            }
        });
    };

    var _removeLoaderFromEventTarget = function (evt) {
        if (evt.target) {
            $target = $(evt.target);
            $loader = $target.find('.action-loader');

            if ($loader.length) {
                $loader.remove();
            }
        }
    };
    
     module.export({
        Component: Component
    });
});