/**
 * Sets up the humhub namespace and module management.
 * This namespace provides the following functions:
 * 
 * initModule - for adding modules to this namespace and initializing them
 * 
 * @type @exp;humhub|@call;humhub.core_L4|Function
 */
var humhub = humhub || (function($) {
    /**
     * Contains all modules by namespace e.g. modules.ui.modal
     * @type object
     */
    var modules = {};
    
    /**
     * Used to collect modules added while initial page load. 
     * These modules will be intitialized after the document is ready.
     * @type Array
     */
    var initialModules = [];
    
    /**
     * Is set wehen document is ready
     * @type Boolean
     */
    var initialized = false;
    
    /**
     * Adds an module to the namespace. And initializes either after dom is ready.
     * The id can be provided either as 
     * 
     * - full namespace humhub.modules.ui.modal
     * - or module.ui.modal
     * - or short ui.modal
     * 
     * Usage:
     * 
     * humhub.initModule('ui.modal', function(module, require, $) {...});
     * 
     * This would create an empty ui namespace (if not already created before) and 
     * initializes the given module. 
     * 
     * The module can export functions ans properties by
     * using either 
     * 
     * module.myFunction = function() {...} 
     * ...
     * 
     * or 
     * 
     * module.export({
     *  myFunction: function() {...},
     *  ...
     * });
     * 
     * The export function can be called as often as needed (but should be called
     * once at the end of the module).
     * A module can provide an init function, which is called automatically 
     * after the document is ready.
     * 
     * Dependencies:
     * 
     * The core modules are initialized in a specific order to provide the needed
     * dependencies for each module. The order is given by the order of initModule calls
     * and in case of core modules configured in the build script. 
     * 
     * A module can be received by using the required function within a module bock.
     * You can either depend on a module at initialisation time or within your functions.
     * 
     * Usage:
     * 
     * var modal = require('ui.modal);
     * 
     * @param {type} id the namespaced id
     * @param {type} module
     * @returns {undefined}
     */
    var initModule = function(id, module) {
        //Create module in the namespace and add helper functions
        var instance = resolveNameSpace(id, true);
        instance.id = 'humhub.modules.'+_cutModulePrefix(id);
        instance.require = require;
        instance.export = function(exports) {
            $.extend(instance, exports);
        };
        
        //Setup the module
        module(instance, require, $);
        
        //Initialize the module when document is ready
        if(!initialized) {
            initialModules.push(instance);
        } else {
            instance.init();
        }
    };
    
    /**
     * Returns a module by its namespace e.g:
     * 
     * For the module humhub.modules.ui.modal you can search:
     * 
     * require('ui.modal');
     * require('modules.ui.modal');
     * require('humhub.modules.ui.modal');
     * 
     * @param {type} moduleId
     * @returns object - the module instance if already initialized else undefined
     * 
     * */
    var require = function(moduleNS) {
        var module = resolveNameSpace(moduleNS);
        if(!module) {
            //TODO: load remote module dependencies
            console.warn('No module found for id: '+moduleNS); 
        }
        return module;
    };
    
    /**
     * Search the given module namespace, and creates the given namespace
     * if init = true.
     * @param {type} typePath the searched module namespace
     * @param {Boolean} init - if set to true, creates namespaces if not already present
     * @returns object - the given module
     */
    var resolveNameSpace = function(typePath, init) {
        try {
            //cut humhub.modules prefix if present
            var moduleSuffix = _cutModulePrefix(typePath);
            
            //Iterate through the namespace and return the last entry
            var result = modules;
            $.each(moduleSuffix.split('.'), function(i, subPath) {
                if(subPath in result) {
                    result = result[subPath];
                } else if(init) {
                    result = result[subPath] = {};
                } else {
                    result = undefined; //path not found
                    return false; //leave each loop
                }
            });
            return result;
        } catch(e) {
            //TODO: handle could not resolve type/namespace error
            return;
        }
    };
    
    /**
     * Cuts the prefix humub.modules or modules. from the given value.
     * @param {type} value
     * @returns {unresolved}
     */
    var _cutModulePrefix = function(value) {
        return _cutPrefix(_cutPrefix(value, 'humhub.'), 'modules.');
    };
    
    /**
     * Cuts a prefix from a string, this is already available in humhub.util but
     * this is not accessible here.
     * 
     * @param {type} value
     * @param {type} prefix
     * @returns {unresolved}
     */
    var _cutPrefix = function(value, prefix) {
        if(!_startsWith(value, prefix)) {
            return value;
        }
        return value.substring(prefix.length, value.length);
    };
    
    /**
     * Checks if a string strats with a given prefix
     * @param {type} val
     * @param {type} prefix
     * @returns {Boolean}
     */
    var _startsWith = function(val, prefix) {
        if(!val || !prefix) {
            return false;
        }
        return val.indexOf(prefix) === 0;
    };
    
    //Initialize all initial modules
    $(document).ready(function() {
        $.each(initialModules, function(i, module) {
           if(module.init) {
               module.init();
           } 
           initialized = true;
           console.log('Module initialized: '+module.id);
        });
    });
    
    return {
        initModule: initModule
    };
})($);;/**
 * Util module with sub module for object and string utility functions
 */
