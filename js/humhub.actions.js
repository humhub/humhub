/**
 * Thid module can be used by humhub sub modules for registering handlers and serves as core module for executing actions triggered in the gui.
 * A module can either register global handler by using the registerHandler and registerAjaxHandler functions or use the content mechanism.
 */
humhub.initModule('actions', function (module, require, $) {
    var _handler = {};
    var object = require('util').object;
    var string = require('util').string;
    var client = require('client');

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
                var path = $(this).data('url-' + event.type) || $(this).data('url');
                client.ajax(path, cfg, event);
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
                if(object.isFunction(directHandler)) {
                    //Direct action handler
                    directHandler.apply($trigger, [event]);
                } else if (_handler[handlerId]) {
                    //Registered action handler
                    var handler = _handler[handlerId];
                    handler.apply($trigger, [event]);
                } else if (!_handler['humhub.modules.content.actiontHandler'](event)) { //Content action handler
                    //If the content handler did not accept this event we try to find a handler by namespace
                    var splittedNS = handlerId.split('.');
                    var handler = splittedNS[splittedNS.length - 1];
                    var target = require(string.cutsuffix(handlerId, '.' + handler));
                    target[handler]({type: type, $trigger: $trigger});
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
});