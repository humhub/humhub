/*
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */
humhub.module('oembed', function(module, require, $) {
    var client = require('client');
    var util = require('util');
    var status = require('ui.status');
    var cache = {};

    /**
     * Loads the previews of the given oembed urls, from the client cache or the server.
     *
     * Without options the media is always embedded - the editor's preview of a link the current
     * user just pasted. `options.consent` honors the user's oembed consent instead: for a domain
     * the user has not allowed, the result is the confirmation prompt a server-rendered richtext
     * shows as well (see `UrlOembed::isAllowedDomain()`) - the setting for rendering text somebody
     * else wrote (see `RichTextOutput.vue`). `options.silent` skips the status warning about urls
     * whose provider could not be reached; such urls are left out of the result.
     *
     * @param {string[]} urls
     * @param {{consent?: boolean, silent?: boolean}} [options]
     * @returns {Promise<Object<string, string>>} preview html by url
     */
    var load = function(urls, options) {
        options = options || {};

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

            if (!requestUrls.length) {
                resolve(result);
                return;
            }

            var data = {urls: requestUrls};
            if (options.consent) {
                data.consent = 1;
            }

            client.post(module.config.loadUrl, {data: data}).then(function(response) {
                const fetchedUrls = {};
                const brokenUrls = [];
                $.each(response.data, function(url, oembed) {
                    if (oembed) {
                        fetchedUrls[url] = oembed;
                    } else {
                        brokenUrls.push(url);
                    }
                });

                $.extend(cache, fetchedUrls);
                const resolveUrls = $.extend(result, fetchedUrls);
                if (brokenUrls.length > 0 && !options.silent) {
                    status.warn(module.text('brokenUrl').replace('{urls}',  brokenUrls.join(', ')));
                }

                resolve(resolveUrls);
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
                if (response.content) {
                    // The next render of this url on the page shows the media, not the prompt again.
                    cache[data.url] = response.content;
                }
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

