/**
 * This module is not in use since Yii provides an build in mechanism for blocking
 * ajax requests for scripts already loaded.
 * 
 * This module provides functions to keep track of loaded scripts and load scripts
 * once synchronously or asynchronously.
 * 
 * @param {type} param1
 * @param {type} param2
 */
humhub.module('scripts', function (module, require, $) {
    var _scripts = [];

    var init = function () {
        $('script[src]').each(function () {
            addScript(cutTimestamp($(this).attr('src')));
        });
    };

    var cutTimestamp = function (url) {
        return url.split('?')[0];
    };

    var loadOnce = function (urls) {
        urls = $.isArray(urls) ? urls : [urls];

        var promises = [];
        $.each(urls, function (index, scriptUrl) {
            if (!containsScript(scriptUrl)) {
                addScript(scriptUrl);
                promises.push($.getScript(scriptUrl));
            }
        });

        promises.push($.Deferred(function (deferred) {
            $(deferred.resolve);
        }));

        return $.when.apply(null, promises);
    };

    var loadOnceSync = function (urls, callback) {
        var deferred = new $.Deferred();
        var promise = deferred.promise();
        $.each(urls, function (index, scriptUrl) {
            if (!containsScript(scriptUrl)) {
                // we need an immediately invoked function expression to capture
                // the current value of the iteration 
                (function (url) {
                    // chaining the promises, 
                    // by assigning the new promise to the variable
                    // and returning a promise from the callback
                    promise = promise.then(function () {
                        addScript(url);
                        return $.getScript(url).done(function () {});
                    });
                }(scriptUrl));
            }
        });

        promise.done(function () {
            callback.apply();
        });

        promise.fail(function (arg) {
            console.error('Failed loading scripts for: ' + arg);
            //Call callback anyway
            callback.apply();
        });

        deferred.resolve();
    };

    var containsScript = function (url) {
        var result = false;
        url = cutTimestamp(url);
        $.each(_scripts, function (index, scriptUrl) {
            if (scriptUrl === url) {
                result = true;
                return false; //leave each
            }
        });
        return result;
    };

    var addScript = function (url) {
        url = cutTimestamp(url);
        _scripts.push(url);
    };

    module.export({
        loadOnce: loadOnce,
        loadOnceSync: loadOnceSync,
        containsScript: containsScript,
        addScript: addScript,
        init: init
    });
});