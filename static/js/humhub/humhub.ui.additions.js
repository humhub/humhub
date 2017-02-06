/**
 * This module manages UI-Additions registered by other modules. Additions can be applied to DOM elements 
 * and are used to add a specific behaviour or manipulate the element itself. e.g: Richtext, Autosize input...
 * 
 * An addition can be registered for a specific selector e.g: <input data-addition-richtext ... />
 * It is possible to register multiple additions for the same selector.
 */
humhub.module('ui.additions', function(module, require, $) {

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
    var register = function(id, selector, handler, options) {
        options = options || {};

        if(!_additions[id] || options.overwrite) {
            _additions[id] = {
                'selector': selector,
                'handler': handler
            };

            // Make sure additions registrated after humhub:ready also affect element
            if(humhub.initialized) {
                apply($('body'), id);
            }
        } else if(options.extend) {
            options.selector = selector;
            module.extend(id, handler, options);
        }
    };

    /**
     * Applies all matched additions to the given element and its children
     * @param {type} element
     * @returns {undefined}
     */
    var applyTo = function(element, options) {
        options = options || {};

        var $element = (element instanceof $) ? element : $(element);
        $.each(_additions, function(id) {
            if(options.filter && !options.filter.indexOf(id)) {
                return;
            }
            try {
                module.apply($element, id);
            } catch(e) {
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
    var apply = function($element, id) {
        var addition = _additions[id];

        if(!addition) {
            return;
        }

        var $match = $element.find(addition.selector).addBack(addition.selector);
        addition.handler.apply($match, [$match, $element]);
    };

    var init = function() {
        event.on('humhub:ready', function(evt) {
            module.applyTo($('body'));
        });

        // workaround for jp-player since it sets display to inline which results in a broken view...
        $(document).on('click.humhub-jp-play', '.jp-play', function() {
            $(this).closest('.jp-controls').find('.jp-pause').css('display', 'block');
        });

        // Autosize textareas
        module.register('autosize', '.autosize', function($match) {
            $match.autosize();
        });

        // Show tooltips on elements
        module.register('tooltip', '.tt', function($match) {
            $match.tooltip({
                html: false,
                container: 'body'
            });

            $match.on('click.tooltip', function() {
                $('.tooltip').remove();
            });
        });

        module.register('markdown', '[data-ui-markdown]', function($match) {
            var converter = new Markdown.Converter();
            Markdown.Extra.init(converter);
            $match.each(function() {
                var $this = $(this);
                
                if($this.data('markdownProcessed')) {
                    return;
                }
                

                // Export all richtext features
                var features = {};
                $this.find('[data-richtext-feature]').each(function() {
                    var $this = $(this);
                    features[$this.data('guid')] = $this.clone();
                    $this.replaceWith($this.data('guid'));
                });
                
                var text = richtext.Richtext.plainText($this.clone());
                var result = converter.makeHtml(text);
         
                // Rewrite richtext feature
                $.each(features, function(guid, $element) {
                    result = result.replace(guid.trim(), $('<div></div>').html($element).html());
                });


                $this.html(result).data('markdownProcessed', true);
            });
        });

        $(document).on('click.humhub-ui-tooltip', function() {
            $('.tooltip').remove();
        });

        // Show popovers on elements
        module.register('popover', '.po', function($match) {
            $match.popover({html: true});
        });

        // Activate placeholder text for older browsers (specially IE)
        /*this.register('placeholder','input, textarea', function($match) {
         $match.placeholder();
         });*/

        // Replace the standard checkbox and radio buttons
       /* module.register('forms', ':checkbox, :radio', function($match) {
            //$match.flatelements();
        });*/

        // Deprecated!
        module.register('', 'a[data-loader="modal"], button[data-loader="modal"]', function($match) {
            $match.loader();
        });
    };

    var extend = function(id, handler, options) {
        options = options || {};

        if(_additions[id]) {
            var addition = _additions[id];
            if(options.prepend) {
                addition.handler = object.chain(addition.handler, handler, addition.handler);
            } else {
                addition.handler = object.chain(addition.handler, addition.handler, handler);
            }

            if(options.selector && options.selector !== addition.selector) {
                addition.selector += ',' + options.selector;
            }

            if(options.applyOnInit) {
                module.apply('body', id);
            }

        } else if(options.selector) {
            options.extend = false; // Make sure we don't get caught in a loop somehow.
            module.register(id, options.selector, handler, options);
        }
    };

    //TODO: additions.extend('id', handler); for extending existing additions.

    /**
     * Cleanup some nodes required to prevent memoryleaks in pjax mode.
     * @returns {undefined}
     */
    var unload = function() {
        // Tooltip issue
        // http://stackoverflow.com/questions/24841028/jquery-tooltip-add-div-role-log-in-my-page
        // https://bugs.jqueryui.com/ticket/10689
        $(".ui-helper-hidden-accessible").remove();

        // Jquery date picker div is not removed...
        $('#ui-datepicker-div').remove();
    };

    var switchButtons = function(outButton, inButton, cfg) {
        cfg = cfg || {};
        var animation = cfg.animation || 'bounceIn';
        var $out = (outButton instanceof $) ? outButton : $(outButton);
        var $in = (inButton instanceof $) ? inButton : $(inButton);

        $out.hide();
        if(cfg.remove) {
            $out.remove();
        }

        $in.addClass('animated ' + animation).show();
    };

    var highlight = function(node) {
        var $node = (node instanceof $) ? node : $(node);
        $node.addClass('highlight');
        $node.delay(200).animate({backgroundColor: 'transparent'}, 1000, function() {
            $node.removeClass('highlight');
            $node.css('backgroundColor', '');
        });
    };

    var observe = function(node, options) {
        if(object.isBoolean(options)) {
            options = {applyOnInit: options};
        } else if(!options) {
            options = {};
        }

        node = (node instanceof $) ? node[0] : node;

        var observer = new MutationObserver(function(mutations) {
            module.applyTo(node);
        });

        observer.observe(node, {childList: true, subtree: true});

        if(options.applyOnInit) {
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