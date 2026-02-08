humhub.module('i18n', function(module, require, $) {
    var IntlMessageFormatter = (typeof IntlMessageFormat === 'function')
        ? IntlMessageFormat
        : (window.IntlMessageFormat && (window.IntlMessageFormat.IntlMessageFormat || window.IntlMessageFormat));

    var locale = module.config.language;
    var globalMessages = {};
    var loadedCategories = new Set();
    var pendingLoads = new Map();
    var compiledCache = new Map();

    var getStorageKey = function(category) {
        var revision = module.config.revision || '';
        var language = module.config.language || 'en';
        return 'humhub.i18n.' + revision + '.' + language + '.' + category;
    };

    function compileMessage(template) {
        var perLocale = compiledCache.get(locale);
        if (!perLocale) {
            perLocale = new Map();
            compiledCache.set(locale, perLocale);
        }
        var formatter = perLocale.get(template);
        if (!formatter) {
            formatter = new IntlMessageFormatter(template, locale);
            perLocale.set(template, formatter);
        }
        return formatter;
    }

    function updateIntlMessages(category, messages) {
        if (!globalMessages[category]) {
            globalMessages[category] = {};
        }
        $.extend(globalMessages[category], messages);
    }

    var loadTranslations = function(categories) {
        if (typeof categories === 'string') {
            categories = [categories];
        }

        var categoriesToLoad = categories.filter(function(category) {
            if (loadedCategories.has(category)) {
                return false;
            }

            try {
                var cached = localStorage.getItem(getStorageKey(category));
                if (cached) {
                    var messages = JSON.parse(cached);
                    updateIntlMessages(category, messages);
                    loadedCategories.add(category);
                    return false;
                }
            } catch (e) {}

            return true;
        });

        if (categoriesToLoad.length === 0) {
            return Promise.resolve();
        }

        var categoriesLoadingKey = categoriesToLoad.sort().join(',');
        if (pendingLoads.has(categoriesLoadingKey)) {
            return pendingLoads.get(categoriesLoadingKey);
        }

        var promise = $.ajax({
            url: module.config.translationUrl,
            data: {category: categoriesLoadingKey},
            method: 'GET'
        }).then(function(data) {
            if (data && data.messages) {
                $.each(data.messages, function(category, messages) {
                    updateIntlMessages(category, messages);
                    loadedCategories.add(category);

                    try {
                        localStorage.setItem(getStorageKey(category), JSON.stringify(messages));
                    } catch (e) {}
                });
            }
        }).always(function() {
            pendingLoads.delete(categoriesLoadingKey);
        });

        pendingLoads.set(categoriesLoadingKey, promise);
        return promise;
    };

    var translate = function(category, message, params) {
        params = params || {};
        var key = String(message);

        if (!loadedCategories.has(category)) {
            module.log.warn(`Category '${category}' was not correctly preloaded for translation '${key}'`);
        }

        var template = (globalMessages[category] && key in globalMessages[category]) ? globalMessages[category][key] : key;
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
        return loadTranslations(categories);
    };

    module.export({
        t: translate,
        loadTranslations: loadTranslations,
        preload: preload
    });
});