humhub.initModule('util', function(module, require, $) {
    var object = {
        isFunction: function (obj) {
            return $.isFunction(obj);
        },
        isObject: function (obj) {
            return $.isPlainObject(obj);
        },
        isJQuery: function (obj) {
            return this.isDefined(obj) && obj.jquery;
        },
        isArray: function(obj) {
            return $.isArray(obj);
        },
        isString: function (obj) {
            return typeof obj === 'string';
        },
        isNumber: function (n) {
            return this.isDefined(n) && !isNaN(parseFloat(n)) && isFinite(n);
        },
        isBoolean: function (obj) {
            return typeof obj === 'boolean';
        },
        isDefined: function (obj) {
            if (arguments.length > 1) {
                var result = true;
                var that = this;
                this.each(arguments, function (index, value) {
                    if (!that.isDefined(value)) {
                        return false;
                    }
                });

                return result;
            }
            return typeof obj !== 'undefined';
        },
        inherits: function(Sub, Parent) {
            Sub.prototype = Object.create(Parent.prototype);
            Sub._super = Parent.prototype;
        }
    };
    
    var string = {
        cutprefix : function(val, prefix) {
            if(!this.startsWith(prefix)) {
                return val;
            }
            return val.substring(prefix.length, val.length);
        },
        cutsuffix : function(val, suffix) {
            return val.slice(0, suffix.length * -1);
        },
        startsWith : function(val, prefix) {
            if(!object.isDefined(val) || !object.isDefined(prefix)) {
                return false;
            }
            return val.indexOf(prefix) === 0;
        },
        endsWith : function(val, suffix) {
            if(!object.isDefined(val) || !object.isDefined(suffix)) {
                return false;
            }
            return val.indexOf(suffix, val.length - suffix.length) !== -1;
        }
    };
    
    module.export({
        object: object,
        string: string
    });
});;/**
 * This module manages UI-Additions registered by other modules. Additions can be applied to DOM elements 
 * and are used to add a specific behaviour or manipulate the element itself. e.g: Richtext, Autosize input...
 * 
 * An addition can be registered for a specific selector e.g: <input data-addition-richtext ... />
 * It is possible to register multiple additions for the same selector.
 */
