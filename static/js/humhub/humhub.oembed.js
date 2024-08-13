/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
humhub.module('oembed', function(module, require, $) {
    var client = require('client');
    var util = require('util');
    var cache = {};

    var load = function(urls) {
        return new Promise(function(resolve, reject) {
            var result = {};
            var requestUrls = [];
            urls.forEach(function(url) {
                if (!cache[url]) {
                    requestUrls.push(url);
                } else {
                    result[url] = cache[url];
                }
            });

            client.post(module.config.loadUrl, {data: {urls: requestUrls}}).then(function(response) {
                $.extend(cache, response.data);
                resolve($.extend(result, response.data));
            }).catch(reject);
        });
    };

    const get = function(url) {
        const $result = cache[url] ? $(cache[url]) : findSnippetByUrl(url);

        if ($result && $result.is('.oembed_snippet,.oembed_confirmation')) {
           return $result;
        }

        return null;
    };

    const findSnippetByUrl = function(url) {
        const $dom = $('[data-oembed="' + $.escapeSelector(util.string.escapeHtml(url, true)) + '"]:first')
        if (!$dom.length || !$dom.is('[data-oembed]')) {
            return null;
        }

        const confirmation = $dom.find('.oembed_confirmation');
        if (confirmation.length) {
            return confirmation.clone().show();
        }

        return $dom.find('.oembed_snippet').clone().show();
    }

    const display = function(evt) {
        const confirmation = evt.$trigger.closest('.oembed_confirmation');
        if (!confirmation.length) {
            return;
        }

        const data = {
            url: confirmation.data('url'),
            alwaysShow: confirmation.find('input[type=checkbox]:checked').length ? 1 : 0,
        }

        client.post(module.config.displayUrl, {data}).then(function(response) {
            if (response.success) {
                confirmation.after(response.content).remove();
            } else {
                module.log.error(response, true);
                evt.finish();
            }
        }).catch(function(e) {
            module.log.error(e, true);
            evt.finish();
        });
    };

    module.export({
        load,
        get,
        display,
    });
});

