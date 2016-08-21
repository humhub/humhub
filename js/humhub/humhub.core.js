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
        try {
            module(instance, require, $);
        } catch(err) {
            console.error('Error while creating module: '+id, err);
        }
        
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
     * Config implementation
     */
    
    var config = {
        get : function(module, key, defaultVal) {
            if(arguments.length === 1) {
                return this.getModuleConfig(module);
            } else if(_isDefined(key)) {
                var result = this.getModuleConfig(module)[key];
                return (_isDefined(result)) ? result : defaultVal;
            }
        },
        getModuleConfig: function(module) {
            if(!this[module]) {
                this[module] = {};
            }
            return this[module];
        },

        is : function(module, key, defaultVal) {
            return this.get(module, key, defaultVal) === true;
        },

        set : function(moduleId, key, value) {
            //Moduleid with multiple values
            if(arguments.length === 1) {
                var that = this;
                $.each(moduleId, function(moduleKey, config) {
                    that.set(moduleKey, config);
                });
            }else if(arguments.length === 2) {
                $.extend(this.getModuleConfig(moduleId), key);
            } else if(arguments.length === 3) {
                this.getModuleConfig(moduleId)[key] = value;
            }
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
    
    var _isDefined = function(obj) {
        return typeof obj !== 'undefined';
    };
    
    //Initialize all initial modules
    $(document).ready(function() {
        $.each(initialModules, function(i, module) {
           if(module.init) {
               try {
                    module.init();
               } catch(err) {
                   console.error('Could not initialize module: '+module.id, err);
               }
           } 
           initialized = true;
           console.log('Module initialized: '+module.id);
        });
    });
    
   
    
    return {
        initModule: initModule,
        modules: modules,
        config: config
    };
})($);