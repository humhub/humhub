/**
 * This module manages UI-Additions registered by other modules. Additions can be applied to DOM elements 
 * and are used to add a specific behaviour or manipulate the element itself. e.g: Richtext, Autosize input...
 * 
 * An addition can be registered for a specific selector e.g: <input data-addition-richtext ... />
 * It is possible to register multiple additions for the same selector.
 */
humhub.module('ui.additions', function (module, require, $) {

    var event = require('event');
    var object = require('util.object');
    var richtext = require('ui.richtext', true);

    var _additions = {};

    /**
     * Registers an addition for a given jQuery selector. There can be registered
     * multiple additions for the same selector.
     * 
     * @param string id additionid
     * @param string selector jQuery selector
     * @param function addition addition function
     * @returns {undefined}
     */
    var register = function (id, selector, handler, options) {
        options = options || {};

        if (!_additions[id] || options.overwrite) {
            _additions[id] = {
                'selector': selector,
                'handler': handler
            };

            // Make sure additions registrated after humhub:ready also affect element
            if (humhub.initialized) {
                apply($('body'), id);
            }
        } else if (options.extend) {
            options.selector = selector;
            module.extend(id, handler, options);
        }
    };

    /**
     * Applies all matched additions to the given element and its children
     * @param {type} element
     * @returns {undefined}
     */
    var applyTo = function (element, options) {
        options = options || {};

        var $element = (element instanceof $) ? element : $(element);
        $.each(_additions, function (id) {
            // Only apply certain filter if filter option is set
            if (options.filter && !options.filter.indexOf(id)) {
                return;
            }
            
            try {
                module.apply($element, id);
            } catch (e) {
                module.log.error('Error while applying addition ' + id, e);
            }
        });
    };

    /**
     * Applies a given addition to all matches of the given $element.
     * @param {type} $element
     * @param {type} selector
     * @param {type} addition
     * @returns {undefined}
     */
    var apply = function ($element, id) {
        var addition = _additions[id];

        if (!addition) {
            return;
        }

        var $match = $element.find(addition.selector).addBack(addition.selector);
        
        // only apply addition if we actually find a match
        if(!$match.length) {
            return;
        }
        
        addition.handler.apply($match, [$match, $element]);
    };

    var init = function () {
        event.on('humhub:ready', function (evt) {
            module.applyTo($('body'));
        });
        
        require('action').registerHandler('copyToClipboard', function(evt) {
            clipboard.copy(evt.$target.text());
        });

        // workaround for jp-player since it sets display to inline which results in a broken view...
        $(document).on('click.humhub-jp-play', '.jp-play', function () {
            $(this).closest('.jp-controls').find('.jp-pause').css('display', 'block');
        });

        // Autosize textareas
        module.register('autosize', '.autosize', function ($match) {
            $match.autosize();
        });
        
        module.register('select2', '[data-ui-select2]', function ($match) {
            $match.select2({theme:"humhub"});
        });

        // Show tooltips on elements
        module.register('tooltip', '.tt', function ($match) {
            $match.tooltip({
                html: false,
                container: 'body'
            });

            $match.on('click.tooltip', function () {
                $('.tooltip').remove();
            });
        });



        $(document).on('click.humhub-ui-additions', function () {
            $('.tooltip').remove();
            $('.popover:not(.tour,.prevClose)').remove();
        });

        // Show popovers on elements
        module.register('popover', '.po', function ($match) {
            $match.popover({html: true});
        });

        // Deprecated!
        module.register('', 'a[data-loader="modal"], button[data-loader="modal"]', function ($match) {
            $match.loader();
        });
    };
    
    var extend = function (id, handler, options) {
        options = options || {};

        if (_additions[id]) {
            var addition = _additions[id];
            if (options.prepend) {
                addition.handler = object.chain(addition.handler, handler, addition.handler);
            } else {
                addition.handler = object.chain(addition.handler, addition.handler, handler);
            }

            if (options.selector && options.selector !== addition.selector) {
                addition.selector += ',' + options.selector;
            }

            if (options.applyOnInit) {
                module.apply('body', id);
            }

        } else if (options.selector) {
            options.extend = false; // Make sure we don't get caught in a loop somehow.
            module.register(id, options.selector, handler, options);
        }
    };

    /**
     * Cleanup some nodes required to prevent memoryleaks in pjax mode.
     * @returns {undefined}
     */
    var unload = function () {
        // Tooltip issue
        // http://stackoverflow.com/questions/24841028/jquery-tooltip-add-div-role-log-in-my-page
        // https://bugs.jqueryui.com/ticket/10689
        $(".ui-helper-hidden-accessible").remove();

        // Jquery date picker div is not removed...
        $('#ui-datepicker-div').remove();
        
        $('.popover').remove();
        $('.tooltip').remove();
    };

    var switchButtons = function (outButton, inButton, cfg) {
        cfg = cfg || {};
        var animation = cfg.animation || 'bounceIn';
        var $out = (outButton instanceof $) ? outButton : $(outButton);
        var $in = (inButton instanceof $) ? inButton : $(inButton);

        $out.hide();
        if (cfg.remove) {
            $out.remove();
        }

        $in.addClass('animated ' + animation).show();
    };

    var highlight = function (node) {
        var $node = (node instanceof $) ? node : $(node);
        $node.addClass('highlight');
        $node.delay(200).animate({backgroundColor: 'transparent'}, 1000, function () {
            $node.removeClass('highlight');
            $node.css('backgroundColor', '');
        });
    };

    var observe = function (node, options) {
        if (object.isBoolean(options)) {
            options = {applyOnInit: options};
        } else if (!options) {
            options = {};
        }

        var $node = $(node);
        node = $node[0];
        var observer = new MutationObserver(function (mutations) {
            mutations.forEach(function(mutation) {
                var $nodes = $(mutation.addedNodes).filter(function () {
                    return this.nodeType === 1; // filter out text nodes
                });

                $nodes.each(function() {
                    var $this = $(this);
                    module.applyTo($this);
                })
            });
        });

        observer.observe(node, {childList: true, subtree: true});

        if (options.applyOnInit) {
            module.applyTo(node, options);
        }

        return node;
    };

    module.export({
        init: init,
        observe: observe,
        unload: unload,
        applyTo: applyTo,
        apply: apply,
        extend: extend,
        register: register,
        switchButtons: switchButtons,
        highlight: highlight
    });
});

