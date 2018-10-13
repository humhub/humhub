/**
 * Util module with sub module for object and string utility functions
 */
humhub.module('util', function(module, require, $) {
    var object = {
        isFunction: function(obj) {
            return $.isFunction(obj);
        },
        isObject: function(obj) {
            return $.isPlainObject(obj);
        },
        isJQuery: function(obj) {
            return this.isDefined(obj) && obj.jquery;
        },
        isArray: function(obj) {
            return $.isArray(obj);
        },
        isEmpty: function(obj) {
            if(!obj) {
                return true;
            }

            if(object.isArray(obj)) {
                return obj.length <= 0;
            }

            return $.isEmptyObject(obj);
        },
        isString: function(obj) {
            return typeof obj === 'string';
        },
        isNumber: function(n) {
            return this.isDefined(n) && !isNaN(parseFloat(n)) && isFinite(n);
        },
        isBoolean: function(obj) {
            return typeof obj === 'boolean';
        },
        defaultValue: function(obj, defaultValue) {
            return object.isDefined(obj) ? obj : defaultValue;
        },
        resolve: function(obj, ns, init) {
            var result = obj;
            $.each(ns.split('.'), function(i, subPath) {
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
        },
        swap: function swap(json){
            var ret = {};
            for(var key in json){
                ret[json[key]] = key;
            }
            return ret;
        },
        isDefined: function(obj) {
            if(arguments.length > 1) {
                var result = true;
                var that = this;
                this.each(arguments, function(index, value) {
                    if(!that.isDefined(value)) {
                        return false;
                    }
                });

                return result;
            }
            return typeof obj !== 'undefined';
        },
        chain: function(thisObj) {
            var handlers = [];
            Array.prototype.push.apply(handlers, arguments);
            handlers.shift();

            return function() {
                var _arguments = arguments;
                handlers.forEach(function(handler) {
                    handler.apply(thisObj, _arguments);
                });
            };
        },
        debounce: function(func, wait, immediate) {
            var timeout;
            return function() {
                var context = this, args = arguments;
                var later = function() {
                    timeout = null;
                    if (!immediate) func.apply(context, args);
                };
                var callNow = immediate && !timeout;
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
                if (callNow) func.apply(context, args);
            };
        },
        inherits: function(Sub, Parent, options) {
            for(var i in Parent) {
                if(!options || !options.excludeFields || options.excludeFields.indexOf(i) < 0) {
                    Sub[i] = Parent[i];
                }
            }

            Sub.prototype = Object.create(Parent.prototype);
            Sub._super = Parent.prototype;
            Sub._superConst = Parent; // Deprecated

            Sub.prototype.static = function(name) {
                var staticField = Sub[name];
                if(object.isFunction(staticField)) {
                    var args;

                    if(arguments.length > 1) {
                        args = [];
                        Array.prototype.push.apply(args, arguments);
                        args.shift();
                    }
                    return staticField.apply(this, args);
                } else {
                    return staticField;
                }
            };

            Sub.prototype.super = function() {
                if(!Sub._super[arguments[0]]) {
                    throw new Error('Call of undefined method of super type: ' + arguments[0]);
                }

                var args;

                if(arguments.length > 1) {
                    args = [];
                    Array.prototype.push.apply(args, arguments);
                    args.shift();
                }
                return Sub._super[arguments[0]].apply(this, args);
            };
        },
        extendable: function(options) {

            if(object.isFunction(options)) {
                options = {init:options};
            }

            var extendableClass = options.init || function() {};

            if(options.name) {
                try {
                    Object.defineProperty(extendableClass, "name", { value: options.name});
                } catch(e) {
                    console.warn('Could not overwrite extendable name property', e);
                }

            }

            extendableClass.extend = function(init, name) {
                if(object.isString(init)) {
                    name = init;
                    init = undefined;
                }

                init = init || function() {
                    extendableClass.apply(this, arguments);
                };

                var Sub = object.extendable({
                    init: init,
                    name: name
                });

                object.inherits(Sub, extendableClass, {excludeFields: ['extend']});

                return Sub;
            };

            return extendableClass;
        }
    };

    var entityMap = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#39;',
        '/': '&#x2F;',
        '`': '&#x60;',
        '=': '&#x3D;'
    };

    var string = {
        escapeHtml: function(string) {
            return String(string).replace(/[&<>"'`=\/]/g, function(s) {
                return entityMap[s];
            });
        },
        capitalize: function(string) {
            return string.charAt(0).toUpperCase() + string.slice(1);
        },
        cutPrefix: function(val, prefix) {
            if(!this.startsWith(val, prefix)) {
                return val;
            }
            return val.substring(prefix.length, val.length);
        },
        cutSuffix: function(val, suffix) {
            return val.slice(0, suffix.length * -1);
        },
        startsWith: function(val, prefix) {
            if(!object.isDefined(val) || !object.isDefined(prefix)) {
                return false;
            }
            return val.indexOf(prefix) === 0;
        },
        endsWith: function(val, suffix) {
            if(!object.isDefined(val) || !object.isDefined(suffix)) {
                return false;
            }
            return val.indexOf(suffix, val.length - suffix.length) !== -1;
        },
        capitalizeFirstLetter: function capitalizeFirstLetter(s) {
            if(!s || !s.length) {
                return s;
            }

            return s.charAt(0).toUpperCase() + s.slice(1);
        },
        lowerCaseFirstLetter: function capitalizeFirstLetter(s) {
            if(!s || !s.length) {
                return s;
            }

            return s.charAt(0).toLowerCase() + s.slice(1);
        },

        /* @deptecated use encode */
        htmlEncode: function(value) {
            return $('<div/>').text(value).html();
        },
        encode: function(value) {
            return $('<div/>').text(value).html();
        },
        /* @deptecated use decode */
        htmlDecode: function(value) {
            return $('<div/>').html(value).text();
        },
        decode: function(value) {
            return $('<div/>').html(value).text();
        },
        template: function(tmpl, config) {
            return tmpl.replace(/{(.*?)}/g, function(match, contents, offset, s) {
                var value = object.resolve(config, contents);
                return object.isDefined(value) ? value : match;
            });
        }
    };

    var array = {
        move: function (arr, old_index, new_index) {
            while (old_index < 0) {
                old_index += arr.length;
            }
            while (new_index < 0) {
                new_index += arr.length;
            }
            if (new_index >= arr.length) {
                var k = new_index - arr.length;
                while ((k--) + 1) {
                    arr.push(undefined);
                }
            }
            arr.splice(new_index, 0, arr.splice(old_index, 1)[0]);
            return arr; // for testing purposes
        }
    };

    module.export({
        object: object,
        string: string,
        array:array
    });
});
