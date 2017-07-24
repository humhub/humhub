/**
 *  
 * @param {type} param1
 * @param {type} param2
 */
humhub.module('ui.status', function (module, require, $) {

    var event = require('event');
    var log = require('log');
    var util = require('util');
    var object = util.object;
    var string = util.string;
    var client = require('client');

    module.initOnPjaxLoad = true;

    module.template = {
        info: '<i class="fa fa-info-circle info"></i><span>{msg}</span>',
        success: '<i class="fa fa-check-circle success"></i><span>{msg}</span>',
        warn: '<i class="fa fa-exclamation-triangle warning"></i><span>{msg}</span>',
        error: '<i class="fa fa-exclamation-circle error"></i><span>{msg}</span>',
        closeButton: '<a class="status-bar-close pull-right" style="">×</a>',
        showMoreButton: '<a class="showMore"><i class="fa fa-angle-up"></i></a>',
        errorBlock: '<div class="status-bar-details" style="display:none;"><pre>{msg}</pre><div>'
    };

    var state = {};
    var title;

    var SELECTOR_ROOT = '#status-bar';
    var SELECTOR_BODY = '.status-bar-body';
    var SELECTOR_CONTENT = '.status-bar-content';

    var AUTOCLOSE_INFO = 6000;
    var AUTOCLOSE_SUCCESS = 2000;
    var AUTOCLOSE_WARN = 10000;

    var StatusBar = function () {
        this.$ = $(SELECTOR_ROOT);
    };

    StatusBar.prototype.info = function (msg, closeAfter) {
        closeAfter = closeAfter || AUTOCLOSE_INFO;
        this._trigger(string.template(module.template.info, {msg: msg}), undefined, closeAfter);
    };

    StatusBar.prototype.success = function (msg, closeAfter) {
        closeAfter = closeAfter || AUTOCLOSE_SUCCESS;
        this._trigger(string.template(module.template.success, {msg: msg}), undefined, closeAfter);
    };

    StatusBar.prototype.warn = function (msg, error, closeAfter) {
        closeAfter = closeAfter || AUTOCLOSE_WARN;
        this._trigger(string.template(module.template.warn, {msg: msg}), error, closeAfter);
    };

    StatusBar.prototype.error = function (msg, error, closeAfter) {
        this._trigger(string.template(module.template.error, {msg: msg}), error, closeAfter);
    };

    StatusBar.prototype._trigger = function (content, error, closeAfter) {
        if (this.closeTimer) {
            clearTimeout(this.closeTimer);
        }

        var that = this;
        this.hide(function () {
            that.setContent(content, error).show(function () {
                if (closeAfter > 0) {
                    that.closeTimer = setTimeout(function () {
                        that.hide();
                    }, closeAfter);
                }
            });
        });
    };

    StatusBar.prototype.setContent = function (content, error) {
        var that = this;
        var $content = this.$.find(SELECTOR_CONTENT).html(content);
        var $closeButton = $(module.template.closeButton);

        if (error && module.config['showMore']) {
            this._addShowMore($content, error);
        }

        $closeButton.on('click', function () {
            that.hide();
        });

        $content.prepend($closeButton);
        return this;
    };

    StatusBar.prototype._addShowMore = function ($content, error) {
        var proxy = $.proxy(this.toggle, this, error);
        $(module.template.showMoreButton).on('click', proxy).appendTo($content);
        this.$.find('.status-bar-content span').on('click', proxy).css('cursor', 'pointer');
    };

    StatusBar.prototype.toggle = function (error) {
        var $content = this.$.find(SELECTOR_CONTENT);
        var $showMore = this.$.find('.showMore');
        var $details = $content.find('.status-bar-details');
        if ($details.length) {
            $details.stop().slideToggle('fast', function () {
                $details.remove();
            });

            $showMore.find('i').attr('class', 'fa fa-angle-up');
        } else {
            $details = $(string.template(module.template.errorBlock, {msg: getErrorMessage(error)}));
            $content.append($details);
            $details.slideToggle('fast');
            $showMore.find('i').attr('class', 'fa fa-angle-down');
        }
    };

    var getErrorMessage = function (error) {
        if (!error) {
            return;
        }

        try {
            if (object.isString(error)) {
                return error;
            } else if (error instanceof Error) {
                var result = error.toString();
                if (error.stack) {
                    result += error.stack;
                }
                return result;
            } else {
                if (error.error instanceof Error) {
                    error.stack = (error.error.stack) ? error.error.stack : undefined;
                    error.error = error.error.message;
                } else if (error instanceof client.Response) {
                    error = error.getLog();
                }
                try {
                    // encode
                    return $('<div/>').text(JSON.stringify(error, null, 4)).html();
                } catch (e) {
                    return error.toString();
                }
            }
        } catch (e) {
            log.error(e);
        }
    };

    StatusBar.prototype.show = function (callback) {
        // Make the container transparent for beeing able to measure the body height
        this.$.css('opacity', 0);
        this.$.show();

        // Prepare the body node for animation, we set auto height to get the real node height
        var $body = this.$.find(SELECTOR_BODY).stop().css('height', 'auto');
        var height = $body.innerHeight();

        // Hide element before animation
        $body.css({'opacity': '0', 'bottom': -height});

        // Show root container
        this.$.css('opacity', 1);

        $body.animate({bottom: '0', opacity: 1.0}, 500, function () {
            if (callback) {
                callback();
            }
        });
    };

    StatusBar.prototype.hide = function (callback) {
        var that = this;
        var $body = this.$.find(SELECTOR_BODY);
        var height = $body.innerHeight();

        $body.stop().animate({bottom: -height, opacity: 0}, 500, function () {
            that.$.hide();
            $body.css('bottom', '0');
            if (callback) {
                callback();
            }
        });
    };

    var init = function ($pjax) {
        title = document.title;
        if (!$pjax) {
            module.statusBar = new StatusBar();

            event.on('humhub:ready', function () {
                module.log.debug('Current ui state', state);
            }).on('humhub:modules:log:setStatus', function (evt, msg, details, level) {
                switch (level) {
                    case log.TRACE_ERROR:
                    case log.TRACE_FATAL:
                        module.statusBar.error(msg, details);
                        break;
                    case log.TRACE_WARN:
                        module.statusBar.warn(msg, details);
                        break;
                    case log.TRACE_SUCCESS:
                        module.statusBar.success(msg);
                        break;
                    default:
                        module.statusBar.info(msg);
                        break;
                }
            });

            // The initState can be used to append status messages before the module is initialized
            if (module.initState) {
                module.statusBar[module.initState[0]].apply(module.statusBar, module.initState[1]);
                module.initState = null;
            }
        }
    };

    module.export({
        init: init,
        setState: function (moduleId, controlerId, action) {
            // This function is called by controller itself
            state = {
                title: title || document.title,
                moduleId: moduleId,
                controllerId: controlerId,
                action: action
            };
        },
        getState: function () {
            return $.extend({}, state);
        },
        StatusBar: StatusBar,
        success: function (msg, closeAfter) {
            if (!module.statusBar) {
                module.initState = ['success', [msg, closeAfter]];
            } else {
                module.statusBar.success(msg, closeAfter);
            }
        },
        info: function (msg, closeAfter) {
            if (!module.statusBar) {
                module.initState = ['info', [msg, closeAfter]];
            } else {
                module.statusBar.info(msg, closeAfter);
            }
        },
        warn: function (msg, error, closeAfter) {
            if (!module.statusBar) {
                module.initState = ['warn', [msg, error, closeAfter]];
            } else {
                module.statusBar.warn(msg, error, closeAfter);
            }
        },
        error: function (msg, error, closeAfter) {
            if (!module.statusBar) {
                module.initState = ['error', [msg, error, closeAfter]];
            } else {
                module.statusBar.error(msg, error, closeAfter);
            }
        }

    });
});