humhub.initModule('additions', function(module, require, $) {
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
        if(!_additions[selector]) {
            _additions[selector] = [];
        }
        
        _additions[selector].push(addition);
    };
    
    /**
     * Applies all matched additions to the given element and its children
     * @param {type} element
     * @returns {undefined}
     */
    module.applyTo = function(element) {
        var $element = $(element);
        $.each(_additions, function(selector, additions) {
            $.each(additions, function(i, addition) {
                $.each($element.find(selector).addBack(selector), function() {
                    try {
                        var $match = $(this);
                        addition.apply($match, [$match, $element]);
                    } catch(e) {
                        console.error('Error while applying addition '+addition+' on selector '+selector);
                    }
                });
            });
        });
    };
    
    module.init = function() {
        //TODO: apply to html on startup, the problem is this could crash legacy code.
    };
});;/**
 * Manages the client/server communication. Handles humhub json api responses and
 * pjax requests.
 */
humhub.initModule('client', function (module, require, $) {
    var object = require('util').object;
    var scripts = require('scripts');
    
    var init = function() {
        /*$.ajaxPrefilter('html', function(options, originalOptions, jqXHR) {
            debugger;
            console.log(options);
            var pjaxHandler = options.success;
            options.success = function(result, textStatus, xhr) {
                console.log(result);
                pjaxHandler(result, textStatus, xhr);
            };
            options.error = function(err) {
                debugger;
            };
        });
        
        ///TEESSS 
        $.pjax.defaults.maxCacheLength = 0;
        $('a.dashboard').on('click', function(evt) {
            debugger;
            evt.preventDefault();
            $.pjax({url:$(this).attr('href'), container: '#main-content', maxCacheLength:0, timeout:2000});
        });*/
    }
    /**
     * Response Wrapper Object for easily accessing common data
     */
    var Response = function (data) {
        this.data = data;
    };

    /**
     * Checks if the response is a confirmation of the request
     * @returns {Boolean}
     */
    Response.prototype.isConfirmation = function () {
        return this.getStatus() === 0;
    };

    //TODO: isValidationError status 2 

    /**
     * Checks if the response is marke
     * @returns {humhub.client_L5.Response.data.status|Boolean}
     */
    Response.prototype.isError = function () {
        return this.getStatus() > 0 || this.getErrors().length;
    };

    Response.prototype.getStatus = function () {
        return (this.data && object.isDefined(this.data.status)) ? this.data.status : -1;
    };
    
    Response.prototype.getErrorTitle = function() {
        return (this.data) ? this.data.errorTitle : undefined;
    };
    
    Response.prototype.getFirstError = function() {
        var errors = this.getErrors();
        if(errors.length) {
            return errors[0];
        }
    };
    
    Response.prototype.setAjaxError = function(xhr, errorThrown, textStatus,data , status) {
        this.xhr = xhr;
        this.textStatus = textStatus;
        this.data = data || {};
        this.data.status = status || xhr.status;
        this.data.errors = [errorThrown];
    };

    /**
     * Returns an array of errors or an empty array so getErrors().length is always
     * safe.
     * @returns {array} error array or empty array
     */
    Response.prototype.getErrors = function () {
        if (this.data) {
            var errors = this.data.errors || [];
            return (object.isString(errors)) ? [errors] : errors;
        }
        return [];
    };

    /**
     * Returns the raw content object. The content object can either be an
     * object with multiple partials {partialId: content string} or a single content string.
     * @param {type} id
     * @returns {undefined|humhub.client_L5.Response.data.content}1
     */
    Response.prototype.getContent = function () {
        return this.data.content;
    };

    /**
     * Returns the response partial. If no id is given we return the first partial
     * we find.
     * @returns {humhub.client_L5.Response.data.content}
     */
    Response.prototype.getPartial = function (id) {
        if (!this.data) {
            return;
        }
        //TODO: handleResponse filter...
        if (object.isObject(this.data.content)) {
            return (id) ? this.data.content[id] : this.data.content;
        } else if (!id) {
            return this.data.content;
        }

        return;
    };

    Response.prototype.toString = function () {
        return "{ status: " + this.getStatus() + " error: " + this.getErrors() + " data: " + this.getContent() + " }";
    };

    var submit = function ($form, cfg) {
        var cfg = cfg || {};
        $form = object.isString($form) ? $($form) : $form;
        cfg.type = $form.attr('method') || 'post';
        cfg.data = $form.serialize()
        ajax($form.attr('action'), cfg);
    };

    var ajax = function (path, cfg) {
        var cfg = cfg || {};
        var async = cfg.async || true;
        var dataType = cfg.dataType || "json";

        var error = function (xhr, textStatus, errorThrown, data, status) {
            //Textstatus = "timeout", "error", "abort", "parsererror", "application"
            if (cfg.error && object.isFunction(cfg.error)) {
                var response = new Response();
                response.setAjaxError(xhr, errorThrown, textStatus, data, status);
                cfg.error(response);
            } else {
                console.warn('Unhandled ajax error: ' + path + " type" + type + " error: " + errorThrown);
            }
        };

        var success = function (json, textStatus, xhr) {
            var response = new Response(json);
            if (response.isError()) { //Application errors
                return error(xhr, "application", response.getErrors(), json, response.getStatus() );
            } else if (cfg.success) {
                response.textStatus = textStatus;
                response.xhr = xhr;
                cfg.success(response);
            }
        };

        $.ajax({
            url: path,
            data: cfg.data,
            type: cfg.type,
            beforeSend: cfg.beforeSend,
            processData: cfg.processData,
            contentType: cfg.contentType,
            async: async,
            dataType: dataType,
            success: success,
            error: error
        });
    };

    module.export({
        ajax: ajax,
        submit: submit,
        init: init
    });
});

