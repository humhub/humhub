/**
 *  
 * @param {type} param1
 * @param {type} param2
 */
humhub.initModule('ui.status', function (module, require, $) {

    var event = require('event');
    var log = require('log');
    var object = require('util').object;

    var SELECTOR_ROOT = '#status-bar';
    var SELECTOR_BODY = '.status-bar-body';
    var SELECTOR_CONTENT = '.status-bar-content';

    var AUTOCLOSE_DELAY = 6000;

    var StatusBar = function () {
        this.$ = $(SELECTOR_ROOT);
    };

    StatusBar.prototype.info = function (msg, closeAfter) {
        closeAfter = closeAfter || AUTOCLOSE_DELAY;
        this._trigger('<i class="fa fa-info-circle info"></i><span>' + msg + '</span>', undefined, closeAfter);
    };
    
    StatusBar.prototype.success = function (msg, closeAfter) {
        closeAfter = closeAfter || AUTOCLOSE_DELAY;
        this._trigger('<i class="fa fa-check-circle success"></i><span>' + msg + '</span>', undefined, closeAfter);
    };

    StatusBar.prototype.warning = function (msg, error, closeAfter) {
        this._trigger('<i class="fa fa-exclamation-triangle warning"></i><span>' + msg + '</span>', error, closeAfter);
    };

    StatusBar.prototype.error = function (msg, error, closeAfter) {
        this._trigger('<i class="fa fa-exclamation-circle error"></i><span>' + msg + '</span>', error, closeAfter);
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
        var $closeButton = $('<a class="status-bar-close pull-right" style="">×</a>');

        if (error && module.config['showMore']) {
            this._addShowMoreButton($content, error);
        }

        $closeButton.on('click', function () {
            that.hide();
        });

        $content.prepend($closeButton);
        return this;
    };

    StatusBar.prototype._addShowMoreButton = function ($content, error) {
        var $showMore = $('<a class="showMore"><i class="fa fa-angle-up"></i></a>');
        $showMore.on('click', function () {
            var $details = $content.find('.status-bar-details');
            if($details.length) {
                $details.stop().slideToggle('fast', function() {
                    $details.remove();
                });
                
                $showMore.find('i').attr('class', 'fa fa-angle-up');
            } else {
                $details = $('<div class="status-bar-details" style="display:none;"><pre>' + getErrorMessage(error) + '</pre><div>'); 
                $content.append($details);
                $details.slideToggle('fast');
                $showMore.find('i').attr('class', 'fa fa-angle-down');
            }
        });
        $content.append($showMore);
    };

    var getErrorMessage = function (error) {
        try {
            if (!error) {
                return;
            } else if (object.isString(error)) {
                return error;
            } else if (error instanceof Error) {
                var result = error.toString();
                if(error.stack) {
                    result += error.stack;
                }
                return result;
            } else {
                return JSON.stringify(error, null, 4);
            }
        } catch (e) {
            log.error(e);
        }
    }

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
        
        $body.stop().animate({bottom: -height, opacity:0}, 500, function () {
            that.$.hide();
            $body.css('bottom', '0');
            if (callback) {
                callback();
            }
        });

    };

    var init = function () {
        module.statusBar = new StatusBar();
        event.on('humhub:modules:log:setStatus', function (evt, msg, details, level) {
            switch (level) {
                case log.TRACE_ERROR:
                case log.TRACE_FATAL:
                    module.statusBar.error(msg, details);
                    break;
                case log.TRACE_WARN:
                    module.statusBar.warning(msg, details);
                    break;
                case log.TRACE_SUCCESS:
                    module.statusBar.success(msg);
                    break;
                default:
                    module.statusBar.info(msg);
                    break;
            }
        });
    };

    module.export({
        init: init,
        StatusBar: StatusBar,
        success: function (msg, closeAfter) {
            module.statusBar.success(msg, closeAfter);
        },
        info: function (msg, closeAfter) {
            module.statusBar.info(msg, closeAfter);
        },
        warn: function (msg, error, closeAfter) {
            module.statusBar.warn(msg, error, closeAfter);
        },
        error: function (msg, error, closeAfter) {
            module.statusBar.error(msg, error, closeAfter);
        }
        
    });
});