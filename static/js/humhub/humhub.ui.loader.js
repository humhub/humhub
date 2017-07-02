/**
 * Module for adding loader animations to dom nodes.
 * 
 * The default loader animation can be added or appended/prepended as follows
 * 
 * var loader = require('ui.loader');
 * 
 * // Overwrite current html content with loader animation
 * loader.set(myNode);
 * 
 * // Remove loader animation
 * loader.reset(myNode);
 * 
 * The loader module also adds an click handler to all buttons and links with a
 * data-ui-loader attribute set.
 * 
 * If a data-ui-loader button is used within a yii ActiveForm we automaticly reset all loader buttons
 * in case of form validation errors.
 * 
 *  
 * @param {type} param1
 * @param {type} param2
 */
humhub.module('ui.loader', function (module, require, $) {

    var DEFAULT_LOADER_SELECTOR = '#humhub-ui-loader-default';

    var set = function (node, cfg) {
        var $node = (node instanceof $) ? node : $(node);

        if ($node.length) {
            $node.each(function () {
                var $this = $(this);

                if (hasLoader($this)) {
                    return;
                }

                $this.data('htmlOld', $node.html());
                $this.html(getInstance(cfg, $this));
            });

        }
        return $node;
    };

    var append = function (node, cfg) {
        var $node = (node instanceof $) ? node : $(node);
        if ($node.length) {
            $node.append(getInstance(cfg));
        }
    };

    var prepend = function (node, cfg) {
        var $node = (node instanceof $) ? node : $(node);
        if ($node.length) {
            $node.prepend(getInstance(cfg));
        }
    };

    var remove = function (node) {
        $(node).find('.loader').remove();
    };

    var reset = function (node) {
        var $node = (node instanceof $) ? node : $(node);
        var $loader = $node.find('.loader').length;
        if (!$loader) {
            return;
        }

        $node.removeClass('disabled');

        if ($loader && $node.data('htmlOld')) {
            $node.html($node.data('htmlOld'));
        } else if ($loader) {
            $node.find('.loader').remove();
        }
    };

    var is = function (node) {
        return $(node).find('.loader').length > 0;
    };

    var getInstance = function (cfg, $this) {
        cfg = cfg || {};

        var $result = $(DEFAULT_LOADER_SELECTOR).clone().removeAttr('id').show();

        if (cfg['cssClass']) {
            $result.addClass(cfg['cssClass']);
        }

        if (cfg['id']) {
            $result.attr('id', cfg['id']);
        }

        if (cfg['css']) {
            $result.css(cfg['css']);
        }
        
        if($this && cfg['alignHeight']) {
            var height = $this.innerHeight();
            $result.css('height', height+'px');
            $result.css('line-height', (height / 2)+'px');
        }

        if (cfg['position']) {
            if (cfg['position'] === 'left') {
                $result.find('.sk-spinner').css('margin', '0');
            } else if (cfg['position'] === 'right') {
                $result.find('.sk-spinner').css('margin', '0').addClass('pull-right');
                $result.addClass('clearfix');
            }
        }

        $skBounce = $result.find('.sk-bounce1, .sk-bounce2, .sk-bounce3');

        if (cfg['itemCss']) {
            $skBounce.css(cfg['itemCss']);
        }

        if (cfg['size']) {
            var size = cfg['size'];
            $skBounce.css({'width': size, 'height': size});
        }

        if (cfg['wrapper']) {
            $result = $(cfg['wrapper']).append($result);
        }

        return $result;
    };

    var init = function () {
        $(document).on('click.humhub:modules:ui:loader', '[data-ui-loader]', function (evt) {
            return module.initLoaderButton(this, evt);
        });

        $(document).on('afterValidate.humhub:modules:ui:loader', function (evt, messages, errors) {
            if (errors.length) {
                $(evt.target).find('[data-ui-loader]').each(function () {
                    reset(this);
                });
            }
        });

        // Added support for html5 inputs e.g. email validation
        $('input').on('invalid', function () {
            $(this).closest('form').find('[data-ui-loader]').each(function () {
                reset(this);
            });
        });
    };

    var hasLoader = function ($node) {
        return $node.find('.loader').length > 0;
    };

    var initLoaderButton = function (node, evt) {
        var $node = (node instanceof $) ? node : $(node);
        var loader = hasLoader($node);

        /**
         * Prevent multiple mouse clicks, if originalEvent is present its a real mouse event otherwise its script triggered
         * This is a workaround since yii version 2.0.10 changed the activeForm submission from $form.submit() to data.submitObject.trigger("click");
         * which triggers this handler twice. Here we get sure not to block the script triggered submission.
         */
        if (loader && evt.originalEvent) {
            return false;
        } else if (loader) {
            return;
        }

        // Adopt current color for the loader animation
        var color = $node.css('color') || '#ffffff';
        var $loader = $(module.template);

        // Align bouncer animation color and size
        $loader.find('.sk-bounce1, .sk-bounce2, .sk-bounce3')
                .addClass('disabled')
                .css({'background-color': color, 'width': '10px', 'height': '10px'});

        // The loader does have some margin we have to hide
        $node.css('overflow', 'hidden');
        $node.addClass('disabled');

        // Prevent the container from resizing
        $node.css('min-width', node.getBoundingClientRect().width);

        // Somehow the form submission is disturbed sometimes if we do not set a timeout.
        if ($node.is('[type="submit"]')) {
            setTimeout(function () {
                $node.data('htmlOld', $node.html());
                $node.html($loader);
            }, 10);
        } else {
            $node.data('htmlOld', $node.html());
            $node.html($loader);
        }
    };

    var template = '<span class="loader"><span class="sk-spinner sk-spinner-three-bounce"><span class="sk-bounce1"></span><span class="sk-bounce2"></span><span class="sk-bounce3"></span></span></span>';

    module.export({
        set: set,
        is: is,
        remove: remove,
        append: append,
        prepend: prepend,
        reset: reset,
        getInstance: getInstance,
        template: template,
        initLoaderButton: initLoaderButton,
        init: init
    });
});