/**
 * 
        var handleResponse = function (json, callback) {
            var response = new Response(json);
            if (json.content) {
                response.$content = $('<div>' + json.content + '</div>');

                //Find all remote scripts and remove them from the partial
                var scriptSrcArr = [];
                response.$content.find('script[src]').each(function () {
                    scriptSrcArr.push($(this).attr('src'));
                    $(this).remove();
                });

                //Load the remote scripts synchronously only if they are not already loaded.
                scripts.loadOnceSync(scriptSrcArr, function () {
                    callback(response);
                });
            } else {
                callback(response);
            }
        };
 */;humhub.initModule('ui', function(module, require, $) {
    var additions = require('additions');
    module.init = function() {
        additions.registerAddition('.autosize', function($match) {
            $match.autosize();
        });
    }
});;/**
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
});;/**
 * This module provides an api for handling content objects e.g. Posts, Polls...
 *
 * @type undefined|Function
 */

humhub.initModule('content', function(module, require, $) {
    var client = require('client');
    var object = require('util').object;
    var actions = require('actions');
    
    module.init = function() {
        actions.registerHandler('humhub.modules.content.actiontHandler', function(event) {
            return module.handleAction(event);
        });
    };
    
    /**
     * Handles the given contentAction event. The event should provide the following properties:
     * 
     *  $trigger (required) : the trigger node of the event
     *  handler (required)  : the handler functionn name to be executed on the content
     *  type (optoinal)     : the event type 'click', 'change',...
     * 
     * @param {object} event - event object
     * @returns {Boolean} true if the contentAction could be executed else false
     */
    module.handleAction = function(event) {
        var $contentBase = this.getContentBase(event.$trigger);
        if($contentBase.length) {
            //Initialize a content instance by means of the content-base type and execute the handler
            var ContentType = require($contentBase.data('content-base'));
            if(ContentType) {
                var content = new ContentType($contentBase);
                if(event.handler && content[event.handler]) {
                    content[event.handler](event);
                    return true;
                }
            } else {
                console.error('No ContentType found for '+$contentBase.data('content-base'));
            }
        }
        return false;
    };
    
    module.getContentBase = function($element) {
        return $element.closest('[data-content-base]');
    };
    
    var Content = function(id) {
        if(!id) { //Create content
            return;
        }
        if (typeof id === 'string') {
            this.id = id;
            this.$ = $('#' + id);
        } else if (id.jquery) {
            this.$ = id;
            this.id = this.$.attr('id');
        }
    };
    
    Content.prototype.getKey = function () {
        return this.$.data('content-key');
    };
    
    Content.prototype.getEditUrl = function () {
        var result = this.$.data('content-edit-url');
        if(!result) {
            var parentContent = this.getParentContentBase('[data-content-base]');
            if(parentContent) {
                return parentContent.getEditUrl();
            }
        }
        return result;
    };
    
    Content.prototype.getParentContentBase = function() {
        var $parent = this.$.parent().closest('[data-content-base]');
        if($parent.length) {
            try {
                var ParentType = require($parent.data('content-base'));
                return new ParentType($parent);
            } catch(err) {
                console.error('Could not instantiate parent content base: '+$parent.data('content-base'));
            }
        }
    };
    
    Content.prototype.create = function (addContentHandler) {
        //Note that this Content won't have an id, so the backend will create an instance
        this.edit(addContentHandler);
    };
    
    Content.prototype.edit = function (successHandler) {
        var editUrl = this.getEditUrl();
        var contentId = this.getKey();
        var modal = require('ui.modal').global;
        
        if(!editUrl) {
            //Todo: handle error
            console.error('No editUrl found for edit content action editUrl: '+editUrl+ ' contentId '+contentId);
            return;
        }
   
        var that = this;
        
        client.ajax(editUrl, {
            data: {
                'id' : contentId
            },
            beforeSend: function() {
                modal.loader();
            },
            success: function(response) {
                //Successfully retrieved the edit form, now show it within a modal
                modal.content(response.getContent(), function() {
                    //Bind direct action handler we could use a global registeredHandler but this is more efficient
                    actions.bindAction(modal.getBody(), 'click', '[data-content-save]', function(event) {
                        client.submit(modal.getForm(), {
                            success : function(response) {
                                if(object.isFunction(successHandler)) {
                                    if(successHandler(response, modal)) {modal.close();};
                                } else {
                                    that.replaceContent(response.getContent());
                                    //TODO: check for content.highlight
                                    modal.close();
                                } 
                                event.finish();
                            },
                            error : function(error) {
                                //TODO: handle error
                                modal.error(error);
                                console.error('Error while submitting form :'+error);
                                event.finish();
                            }
                        });
                    });
                });
            },
            error: function(errResponse) {
                modal.error(errResponse);
                console.log('Error occured while editing content: '+errResponse.getFirstError());
                //Todo: handle error
            }
        });
    };
    
    Content.prototype.replaceContent = function(content) {
        try {
            this.$.html($(content).children());
        } catch(e) {
            console.error('Error occured while replacing content: '+this.id , e);
        }
    };
    
    Content.prototype.delete = function () {
        //Search for data-content-delte-url on root.
        //if(this.deleteModal) {open modal bla}
        //Call this url with data-content-pk
        //Trigger delete event
    };
    
    module.Content = Content;
});;/**
 * Core module for managing Streams and StreamItems
 * @type Function
 */
