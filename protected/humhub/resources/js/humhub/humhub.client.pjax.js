humhub.module('client.pjax', function (module, require, $) {
    var event = require('event');

    var PJAX_CONTAINER_SELECTOR = '#layout-content';

    var init = function () {
        if (module.config.active) {
            $(document).pjax('a:not([data-pjax-prevent],[target],[data-bs-target],[data-bs-toggle],.exclude-from-pjax-client a)', PJAX_CONTAINER_SELECTOR, module.config.options);
            pjaxRedirectFix();
            module.installLoader();
        }
    };

    var post = function(evt) {
        var $options = $.extend({}, module.config.options);
        $options.url = evt.url;
        $options.container = PJAX_CONTAINER_SELECTOR;
        $options.type = 'POST';
        $.pjax($options);
    };

    var redirect = function(url) {
        $.pjax({url: url, container: PJAX_CONTAINER_SELECTOR, timeout : module.config.options.timeout});
    };

    var reload = function() {
        $.pjax.reload({container: PJAX_CONTAINER_SELECTOR, timeout : module.config.options.timeout});
    };

    var pjaxRedirectFix = function () {
        $(document).on("pjax:beforeSend", function (evt, xhr, options) {
            // Ignore links with data-bs-target attribute
            if ($(event.relatedTarget).data('target')) {
                return false;
            }
        });

        // The module-lifecycle unload signal (humhub.core.js unloads EVERY
        // module on it, e.g. humhub.vue.js used to unmount all of its islands)
        // is bound to pjax:send, not pjax:beforeSend: pjax:send only fires once
        // the request was actually dispatched (jquery.pjax.js only fires it for
        // xhr.readyState > 0), i.e. after every pjax:beforeSend handler allowed
        // the navigation. A pjax:beforeSend handler that cancels the navigation
        // — most importantly humhub.client.js's acknowledgeForm "Unsaved
        // changes will be lost" confirm — aborts the request before it is ever
        // sent, and no module may be unloaded for a navigation that never
        // happens: modules are only re-initialized on pjax:success, so a
        // beforeSend-triggered unload left the CURRENT page half-unloaded
        // (all Vue islands unmounted, document-level listeners gone) whenever
        // the user canceled that confirm. The humhub-level event keeps its
        // established name — it still marks "a pjax navigation is leaving this
        // page", just only for navigations that are actually happening.
        $(document).on("pjax:send", function (evt, xhr, options) {
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
        sortOrder: 100,
        reload: reload,
        redirect: redirect,
        post: post,
        isActive: isActive,
        installLoader: installLoader,
    });
});
