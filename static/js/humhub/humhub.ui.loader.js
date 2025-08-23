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
 * If a data-ui-loader button is used within a yii ActiveForm we automatically reset all loader buttons
 * in case of form validation errors.
 *
 *
 * @param {type} param1
 * @param {type} param2
 */
humhub.module('ui.loader', function (module, require, $) {

    var DEFAULT_LOADER_SELECTOR = '#humhub-ui-loader-default';
    var LOADER_SELECTOR = '.hh-loader';

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
        $(node).find(LOADER_SELECTOR).remove();
    };

    var reset = function (node) {
        var $node = (node instanceof $) ? node : $(node);
        var $loader = $node.find(LOADER_SELECTOR).length;
        if (!$loader) {
            return;
        }

        $node.removeClass('disabled');

        if ($loader && $node.data('htmlOld')) {
            $node.html($node.data('htmlOld'));
        } else if ($loader) {
            $node.find(LOADER_SELECTOR).remove();
        }
    };

    var is = function (node) {
        return $(node).find(LOADER_SELECTOR).length > 0;
    };

    var getInstance = function (cfg, $this) {
        cfg = cfg || {};

        // TODO use div template instead of clone
        var $result = (cfg.span) ? getTemplate() : $(DEFAULT_LOADER_SELECTOR).clone().removeAttr('id').show();

        if (cfg.cssClass) {
            $result.addClass(cfg.cssClass);
        }

        if (cfg.id) {
            $result.attr('id', cfg.id);
        }

        if (cfg.style || cfg.css) {
            $result.css(cfg.style || cfg.css);
        }

        if($this && cfg.alignHeight) {
            var height = $this.innerHeight();
            $result.css('height', height+'px');
            $result.css('line-height', (height / 2)+'px');
        }

        if (cfg.position) {
            if (cfg.position === 'left') {
                $result.addClass('float-start');
            } else if (cfg.position === 'right') {
                $result.addClass('float-end');
            }
        }

        if (cfg.wrapper) {
            $result = $(cfg.wrapper).append($result);
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
                setTimeout(reset, 10, this);
            });
        });
    };

    var hasLoader = function ($node) {
        return $node.find(LOADER_SELECTOR).length > 0;
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

        // Get loader template
        var $loader = getTemplate($node.data('ui-loader'));

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

    var getTemplate = function (loadingMessage = '') {
        return '<span class="hh-loader text-center">'
            + '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>'
            + ' '
            + '<span role="status"' + (loadingMessage ? '' : ' class="visually-hidden"') + '>'
                + (loadingMessage || module.text('loading'))
            + '</span>'
        + '</span>';
    }

    module.export({
        set: set,
        is: is,
        remove: remove,
        append: append,
        prepend: prepend,
        reset: reset,
        getInstance: getInstance,
        getTemplate: getTemplate,
        initLoaderButton: initLoaderButton,
        init: init,
        sortOrder: 100,
    });
});