humhub.initModule('stream', function(module, require, $) {

    var ENTRY_ID_SELECTOR_PREFIX = '#wallEntry_';
    var WALLSTREAM_ID = 'wallStream';

    /**
     * Base class for all StreamItems
     * @param {type} id
     * @returns {undefined}
     */
    var StreamItem = function (id) {
        if (typeof id === 'string') {
            this.id = id;
            this.$ = $('#' + id);
        } else if (id.jquery) {
            this.$ = id;
            this.id = this.$.attr('id');
        }
    };

    /**
     * Removes the stream item from stream
     */
    StreamItem.prototype.remove = function () {
        this.$.remove();
    };
    
    StreamItem.prototype.getContentKey = function () {}
    
    StreamItem.prototype.edit = function () {
        //Search for data-content-edit-url on root.
        //Call this url with data-content-pk
        //Trigger delete event
    };
    
    StreamItem.prototype.delete = function () {
        //Search for data-content-delte-url on root.
        //Call this url with data-content-pk
        //Trigger delete event
    };

    StreamItem.prototype.getContent = function () {
        return this.$.find('.content');
    };

/*
    module.StreamItem.prototype.highlightContent = function () {
        var $content = this.getContent();
        $content.addClass('highlight');
        $content.delay(200).animate({backgroundColor: 'transparent'}, 1000, function () {
            $content.removeClass('highlight');
            $content.css('backgroundColor', '');
        });
    };
*/
    /**
     * Stream implementation
     * @param {type} id
     * @returns {undefined}
     */
    var Stream = function (id) {
        this.id = id;
        this.$ = $('#' + id);
    };

    Stream.prototype.getEntry = function (id) {
        //Search for data-content-base and try to initiate the Item class
        
        return new module.Entry(this.$.find(ENTRY_ID_SELECTOR_PREFIX + id));
    };

    Stream.prototype.wallStick = function (url) {
        $.ajax({
            dataType: "json",
            type: 'post',
            url: url
        }).done(function (data) {
            if (data.success) {
                if (currentStream) {
                    $.each(data.wallEntryIds, function (k, wallEntryId) {
                        currentStream.deleteEntry(wallEntryId);
                        currentStream.prependEntry(wallEntryId);
                    });
                    $('html, body').animate({scrollTop: 0}, 'slow');
                }
            } else {
                alert(data.errorMessage);
            }
        });
    };

    Stream.prototype.wallUnstick = function (url) {
        $.ajax({
            dataType: "json",
            type: 'post',
            url: url
        }).done(function (data) {
            if (data.success) {
                //Reload the whole stream, since we have to reorder the entries
                currentStream.showStream();
            }
        });
    };

    /**
     * Click Handler for Archive Link of Wall Posts
     * (archiveLink.php)
     * 
     * @param {type} className
     * @param {type} id
     */
    Stream.prototype.wallArchive = function (id) {

        url = wallArchiveLinkUrl.replace('-id-', id);

        $.ajax({
            dataType: "json",
            type: 'post',
            url: url
        }).done(function (data) {
            if (data.success) {
                if (currentStream) {
                    $.each(data.wallEntryIds, function (k, wallEntryId) {
                        //currentStream.reloadWallEntry(wallEntryId);
                        // fade out post
                        setInterval(fadeOut(), 1000);

                        function fadeOut() {
                            // fade out current archived post
                            $('#wallEntry_' + wallEntryId).fadeOut('slow');
                        }
                    });
                }
            }
        });
    };


    /**
     * Click Handler for Un Archive Link of Wall Posts
     * (archiveLink.php)
     * 
     * @param {type} className
     * @param {type} id
     */
    Stream.prototype.wallUnarchive = function (id) {
        url = wallUnarchiveLinkUrl.replace('-id-', id);

        $.ajax({
            dataType: "json",
            type: 'post',
            url: url
        }).done(function (data) {
            if (data.success) {
                if (currentStream) {
                    $.each(data.wallEntryIds, function (k, wallEntryId) {
                        currentStream.reloadWallEntry(wallEntryId);
                    });

                }
            }
        });
    };
    
    var getStream = function () { 
        if (!module.mainStream) {
            module.mainStream = new module.Stream(WALLSTREAM_ID);
        }
        return module.mainStream;
    };

    var getEntry = function (id) {
        return module.getStream().getEntry(id);
    };
    
    module.export({
       getStream : getStream,
       getEntry : getEntry
    });
});;/**
 * Module for creating an manipulating modal dialoges.
 * Normal layout of a dialog:
 * 
 * <div class="modal">
 *     <div class="modal-dialog">
 *         <div class="modal-content">
 *             <div class="modal-header"></div>
 *             <div class="modal-body"></div>
 *             <div class="modal-footer"></div>
 *         </div>
 *     </div>
 * </div>
 *  
 * @param {type} param1
 * @param {type} param2
 */
