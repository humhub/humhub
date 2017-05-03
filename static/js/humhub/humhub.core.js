/**
 * Sets up the humhub namespace and module management.
 * This namespace provides the following functions:
 * 
 * module - for adding modules to this namespace and initializing them
 * 
 * @type @exp;humhub|@call;humhub.core_L4|Function
 */
var humhub = humhub || (function ($) {
    /**
     * Contains the modules namespace e.g. modules.ui.modal
     * @type object
     */
    var modules = {};

    /**
     * Flat array with all registered modules.
     * @type Array
     */
    var moduleArr = [];

    /**
     * Used to collect modules added while initial page load. 
     * These modules will be intitialized after the document is ready.
     * @type Array
     */
    var initialModules = [];

    /**
     * Contains all modules which needs to be reinitialized after a pjax reload
     * @type Array
     */
    var pjaxInitModules = [];

    /**
     * Adds a module to the namespace. And initializes after dom is ready.
     * 
     * The id can be provided either as 
     * 
     * - full namespace humhub.modules.ui.modal
     * - or module.ui.modal
     * - or short ui.modal
     * 
     * Usage:
     * 
     * humhub.module('ui.modal', function(module, require, $) {...});
     * 
     * This would create an empty ui namespace (if not already created before) and 
     * initializes the given module. 
     * 
     * The module can export functions and properties by
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
     * The export function can be called as often as needed (but should be called once at the end of the module).
     * 
     * A module can provide an init function, which is called automatically  after the document is ready.
     * 
     * Dependencies:
     * 
     * The core modules are initialized in a specific order to provide the needed
     * dependencies for each module. The order is given by the order of module calls
     * and in case of core modules configured in the API's AssetBundle. 
     * 
     * A module can be received by using the required function within a module function.
     * You can either depend on a module at initialisation time or within your functions or
     * use the lazy flag of the require function.
     * 
     * Usage:
     * 
     * var modal = require('ui.modal');
     * 
     * or lazy require
     * 
     * var modal = require('ui.modal', true);
     * 
     * @param {type} id the namespaced id
     * @param {type} module
     * @returns {undefined}
     */
    var module = function (id, moduleFunction) {
        //Create module in the namespace and add helper functions
        var instance = resolveNameSpace(id, true);

        // Do not register modules twice!
        if (instance.id) {
            return;
        }

        instance.id = 'humhub.modules.' + _cutModulePrefix(id);
        instance.require = require;
        instance.initOnPjaxLoad = false;
        instance.config = require('config').module(instance);
        instance.isModule = true;

        instance.text = function ($key) {
            var textCfg = instance.config['text'];
            return (textCfg) ? textCfg[$key] : undefined;
        };

        var exportFunc = instance.export = function (exports) {
            $.extend(instance, exports);
        };

        // Setup the module by calling the moduleFunction
        try {
            moduleFunction(instance, require, $);
            // Allows module.export = MyClass for exporting single classes/objects
            if(exportFunc !== instance.export) {
                _setNameSpace(instance.id, instance.export);
            }
        } catch (err) {
            console.error('Error while creating module: ' + id, err);
        }

        moduleArr.push(instance);

        if (instance.init && instance.initOnPjaxLoad) {
            pjaxInitModules.push(instance);
        }

        //Initialize the modules when document is ready
        if(!humhub.initialized) {
            initialModules.push(instance);
        } else { // Init modules added asynchronously (ajax/pjax)
            addModuleLogger(instance);
            initModule(instance);
        }
    };

    /**
     * This function is used to resolve namespaces for receiving module instances
     * or classes.
     * 
     * For the module humhub.modules.ui.modal you can search:
     * 
     * require('ui.modal');
     * require('modules.ui.modal');
     * require('humhub.modules.ui.modal');
     * 
     * @param {type} moduleId
     * @param {boolean} lazy - can be set to require modules which are not yet created.
     * @returns object - the module instance if already initialized else undefined
     * 
     * */
    var require = function (moduleNS, lazy) {
        var module = resolveNameSpace(moduleNS, lazy);
        if (!module) {
            console.error('No module found for namespace: ' + moduleNS);
        }
        return module;
    };

    /**
     * Search the given namespace, and creates the namespace if init = true.
     * 
     * @param {type} typePath the searched module namespace
     * @param {Boolean} init - if set to true, creates namespaces if not already present
     * @returns object - the given module
     */
    var resolveNameSpace = function (typePath, init) {
        try {
            //cut humhub.modules prefix if present
            var moduleSuffix = _cutModulePrefix(typePath);

            //Iterate through the namespace and return the last entry
            var result = modules;
            $.each(moduleSuffix.split('.'), function (i, subPath) {
                if (subPath in result) {
                    result = result[subPath];
                } else if (init) {
                    result = result[subPath] = {};
                } else {
                    result = undefined; //path not found
                    return false; //leave each loop
                }
            });
            return result;
        } catch (e) {
            var log = require('log') || console;
            log.error('Error while resolving namespace: ' + typePath, e);
        }
    };
    
    var _setNameSpace = function (path, obj) {
        try {
            //cut humhub.modules prefix if present
            var moduleSuffix = _cutModulePrefix(path);

            //Iterate through the namespace and return the last entry
            var currentPath = modules;
            var parent, last;
            $.each(moduleSuffix.split('.'), function (i, subPath) {
                if (subPath in currentPath) {
                    last = subPath;
                    parent = currentPath;
                    currentPath = currentPath[subPath];
                } else {
                    return false; //leave each loop
                }
            });
            parent[last] = obj;
        } catch (e) {
            var log = require('log') || console;
            log.error('Error while setting namespace: ' + path, e);
        }
    };

    /**
     * Config implementation
     */
    var config = modules['config'] = {
        id: 'config',
        get: function (module, key, defaultVal) {
            if (arguments.length === 1) {
                return this.module(module);
            } else if (_isDefined(key)) {
                var result = this.module(module)[key];
                return (_isDefined(result)) ? result : defaultVal;
            }
        },
        module: function (module) {
            module = (module.id) ? module.id : module;
            module = _cutModulePrefix(module);
            if (!this[module]) {
                this[module] = {};
            }
            return this[module];
        },
        is: function (module, key, defaultVal) {
            return this.get(module, key, defaultVal) === true;
        },
        set: function (moduleId, key, value) {
            //Moduleid with multiple values
            if (arguments.length === 1) {
                var that = this;
                $.each(moduleId, function (moduleKey, config) {
                    that.set(moduleKey, config);
                });
            } else if (arguments.length === 2) {
                $.extend(this.module(moduleId), key);
            } else if (arguments.length === 3) {
                this.module(moduleId)[key] = value;
            }
        }
    };

    var event = modules['event'] = {
        events: $({}),
        off: function (events, selector, handler) {
            this.events.off(events, selector, handler);
            return this;
        },
        on: function (event, selector, data, handler) {
            this.events.on(event, selector, data, handler);
            return this;
        },
        trigger: function (eventType, extraParameters) {
            this.events.trigger(eventType, extraParameters);
            return this;
        },
        one: function (event, selector, data, handler) {
            this.events.one(event, selector, data, handler);
            return this;
        },
        sub: function (target) {
            target.events = $({});
            target.on = $.proxy(event.on, target);
            target.one = $.proxy(event.one, target);
            target.off = $.proxy(event.off, target);
            target.trigger = $.proxy(event.trigger, target);
            target.triggerCondition = $.proxy(event.triggerCondition, target);
        },
        triggerCondition: function (target, event, extraParameters) {
            var $target;
            /**
             * Supports the following cases:
             * 
             * event.triggerCondition('testevent');
             * event.triggerCondition('testevent', ['asdf']);
             * event.triggerCondition('#test', 'testevent');
             * event.triggerCondition('#test', 'testevent', ['asdf']);
             */
            switch (arguments.length) {
                case 1:
                    $target = this.events;
                    event = target;
                    break;
                case 2:
                    if ($.isArray(event)) {
                        $target = this.events;
                        extraParameters = event;
                    } else {
                        $target = $(target);
                    }
                    break;
                default:
                    $target = $(target);
                    break;
            }

            if (!event) {
                return false;
            }

            var eventObj = $.Event(event);
            $target.trigger(eventObj);
            return eventObj.isDefaultPrevented();
        }
    };

    /**
     * Cuts the prefix humub.modules or modules. from the given value.
     * @param {type} value
     * @returns {unresolved}
     */
    var _cutModulePrefix = function (value) {
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
    var _cutPrefix = function (value, prefix) {
        if (!_startsWith(value, prefix)) {
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
    var _startsWith = function (val, prefix) {
        if (!val || !prefix) {
            return false;
        }
        return val.indexOf(prefix) === 0;
    };

    var _isDefined = function (obj) {
        return typeof obj !== 'undefined';
    };

    var addModuleLogger = function (module, log) {
        log = log || require('log');
        module.log = log.module(module);
    };

    //Initialize all initial modules
    $(document).ready(function () {
        var log = require('log');

        $.each(moduleArr, function (i, module) {
            addModuleLogger(module, log);
        });

        $.each(initialModules, function (i, module) {
            initModule(module);
        });
        
        humhub.initialized = true;
        event.trigger('humhub:ready');
        $(document).trigger('humhub:ready', [false, humhub]);
    });

    var initModule = function (module) {
        var log = require('log');
        event.trigger('humhub:beforeInitModule', module);
        if (module.init) {
            try {
                // compatibility with beta 1.2 beta release
                event.trigger(module.id.replace('.', ':') + ':beforeInit', module);
                
                event.trigger(module.id.replace(/\./g, ':') + ':beforeInit', module);
                module.init();
                event.trigger(module.id.replace(/\./g, ':') + ':afterInit', module);
                
                // compatibility with beta 1.2 beta release
                event.trigger(module.id.replace('.', ':') + ':afterInit', module);
            } catch (err) {
                log.error('Could not initialize module: ' + module.id, err);
            }
        }
        event.trigger('humhub:afterInitModule', module);
        log.debug('Module initialized: ' + module.id);
    };

    // Used to prevent the double initialization of modules loades by pjax.
    var unloaded = [];

    event.on('humhub:modules:client:pjax:success', function (evt) {        
        // Init all modules again which were unloaded in the beforeSend and are configured for pjax initialization.
        // Note: this does not include modules loaded by the pjax request, those are initialized in the module function.
        $.each(pjaxInitModules, function (i, module) {
            if (module.initOnPjaxLoad && unloaded.indexOf(module.id) > -1) {
                module.init(true);
            }
        });
        
        event.trigger('humhub:ready');
        $(document).trigger('humhub:ready', [true, humhub]);
    }).on('humhub:modules:client:pjax:beforeSend', function (evt) {
        unloaded = [];
        $.each(moduleArr, function (i, module) {
            if (module.unload) {
                module.unload();
            }
            unloaded.push(module.id);
        });
    });

    return {
        module: module,
        modules: modules,
        config: config,
        event: event,
        require: require
    };
})(jQuery);