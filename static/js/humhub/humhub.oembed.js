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
        var $result = null;

        if (cache[url]) {
            $result = $(cache[url]);
        } else {
            var $dom =  $('[data-oembed="' + $.escapeSelector(util.string.escapeHtml(url, true)) + '"]:first');
            if ($dom.length && $dom.is('[data-oembed]')) {
                $result = $dom.find('.oembed_snippet').clone().show();
            }
        }

        if($result && $result.is('.oembed_snippet')) {
           return $result;
        }

        return null;
    };

    module.export({
        load: load,
        get: get
    });
});