/**
 * Context Menu
 */
(function ($, window) {
    $.fn.contextMenu = function (settings) {
        return this.each(function () {

            // Open context menu
            $(this).on("contextmenu",
                    function (e) {
                        // return native menu if pressing control
                        if (e.ctrlKey) {
                            return;
                        }

                        // Make sure all menus are hidden
                        $('.contextMenu').hide();

                        var menuSelector = settings.getMenuSelector.call(this, $(e.target));

                        var oParent = $(menuSelector).parent().offsetParent().offset();
                        var posTop = e.clientY - oParent.top;
                        var posLeft = e.clientX - oParent.left;

                        // open menu
                        var $menu = $(menuSelector).data("invokedOn", $(e.target)).show().css({
                            position: "absolute",
                            left: getMenuPosition(posLeft, 'width', 'scrollLeft'),
                            top: getMenuPosition(posTop, 'height', 'scrollTop')
                        }).off('click').on('click', 'a', function (e) {
                            $menu.hide();

                            var $invokedOn = $menu.data("invokedOn");
                            var $selectedMenu = $(e.target);

                            settings.menuSelected.call(this, $invokedOn, $selectedMenu, e);
                        });

                        return false;
                    });

            // make sure menu closes on any click
            $(document).click(function () {
                $('.contextMenu').hide();
            });
        });

        function getMenuPosition(mouse, direction, scrollDir) {
            var win = $(window)[direction]();
            var scroll = $(window)[scrollDir]();
            var menu = $(settings.menuSelector)[direction]();
            var position = mouse + scroll;

            // opening menu would pass the side of the page
            if (mouse + menu > win && menu < mouse)
                position -= menu;

            return position;
        }

    };
})(jQuery, window);