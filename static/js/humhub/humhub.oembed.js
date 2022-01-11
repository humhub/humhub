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

    var get = function(url) {
        var $result = cache[url] ? $(cache[url]) : findSnippetByUrl(url, true);

        if ($result && $result.is('.oembed_snippet,.oembed_confirmation')) {
           return $result;
        }

        return null;
    };

    var findSnippetByUrl = function(url, confirm) {
        var $dom =  $('[data-oembed="' + $.escapeSelector(util.string.escapeHtml(url, true)) + '"]:first');
        if ($dom.length && $dom.is('[data-oembed]')) {
            if (confirm) {
                var confirmation = $dom.find('.oembed_confirmation');
                if (confirmation.length) {
                    return confirmation.clone().show();
                }
            }
            return $dom.find('.oembed_snippet').clone().show();
        }

        return null;
    }

    var confirm = function(evt) {
        var confirmation = evt.$trigger.closest('.oembed_confirmation');
        if (!confirmation.length) {
            return;
        }

        if (confirmation.find('input[type=checkbox]:checked').length) {
            client.post(module.config.confirmUrl, {data: {url: confirmation.data('url')}}).then(function(response) {
                if (response.success) {
                    displayContent(confirmation);
                } else {
                    module.log.error(response, true);
                    evt.finish();
                }
            }).catch(function(e) {
                module.log.error(e, true);
                evt.finish();
            });
        } else {
            displayContent(confirmation);
        }
    };

    var displayContent = function (confirmation) {
        var snippet = findSnippetByUrl(confirmation.data('url'), false);
        if (snippet && snippet.is('.oembed_snippet')) {
            confirmation.after(snippet).remove();
        }
    }

    module.export({
        load: load,
        get: get,
        confirm: confirm,
    });
});

