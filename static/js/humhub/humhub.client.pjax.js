humhub.module('client.pjax', function (module, require, $) {
    var event = require('event');

    var PJAX_CONTAINER_SELECTOR = '#layout-content';

    var init = function () {
        if (module.config.active) {
            $(document).pjax('a:not([data-pjax-prevent],[target],[data-target],[data-toggle])', PJAX_CONTAINER_SELECTOR, module.config.options);
            pjaxRedirectFix();
            module.installLoader();
        }
    };
    
    var redirect = function(url) {
        $.pjax({url: url, container: PJAX_CONTAINER_SELECTOR, timeout : module.config.options.timeout});
    };
    
    var reload = function() {
        $.pjax.reload({container: PJAX_CONTAINER_SELECTOR, timeout : module.config.options.timeout});
    };

    var pjaxRedirectFix = function () {
        $(document).on("pjax:beforeSend", function (evt, xhr, options) {
            // Ignore links with data-target attribute
            if ($(event.relatedTarget).data('target')) {
                return false;
            }

            event.trigger('humhub:modules:client:pjax:beforeSend', {
                'originalEvent': evt,
                'xhr': xhr,
                'options': options
            });
        });

        $(document).on("pjax:success", function (evt, data, status, xhr, options) {
            event.trigger('humhub:modules:client:pjax:success', {
                'originalEvent': evt,
                'data': data,
                'status': status,
                'xhr': xhr,
                'options': options
            });
            
            // Update default ajax url, used if no url is given.
            $.ajaxSetup({
                url: window.location.href
            });
        });

        $.ajaxPrefilter('html', function (options, originalOptions, jqXHR) {
            var orgErrorHandler = options.error;
            options.error = function (xhr, textStatus, errorThrown) {
                if (isPjaxRedirect(xhr)) {
                    options.url = xhr.getResponseHeader('X-PJAX-REDIRECT-URL');
                    options.replace = true;
                    module.log.info('Handled redirect to: ' + options.url);
                    $.pjax(options);
                } else {
                    orgErrorHandler(xhr, textStatus, errorThrown);
                }
            };
        });
    };

    var isPjaxRedirect = function (xhr) {
        if (!xhr) {
            return false;
        }

        var redirect = (xhr.status >= 301 && xhr.status <= 303);
        return redirect && xhr.getResponseHeader('X-PJAX-REDIRECT-URL') != "" && xhr.getResponseHeader('X-PJAX-REDIRECT-URL') !== null;
    };

    var installLoader = function () {
        NProgress.configure({showSpinner: false});
        NProgress.configure({template: '<div class="bar" role="bar"></div>'});

        $(document).on('pjax:start', function (evt, xhr, options) {
            NProgress.start();
        });

        $(document).on('pjax:end', function (evt, xhr, options) {
            if (!isPjaxRedirect(xhr)) {
                NProgress.done();
            }
        });
    };

    var isActive = function () {
        return module.config.active;
    };

    module.export({
        init: init,
        reload: reload,
        redirect: redirect,
        isActive: isActive,
        installLoader: installLoader,
    });
});
