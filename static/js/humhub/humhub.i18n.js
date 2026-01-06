humhub.module('i18n', function(module, require, $) {
    var IntlMessageFormat = require('intl-messageformat');

    var locale = module.config.language;
    var globalMessages = {};
    var loadedCategories = new Set();
    var pendingLoads = new Map();
    var compiledCache = new Map();

    var getStorageKey = function(category) {
        var version = module.config.version || '';
        return 'humhub.i18n.' + version + '.' + locale + '.' + category;
    };

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

        try {
            var cached = localStorage.getItem(getStorageKey(category));
            if (cached) {
                var data = JSON.parse(cached);
                updateIntlMessages(data);
                loadedCategories.add(category);
                return Promise.resolve();
            }
        } catch (e) {}

        var promise = $.ajax({
            url: module.config.translationUrl,
            data: { category: category },
            method: 'GET'
        }).then(function(data) {
            if (data) {
                locale = data.locale;
                updateIntlMessages(data.messages);
                loadedCategories.add(category);

                try {
                    localStorage.setItem(getStorageKey(category), JSON.stringify(data.messages));
                } catch (e) {}
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

    /**
     * Preloads translations for given categories and returns a promise.
     * This should be called in module init() to ensure translations are loaded.
     *
     * @param {string|string[]} categories - Single category or array of categories to preload
     * @returns {Promise} Promise that resolves when all categories are loaded
     */
    var preload = function(categories) {
        if (typeof categories === 'string') {
            categories = [categories];
        }
        return Promise.all(categories.map(function(cat) {
            return loadTranslations(cat);
        }));
    };

    module.export({
        t: translate,
        loadTranslations: loadTranslations,
        preload: preload
    });
});