humhub.initModule('ui.modal', function (module, require, $) {
    var additions = require('additions');
    var actions = require('actions');

    //Keeps track of all initialized modals
    var modals = [];

    var TMPL_MODAL_CONTAINER = '<div class="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none; background:rgba(0,0,0,0.1)"><div class="modal-dialog"><div class="modal-content"></div></div></div>';
    var TMPL_MODAL_HEADER = '<div class="modal-header"><button type="button" class="close" data-modal-close="true" aria-hidden="true">×</button><h4 class="modal-title"></h4></div>';
    var TMPL_MODAL_BODY = '<div class="modal-body"></div>';
    var ERROR_DEFAULT_TITLE = 'Error';
    var ERROR_DEFAULT_MESSAGE = 'An unknown error occured!';

    /**
     * The Modal class can be used to create new modals or manipulate existing modals.
     * If the constructor finds an element with the given id we use the existing modal,
     * if the id is not already used, we create a new modal dom element.
     * 
     * @param {string} id - id of the modal
     */
    var Modal = function (id) {
        this.$modal = $('#' + id);
        if (!this.$modal.lengh) {
            this.createModal(id);
        }
        this.initModal();
        modals.push(this);
    };

    /**
     * Creates a new modal dom skeleton.
     * @param {type} id the modal id
     * @returns {undefined}
     */
    Modal.prototype.createModal = function (id) {
        this.$modal = $(TMPL_MODAL_CONTAINER).attr('id', id);
        $('body').append(this.$modal);
    };

    /**
     * Initializes default modal events and sets initial data.
     * @returns {undefined}
     */
    Modal.prototype.initModal = function () {
        //Set the loader as default content
        this.reset();
        var that = this;

        //Set default modal manipulation event handlers
        this.getDialog().on('click', '[data-modal-close]', function () {
            that.close();
        }).on('click', '[data-modal-clear-error]', function () {
            that.clearErrorMessage();
        }).on('click', function (evt) {
            //We don't want to bubble this event to the modal root
            evt.stopPropagation();
        });

        //A click on modal root (background) will close the modal
        this.$modal.on('click', function () {
            that.close();
        });
    };

    /**
     * Closes the modal with fade animation and sets the loader content
     * @returns {undefined}
     */
    Modal.prototype.close = function () {
        var that = this;
        this.$modal.fadeOut('fast', function () {
            that.getContent().html('');
            that.reset();
        });
    };

    /**
     * Sets the loader content and shows the modal
     * @returns {undefined}
     */
    Modal.prototype.loader = function () {
        this.reset();
        this.show();
    };

    /**
     * Sets the default content (a loader animation)
     * @returns {undefined}
     */
    Modal.prototype.reset = function () {
        this.content('<div class="modal-body"><div class="loader"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div>');
        this.isFilled = false;
    };

    /**
     * Sets the given content and applies content additions.
     * @param {string|jQuery} content - content to be set
     * @param {function} callback - callback function is called after html was inserted
     * @returns {undefined}
     */
    Modal.prototype.content = function (content, callback) {
        try {
            var that = this;
            this.clearErrorMessage();
            this.getContent().html(content).promise().always(function () {
                additions.applyTo(that.getContent());
                !callback || callback(this.$modal);
            });
            this.isFilled = true;
        } catch (err) {
            console.error('Error while setting modal content', err);
            this.setErrorMessage(err.message);
            //We try to apply additions anyway
            additions.applyTo(that.$modal);
        }
    };

    /**
     * Sets an errormessage and title. This function either creates an standalone
     * error modal with title and message, or adds/replaces a errorboxmessage to
     * already exising and filled modals.
     * @param {type} title
     * @param {type} message
     * @returns {undefined}
     */
    Modal.prototype.error = function (title, message) {

        if (arguments.length === 1 && title) {
            message = (title.getFirstError) ? title.getFirstError() : title;
            title = (title.getErrorTitle) ? title.getErrorTitle() : ERROR_DEFAULT_TITLE;
        }

        title = title || ERROR_DEFAULT_TITLE;
        message = message || ERROR_DEFAULT_MESSAGE;

        //If there is no content yet we create an error only content
        if (!this.isFilled) {
            this.clear();
            this.setTitle(title);
            this.setBody('');
            this.setErrorMessage(message);
            this.$modal.show();
        } else {
            //TODO: allow to set errorMessage and title even for inline messages
            this.setErrorMessage(message);
        }
    };

    /**
     * Removes existing error messages
     * @returns {undefined}
     */
    Modal.prototype.clearErrorMessage = function () {
        var modalError = this.getErrorMessage();
        if (modalError.length) {
            modalError.fadeOut('fast', function () {
                modalError.remove();
            });
        }
    };

    /**
     * Adds or replaces an errormessagebox
     * @param {type} message
     * @returns {undefined}
     */
    Modal.prototype.setErrorMessage = function (message) {
        var $errorMessage = this.getErrorMessage();
        if ($errorMessage.length) {
            $errorMessage.css('opacity', 0);
            $errorMessage.text(message);
            $errorMessage.animate({'opacity': 1}, 'fast');
        } else {
            this.getBody().prepend('<div class="modal-error alert alert-danger">' + message + '</div>');
        }
    };

    /**
     * Returns the current errormessagebox
     * @returns {humhub.ui.modal_L18.Modal.prototype@call;getContent@call;find}
     */
    Modal.prototype.getErrorMessage = function () {
        return this.getContent().find('.modal-error');
    };

    /**
     * Shows the modal
     * @returns {undefined}
     */
    Modal.prototype.show = function () {
        this.$modal.show();
    };

    /**
     * Clears the modal content
     * @returns {undefined}
     */
    Modal.prototype.clear = function () {
        this.getContent().empty();
    };

    /**
     * Retrieves the modal content jQuery representation
     * @returns {humhub.ui.modal_L18.Modal.prototype@pro;$modal@call;find}
     */
    Modal.prototype.getContent = function () {
        //We use the :first selector since jQuery refused to execute javascript if we set content with inline js
        return this.$modal.find('.modal-content:first');
    };

    /**
     * Retrieves the modal dialog jQuery representation
     * @returns {humhub.ui.modal_L18.Modal.prototype@pro;$modal@call;find}
     */
    Modal.prototype.getDialog = function () {
        return this.$modal.find('.modal-dialog');
    };

    /**
     * Searches for forms within the modal
     * @returns {humhub.ui.modal_L18.Modal.prototype@pro;$modal@call;find}
     */
    Modal.prototype.getForm = function () {
        return this.$modal.find('form');
    };

    /**
     * Adds or replaces a modal-title with close button and a title text.
     * @param {type} title
     * @returns {undefined}
     */
    Modal.prototype.setTitle = function (title) {
        var $header = this.getHeader();
        if (!$header.length) {
            this.getContent().prepend($(TMPL_MODAL_HEADER));
            $header = this.getHeader();
        }
        $header.find('.modal-title').html(title);
    };

    /**
     * Adds or replaces the current modal-body
     * @param {type} content
     * @returns {undefined}
     */
    Modal.prototype.setBody = function (content) {
        var $body = this.getBody();
        if (!$body.length) {
            this.getContent().append($(TMPL_MODAL_BODY));
            $body = this.getBody();
        }
        $body.html(content);
    };

    /**
     * Retrieves the modal-header element
     * @returns {humhub.ui.modal_L18.Modal.prototype@pro;$modal@call;find}
     */
    Modal.prototype.getHeader = function () {
        return this.$modal.find('.modal-header');
    };

    /**
     * Retrieves the modal-body element
     * @returns {humhub.ui.modal_L18.Modal.prototype@pro;$modal@call;find}
     */
    Modal.prototype.getBody = function () {
        return this.$modal.find('.modal-body');
    };
    
    module.export({
        init: function () {
            module.global = new Modal('global-modal');
        },
        Modal: Modal
    });
});