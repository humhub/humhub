/**
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
})(humhub.util || {}, $);