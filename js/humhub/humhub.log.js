/**
 *  
 * @param {type} param1
 * @param {type} param2
 */
humhub.initModule('log', function (module, require, $) {

    var event = require('event');

    var TRACE_TRACE = 0;
    var TRACE_DEBUG = 1;
    var TRACE_INFO = 2;
    var TRACE_SUCCESS = 3;
    var TRACE_WARN = 4;
    var TRACE_ERROR = 5;
    var TRACE_FATAL = 6;
    var TRACE_OFF = 7;

    var traceLevels = ['TRACE', 'DEBUG', 'INFO', 'SUCCESS', 'WARN', 'ERROR', 'FATAL', 'OFF'];
    var config = require('config').module(module);
    var object = require('util').object;

    var logger = {};

    var Logger = function (module) {
        if(object.isString(module)) {
            this.module = require('module');
            this.moduleId = module;
        } else if(module){
            this.module = module;
            this.moduleId = module.id;
        }
        this.update();
    };

    Logger.prototype.update = function () {
        var result;
        if (this.moduleId) {
            var moduleConfig = require('config').module(this.moduleId);
            if (moduleConfig.traceLevel && traceLevels.indexOf(moduleConfig.traceLevel.toUpperCase()) >= 0) {
                result = traceLevels.indexOf(moduleConfig.traceLevel.toUpperCase());
            }
        }

        if (!result) {
            result = config.traceLevel || TRACE_INFO;
        }

        return this.traceLevel = result;
    };

    Logger.prototype.trace = function (msg,details, setStatus) {
        this._log(msg, details, setStatus, TRACE_TRACE);
    };

    Logger.prototype.debug = function (msg, details, setStatus) {
        this._log(msg, details, setStatus, TRACE_DEBUG);
    };

    Logger.prototype.info = function (msg, details, setStatus) {
        this._log(msg, details, setStatus, TRACE_INFO);
    };

    Logger.prototype.success = function (msg, setStatus) {
        setStatus = object.isDefined(setStatus) ? setStatus : true;
        this._log(msg, undefined, setStatus, TRACE_SUCCESS);
    };

    Logger.prototype.warn = function (msg, error, setStatus) {
        this._log(msg, error, setStatus, TRACE_WARN);
    };

    Logger.prototype.error = function (msg, error, setStatus) {
        this._log(msg, error, setStatus, TRACE_ERROR);
    };

    Logger.prototype.fatal = function (msg, error, setStatus) {
        this._log(msg, error, setStatus, TRACE_FATAL);
    };

    Logger.prototype._log = function (msg, details, setStatus, level) {
        try {
            if (object.isBoolean(details)) {
                setStatus = details;
                details = undefined;
            }

            if(msg instanceof Error && level >= TRACE_WARN) {
                details = msg;
                msg = this.getMessage(details.message, true);
            } else  if(object.isObject(msg) && msg.status && level >= TRACE_WARN) {
                details = msg;
                msg = this.getMessage(msg.status, true);
            } else if(!object.isObject(msg)) {
                msg = this.getMessage(msg, (!object.isDefined(msg) && level >= TRACE_WARN));
            }

            if (this.traceLevel > level) {
                return;
            }

            this._consoleLog(msg, level, details);
            
            if (setStatus) {
                event.trigger('humhub:modules:log:setStatus', [msg, details, level]);
            }
        } catch(e) {
            console.error('Error while generating log', e);
        }
    };

    Logger.prototype.getMessage = function (key, returnDefault) {
        if(!object.isString(key)) {
            return key;
        }
        
        var result;
        
        if(this.module) {
            result = this.module.text(key);
        }
        
        if(!result) {
            result = module.text(key);
        }
        
        if(!result && returnDefault) {
            result = module.text('default.error');
        } else if(!result){
            result = key;
        }
        
        return result;
    };

    Logger.prototype._consoleLog = function (msg, level, details) {
        if (window.console) {
            var consoleMsg = this.moduleId || 'root';
            consoleMsg += '(' + traceLevels[level] + '): ' + msg;
            switch (level) {
                case TRACE_ERROR:
                case TRACE_FATAL:
                    console.error(consoleMsg, details);
                    break;
                case TRACE_WARN:
                    if (details) {
                        console.warn(consoleMsg, details);
                    } else {
                        console.warn(consoleMsg);
                    }
                    break;
                default:
                    if (details) {
                        console.log(consoleMsg, details);
                    } else {
                        console.log(consoleMsg);
                    }
                    break;
            }
        }
    };

    var init = function () {
        module.rootLogger = new Logger();
    };

    var getRootLogger = function () {
        if (!module.rootLogger) {
            module.rootLogger = new Logger();
        }
        return module.rootLogger;
    };

    var getModuleLogger = function (module) {
        var moduleId = (object.isString(module)) ? module : module.id;
        if (!logger[moduleId]) {
            logger[moduleId] = new Logger(module);
        }
        return logger[moduleId];
    };

    var trace = function (msg, details, setStatus) {
        module.getRootLogger().trace(msg, details, setStatus);
    };

    var debug = function (msg, details, setStatus) {
        module.getRootLogger().debug(msg, details, setStatus);
    };
    
    var success = function (msg, details, setStatus) {
        module.getRootLogger().success(msg, details, setStatus);
    };

    var info = function (msg, details, setStatus) {
        module.getRootLogger().info(msg, details, setStatus);
    };

    var warn = function (msg, error, setStatus) {
        module.getRootLogger().warn(msg, error, setStatus);
    };

    var error = function (msg, error, setStatus) {
        module.getRootLogger().error(msg, error, setStatus);
    };

    var fatal = function (msg, error, setStatus) {
        module.getRootLogger().fatal(msg, error, setStatus);
    };

    module.export({
        init: init,
        Logger: Logger,
        module: getModuleLogger,
        getRootLogger: getRootLogger,
        trace: trace,
        debug: debug,
        info: info,
        success: success,
        warn: warn,
        error: error,
        fata: fatal,
        TRACE_TRACE: TRACE_TRACE,
        TRACE_DEBUG: TRACE_DEBUG,
        TRACE_INFO: TRACE_INFO,
        TRACE_SUCCESS: TRACE_SUCCESS,
        TRACE_WARN: TRACE_WARN,
        TRACE_ERROR: TRACE_ERROR,
        TRACE_OFF: TRACE_OFF
    });
});