humhub.initModule('client.pjax', function (module, require, $) {
    var object = require('util').object;

    var init = function () {
        pjaxRedirectFix();
        installLoader();
    }

    var pjaxRedirectFix = function () {
        var pjaxXhr;
        $(document).on("pjax:beforeSend", function (event, xhr, settings) {
            // Store the current xhr for the beforeReplace method (Header access)
            pjaxXhr = xhr;

            // Ignore links with data-target attribute
            if ($(event.relatedTarget).data('target')) {
                return false;
            }
        });

        $.ajaxPrefilter('html', function (options, originalOptions, jqXHR) {
            orgErrorHandler = options.error;
            options.error = function (xhr, textStatus, errorThrown) {
                var redirect = (xhr.status >= 301 && xhr.status <= 303)
                if (redirect && xhr.getResponseHeader('X-PJAX-REDIRECT-URL') != "") {
                    options.url = xhr.getResponseHeader('X-PJAX-REDIRECT-URL');
                    console.log('Handled redirect to: ' + options.url);
                    $.pjax(options);
                } else {
                    orgErrorHandler(xhr, textStatus, errorThrown);
                }
            }
        });
    }

    var installLoader = function () {
        NProgress.configure({showSpinner: false});
        NProgress.configure({template: '<div class="bar" role="bar"></div>'});

        jQuery(document).on('pjax:start', function () {
            NProgress.start();
        });
        jQuery(document).on('pjax:end', function () {
            NProgress.done();
        });
    }

    module.export({
        init: init,
        installLoader: installLoader,
    });
});
