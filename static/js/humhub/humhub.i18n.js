humhub.module('i18n', function(module, require, $) {
    var IntlMessageFormat = require('intl-messageformat');

    var locale = module.config.language;
    var globalMessages = {};
    var loadedCategories = new Set();
    var pendingLoads = new Map();
    var compiledCache = new Map();

    function compileMessage(template) {
        var perLocale = compiledCache.get(locale);
        if (!perLocale) {
            perLocale = new Map();
            compiledCache.set(locale, perLocale);
        }
        var formatter = perLocale.get(template);
        if (!formatter) {
            formatter = new IntlMessageFormat(template, locale);
            perLocale.set(template, formatter);
        }
        return formatter;
    }

    function updateIntlMessages(newMessages) {
        globalMessages = $.extend({}, globalMessages, newMessages);
    }

    var loadTranslations = function(category) {
        if (loadedCategories.has(category)) {
            return Promise.resolve();
        }
        if (pendingLoads.has(category)) {
            return pendingLoads.get(category);
        }

        var promise = $.ajax({
            url: module.config.translationUrl,
            data: { category: category },
            method: 'GET'
        }).then(function(data) {
            if (data) {
                locale = data.locale;
                updateIntlMessages(data.messages);
                loadedCategories.add(category);
            }
        }).always(function() {
            pendingLoads.delete(category);
        });

        pendingLoads.set(category, promise);
        return promise;
    };

    var translate = function(category, message, params) {
        params = params || {};
        var key = String(message);

        if (!loadedCategories.has(category)) {
            return loadTranslations(category).then(function() {
                var template = (globalMessages && key in globalMessages) ? globalMessages[key] : key;
                return compileMessage(template).format(params);
            }).catch(function(err) {
                return compileMessage(key).format(params);
            });
        }

        var template = (globalMessages && key in globalMessages) ? globalMessages[key] : key;
        return compileMessage(template).format(params);
    };

    module.export({
        t: translate,
        loadTranslations: loadTranslations
    });
});
