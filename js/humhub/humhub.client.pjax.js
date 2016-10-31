humhub.initModule('client.pjax', function (module, require, $) {
    var event = require('event');

    module.initOnPjaxLoad = false;

    var init = function () {
        pjaxRedirectFix();
        module.installLoader();
    };

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

        $(document).on("pjax:success", function (evt, data, status, xhr, options) {
            event.trigger('humhub:modules:client:pjax:afterPageLoad', {
                'originalEvent': evt,
                'data': data,
                'status': status,
                'xhr': xhr,
                'options': options
            });
        });

        $.ajaxPrefilter('html', function (options, originalOptions, jqXHR) {
            var orgErrorHandler = options.error;
            options.error = function (xhr, textStatus, errorThrown) {
                var redirect = (xhr.status >= 301 && xhr.status <= 303)
                if (redirect && xhr.getResponseHeader('X-PJAX-REDIRECT-URL') != "" && xhr.getResponseHeader('X-PJAX-REDIRECT-URL') !== null) {
                    options.url = xhr.getResponseHeader('X-PJAX-REDIRECT-URL');
                    console.log('Handled redirect to: ' + options.url);
                    $.pjax(options);
                } else {
                    orgErrorHandler(xhr, textStatus, errorThrown);
                }
            }
        });
    };

    var installLoader = function () {
        NProgress.configure({showSpinner: false});
        NProgress.configure({template: '<div class="bar" role="bar"></div>'});

        $(document).on('pjax:start', function () {
            NProgress.start();
        });
        
        $(document).on('pjax:end', function () {
            NProgress.done();
        });
    };

    module.export({
        init: init,
        installLoader: installLoader,
    });
});
