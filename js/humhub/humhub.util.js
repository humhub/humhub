/**
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
        isEmpty: function(obj) {
            return $.isEmptyObject(obj);
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
            Sub.prototype.super = function(method) {
                if(!Sub._super[arguments[0]]) {
                    throw new Error('Call of undefined method of super type');
                }
                
                var args;
                        
                if(arguments.length > 1){
                    args = [];
                    Array.prototype.push.apply( args, arguments );
                    args.shift();
                }
                return Sub._super[arguments[0]].apply(this, args);
            };
        }
    };
    
    var string = {
        cutprefix : function(val, prefix) {
            if(!this.startsWith(val, prefix)) {
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
});