/**
 * Thid module can be used by humhub sub modules for registering handlers and serves as core module for executing actions triggered in the gui.
 * A module can either register global handler by using the registerHandler and registerAjaxHandler functions or use the content mechanism.
 */
humhub.modules = (function (module, $) {
    var _handler = {};
    var object = humhub.util.object;
    var stringUtil = humhub.util.string;
    
    /**
     * Constructor for initializing the module.
     */
    module = function() {
        $eventDelegate = $('[data-event-delegate]').first() || document;
        
        //Binding default action types
        this.bindAction(document, 'click', '[data-action-click]');
        this.bindAction(document, 'dblclick', '[data-action-dblclick]');
        this.bindAction(document, 'change', '[data-action-mouseout]');
        
        /**
         * Handler for handling content actions
         */
        this.registerHandler('humhub.modules.contentHandler', function(event) {
            if(!event.$contentBase) {
                console.error('Tried to register contentHandler with no contentBase handler: "'+ event.handler +
                        '"  type: "'+ event.type +
                        '" $trigger: "'+ $trigger +
                        '" $contentBase: "'+ $contentBase +
                        '" handler: "'+ handlerId +'"');
                return;
            }

            //Initialize a content instance by means of the content-base type and execute the handler
            var ContentType = humhub.modules.resolveType(event.$contentBase.data('content-base'));
            if(ContentType) {
                var content = new ContentType(event.$contentBase);
                if(event.handler && content[event.handler]) {
                    content[event.handler](event);
                }
            } else {
                console.error('No ContentType found for '+event.$contentBase.data('content-base')+ ' event:', event);
            }
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
    module.prototype.registerHandler = function (id, handler) {
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
    module.prototype.registerAjaxHandler = function (id, success, error, cfg) {
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
                var path = $(this).data('url-' + event.type) || $(this).data('url');
                humhub.client.ajax(path, cfg, event);
            };
        }
    };

    /**
     * Binds an wrapper event handler to parent. This is used to detect
     * action handlers like data-action-click events and maps the call to either
     * a contentBase
     * @param {type} parent
     * @param {type} type
     * @param {type} selector
     */
    module.prototype.bindAction = function (parent, type, selector) {
        parent = parent || document;
        var $parent = parent.jquery ? parent : $(parent);
        $parent.on(type, selector, function (evt) {
            evt.preventDefault();
 
            //The element which triggered the action e.g. a button or link
            $trigger = $(this);
            //Get
            var handlerId = $trigger.data('action' + '-' + type);
            
            if(_handler[handlerId]) {
                //Check if global handler was registered
                var handler = _handler[handlerId];
                var event = {type: type, $trigger: $trigger};
                handler.apply($trigger, [event]);
            } else {
                var $contentBase = $trigger.closest('[data-content-base]');
                //Check if event is possibly a content event
                if(!$contentBase) {
                    console.error('No handler and no contentBase was found for '+handlerId);
                    return;
                }
                
                var handler = _handler['humhub.modules.contentHandler'];
                var event = {type: type, $trigger: $trigger, $contentBase: $contentBase, handler: handlerId};
                handler.apply($trigger, [event]);
            }
        });
    };
    
    module.prototype.resolveType = function(typePath) {
        try {
            moduleSuffix = stringUtil.cutprefix(typePath, 'humhub.modules.');
            var result = humhub.modules;
            $.each(moduleSuffix.split('.'), function(index, subPath) {
                result = result[subPath];
            });
            return result;
        } catch(e) {
            //TODO: handle could not resolve type/namespace error
            return;
        }
    };

   
    return new module();
})(humhub.modules || {}, $);