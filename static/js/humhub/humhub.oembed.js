/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
humhub.module('oembed', function(module, require, $) {
    var client = require('client');

    var cache = {};

    var load = function(urls) {
        return new Promise(function(resolve, reject) {
            var result = {};
            var requestUrls = [];
            urls.forEach(function(url) {
                if(!cache[url]) {
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
        var result = cache[url];

        if(result) {
           return $(result);
        }

        var $dom =  $('[data-oembed="' + url + '"]');
        if($dom.length) {
            return $dom.clone().show();
        }
    };

    module.export({
        load: load,
        get: get
    })
});

