var humhub = humhub || {};;/**
 * Util module with sub module for object and string utility functions
 */
humhub.util = (function (module, $) { 
    module.object = {
        isFunction: function (obj) {
            return this.prototype.toString.call(obj) === '[object Function]';
        },
        isObject: function (obj) {
            return $.isPlainObject(obj);
        },
        isJQuery: function (obj) {
            return obj.jquery;
        },
        isArray: function(obj) {
            return $.isArray(obj);
        },
        isString: function (obj) {
            return typeof obj === 'string';
        },
        isNumber: function (n) {
            return !isNaN(parseFloat(n)) && isFinite(n);
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
    
    module.string = {
        cutprefix : function(val, suffix) {
            return val.substring(suffix.length, val.length);
        },
        startsWith : function(val, prefix) {
            if(!module.object.isDefined(val) || !module.object.isDefined(prefix)) {
                return false;
            }
            return val.indexOf(prefix) === 0;
        },
        endsWith : function(val, suffix) {
            if(!module.object.isDefined(val) || !module.object.isDefined(suffix)) {
                return false;
            }
            return val.indexOf(suffix, val.length - suffix.length) !== -1;
        }
    };
    
    return module;
})(humhub.util || {}, $);;humhub.scripts = (function (module, $) {
    
    var _scripts = [];
    
    $(document).ready(function() {
        $('script[src]').each(function() {
            addScript(cutTimestamp($(this).attr('src')));
        });
    });
    
    var cutTimestamp = function(url) {
        return url.split('?')[0];
    };
    
    var loadOnce = function(urls, sync, callback) {
        urls = $.isArray(urls) ? urls : [urls];
        
        if(sync) {
            return _loadOnceSync(urls, callback);
        }
            
        var promises = [];
        $.each(urls, function(index, scriptUrl) {            
            if(!containsScript(scriptUrl)) {
                addScript(scriptUrl);
                promises.push($.getScript(scriptUrl));
            }
        });
            
        promises.push($.Deferred(function( deferred ){
            $( deferred.resolve );
        }));
            
        return $.when.apply(null, promises);
    };
    
    var _loadOnceSync = function(urls, callback) {
        var deferred = new $.Deferred();
        var promise = deferred.promise();
        $.each(urls, function(index, scriptUrl) {
             if(!containsScript(scriptUrl)) {
                // we need an immediately invoked function expression to capture
                // the current value of the iteration 
                (function(url) {
                    // chaining the promises, 
                    // by assigning the new promise to the variable
                    // and returning a promise from the callback
                    promise = promise.then(function() {
                        addScript(url);
                        return $.getScript(url).done(function(){});
                    });
                }(scriptUrl));
             }
         });
         
        promise.done(function() {
            callback.apply();
        });
        
        promise.fail(function(arg) {
           console.error('Failed loading scripts for: '+arg); 
           //Call callback anyway
           callback.apply();
        });
        
        deferred.resolve();
         
    };
    
    var containsScript = function(url) {
        var result = false;
        url = cutTimestamp(url);
        $.each(_scripts, function(index, scriptUrl) {
            if(scriptUrl === url) {
                result = true;
                return false;
            }
        });
        return result;
    };
    
    var addScript = function(url) {
        url = cutTimestamp(url);
        _scripts.push(url);
    };
    
    return {
        loadOnce: loadOnce,
        containsScript: containsScript,
        addScript:addScript
    };
})(humhub.scripts || {}, $);;/**
 * This can should be used as parent class for all content implementations
 * @type undefined|Function
 */
humhub.additions = (function (module, $) {
    var _additions = {};
    
    var registerAddition = function (selector, addition) {
        if(!_additions[selector]) {
            _additions[selector] = [];
        }
        
        _additions[selector].push(addition);
    };
    
    var applyTo = function(element) {
        var $element = $(element);
        $.each(_additions, function(selector, additions) {
            $.each(additions, function(i, addition) {
                $.each($element.find(selector).addBack(selector), function() {
                    var $match = $(this);
                    addition.apply($match, [$match, $element]);
                });
            });
        });
    }
    
    return {
        registerAddition: registerAddition,
        applyTo: applyTo
    };
})(humhub.additions || {}, $);;humhub.client = (function (module, $) {
    /**
     * Response Wrapper Object for
     * easily accessing common data
     */
    var Response = function (data) {
        this.data = data;
    };

    Response.prototype.isConfirmation = function () {
        return this.data && (this.data.status === 0);
    };

    Response.prototype.isError = function () {
        return this.data && this.data.status && (this.data.status > 0);
    };
    
    Response.prototype.getContent = function () {
        return this.$wrapperContent.children();
    };

    Response.prototype.getErrors = function () {
        return this.data.errors;
    };

    Response.prototype.getErrorCode = function () {
        return this.data.errorCode;
    };

    Response.prototype.toString = function () {
        return "{ status: " + this.data.status + " error: " + this.data.error + " data: " + this.data.data + " }";
    };

    var errorHandler = function (cfg, xhr, type, errorThrown, errorCode, path) {
        errorCode = (xhr) ? xhr.status : parseInt(errorCode);
        console.warn("AjaxError: " + type + " " + errorThrown + " - " + errorCode);

        if (cfg.error && object.isFunction(cfg.error)) {
            // "timeout", "error", "abort", "parsererror" or "application"
            //TODO: den trigger als this verwenden
            cfg.error(errorThrown, errorCode, type);
        } else {
            console.warn('Unhandled ajax error: ' + path + " type" + type + " error: " + errorThrown);
        }
    };

    var ajax = function (path, cfg) {
        var cfg = cfg || {};
        var async = cfg.async || true;
        var dataType = cfg.dataType || "json";
        
        var error = function (xhr, type, errorThrown, errorCode) {
            errorHandler(cfg, xhr, type, errorThrown, errorCode, path);
        };

        var success = function (json) {
            handleResponse(json, function(response) {
                if (response.isError()) { //Application errors
                    return error(undefined, "application", response.getError(), response.getErrorCode());
                } else if (cfg.success) {
                    cfg.success(response);
                }
            });
        };

        $.ajax({
            url: path,
            data : cfg.data,
            type: cfg.type,
            beforeSend:cfg.beforeSend,
            processData: cfg.processData,
            contentType: cfg.contentType,
            async: async,
            dataType: dataType,
            success: success,
            error: error 
        });
    };
    
    var handleResponse = function(json, callback) {
        var response = new Response(json);
        if(json.content) {
            response.$wrapperContent = $('<div id="respone-wrapper-container">'+json.content+'</div>');
            
            //Find all remote scripts and remove them from the partial
            var scriptSrcArr = [];
            response.$wrapperContent.find('script[src]').each(function() {
                scriptSrcArr.push($(this).attr('src'));
                $(this).remove();
            });
            
            //Load the remote scripts synchronously only if they are not already loaded.
            humhub.scripts.loadOnce(scriptSrcArr, true, function() {
                callback(response);
            }); 
        } else {
            callback(response);
        }
    };
    
    return {
        ajax : ajax
    };
})(humhub.client || {}, $);;humhub.ui = (function (module, $) {
    //Init default additions
    humhub.additions.registerAddition('.autosize', function($match) {
        $match.autosize();
    });
    
    return module;
})(humhub.ui || {}, $);;/**
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
})(humhub.modules || {}, $);;/**
 * This can should be used as parent class for all content implementations
 * @type undefined|Function
 */
humhub.modules.content = (function (module, $) {
    
    var Content = module.Content = function(id) {
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
        return this.$.data('content-edit-url');
    };
    
    Content.prototype.edit = function () {
        var modal = humhub.ui.modal;
        var editUrl = this.getEditUrl();
        var contentId = this.getKey();
        
        if(!editUrl || !contentId) {
            //Todo: handle error
            console.error('No editUrl or contentId found for edit content action editUrl: '+editUrl+ ' contentId '+contentId);
            return;
        }
   
        
        humhub.client.ajax(editUrl, {
            data: {
                'id' : contentId
            },
            beforeSend: function() {
                modal.showLoader();
                $('#globalModal').show();
            },
            success: function(response) {
                modal.content(response.getContent());
                //Parse Javascript
                //Show Modal
                //Todo: render edit modal from result
            },
            error: function(err) {
                console.log(err);
                //Todo: handle error
            }
        });
    };
    
    Content.prototype.delete = function () {
        //Search for data-content-delte-url on root.
        //if(this.deleteModal) {open modal bla}
        //Call this url with data-content-pk
        //Trigger delete event
    };
    
    return module;
})(humhub.modules || {}, $);;/**
 * Core module for managing Streams and StreamItems
 * @type Function
 */
humhub.modules.stream = (function (module, $) {

    var ENTRY_ID_SELECTOR_PREFIX = '#wallEntry_';
    var WALLSTREAM_ID = 'wallStream';

    /**
     * Base class for all StreamItems
     * @param {type} id
     * @returns {undefined}
     */
    module.StreamItem = function (id) {
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
    module.StreamItem.prototype.remove = function () {
        this.$.remove();
    };
    
    module.StreamItem.prototype.getContentKey = function () {}
    
    module.StreamItem.prototype.edit = function () {
        //Search for data-content-edit-url on root.
        //Call this url with data-content-pk
        //Trigger delete event
    };
    
    module.StreamItem.prototype.delete = function () {
        //Search for data-content-delte-url on root.
        //Call this url with data-content-pk
        //Trigger delete event
    };

    module.StreamItem.prototype.getContent = function () {
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
    module.Stream = function (id) {
        this.id = id;
        this.$ = $('#' + id);
    };

    module.Stream.prototype.getEntry = function (id) {
        //Search for data-content-base and try to initiate the Item class
        
        return new module.Entry(this.$.find(ENTRY_ID_SELECTOR_PREFIX + id));
    };

    module.Stream.prototype.wallStick = function (url) {
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

    module.Stream.prototype.wallUnstick = function (url) {
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
    module.Stream.prototype.wallArchive = function (id) {

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
    module.Stream.prototype.wallUnarchive = function (id) {
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


    module.getStream = function () { 
        if (!module.mainStream) {
            module.mainStream = new module.Stream(WALLSTREAM_ID);
        }
        return module.mainStream;
    };

    module.getEntry = function (id) {
        return module.getStream().getEntry(id);
    };

    return module;
})(humhub.modules || {}, $);;humhub.ui.modal = (function (module, $) {
    var Modal = function() {};
    
    Modal.prototype.getModal = function() {
        if(!this.$global) {
            this.$global = $('#globalModal');
            this.initModal();
        }
        return this.$global;
    };
    
    Modal.prototype.initModal = function() {
        var that = this;
        this.reset();
        this.$global.on('click', '[data-modal-close]', function() {
            that.close();
        });
    };
    
    Modal.prototype.close = function() {
         this.$global.hide();
         this.$global.html('');
         this.reset();
    };
    
    Modal.prototype.reset = function() {
        this.content('<div class="modal-dialog"><div class="modal-content"><div class="modal-body"><div class="loader"><div class="sk-spinner sk-spinner-three-bounce"><div class="sk-bounce1"></div><div class="sk-bounce2"></div><div class="sk-bounce3"></div></div></div></div></div></div>');
    };
    
    Modal.prototype.showLoader = function() {
        $(".modal-footer .btn").hide();
        $(".modal-footer .loader").removeClass("hidden");
        this.getModal().show();
    };
    
    Modal.prototype.content = function(content) {
        try {
            var that = this;
            console.log('add content modal');
            this.getModal().html(content).promise().always(function() {
                console.log('modal content added');
                humhub.additions.applyTo(that.getModal());
            });
        } catch(err) {
            console.error('Error while setting modal content', err);
            //We try to apply additions anyway
            humhub.additions.applyTo(that.getModal());
        }
    };
    
    return new Modal();
})(humhub.ui.modal || {}, $);