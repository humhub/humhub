/**
 * Url manager module for client side url creation.
 */
humhub.module('urlManager', function (module, require, $) {
    const object = require('util').object;

    const containerMap = {};

    const getConfig = function () {
        return module.config || {};
    };

    const getRules = function () {
        var config = getConfig();
        return config.rules || [];
    };

    const normalizeRoute = function (route) {
        if (!route) {
            return '';
        }
        return ('' + route).replace(/^\/+|\/+$/g, '');
    };

    const normalizeParams = function (params) {
        var result = {};
        if (!params) {
            return result;
        }

        if (object.isObject(params)) {
            $.each(params, function (key, value) {
                if (typeof value !== 'undefined' && value !== null) {
                    result[key] = value;
                }
            });
        }

        return result;
    };

    const buildQuery = function (params) {
        var query = $.param(params || {});
        return query ? ('?' + query) : '';
    };

    const joinUrl = function (baseUrl, path) {
        if (!baseUrl || !baseUrl.length) {
            baseUrl = '/';
        }
        if (!path) {
            path = '';
        }

        if (!path.length) {
            return baseUrl;
        }

        if (path.charAt(0) === '?' || path.charAt(0) === '#') {
            return baseUrl + path;
        }

        var baseEndsWithSlash = baseUrl.charAt(baseUrl.length - 1) === '/';
        var pathStartsWithSlash = path.charAt(0) === '/';

        if (baseEndsWithSlash && pathStartsWithSlash) {
            return baseUrl + path.substr(1);
        }

        if (!baseEndsWithSlash && !pathStartsWithSlash) {
            return baseUrl + '/' + path;
        }

        return baseUrl + path;
    };

    const getBaseUrl = function () {
        var config = getConfig();
        if (config.showScriptName || !config.enablePrettyUrl) {
            return config.scriptUrl || '';
        }
        return config.baseUrl || '/';
    };

    const extractAnchor = function (params) {
        var anchor = '';
        if (object.isDefined(params['#'])) {
            anchor = '#' + params['#'];
            delete params['#'];
        }
        return anchor;
    };

    const resolvePlaceholder = function (part) {
        var match = part.match(/^<([a-zA-Z0-9_-]+)(?::[^>]+)?>$/);
        return match ? match[1] : null;
    };

    const createByRule = function (rule, route, params) {
        if (!rule || rule.route !== route) {
            return null;
        }

        var name = rule.name || '';
        if (!name) {
            return null;
        }

        var ruleParams = $.extend({}, params);
        var parts = name.split('/');
        var urlParts = [];

        for (var i = 0; i < parts.length; i++) {
            var placeholder = resolvePlaceholder(parts[i]);
            if (placeholder) {
                if (!object.isDefined(ruleParams[placeholder])) {
                    return null;
                }
                urlParts.push(encodeURIComponent(ruleParams[placeholder]));
                delete ruleParams[placeholder];
            } else {
                urlParts.push(parts[i]);
            }
        }

        var url = urlParts.join('/');
        var suffix = object.isDefined(rule.suffix) ? rule.suffix : getConfig().suffix;
        if (url && suffix) {
            url += suffix;
        }

        url += buildQuery(ruleParams);
        return url;
    };

    const findContainerConfig = function (route) {
        var config = getConfig();
        var types = (config.contentContainer && config.contentContainer.types) || {};

        for (var type in types) {
            if (!Object.prototype.hasOwnProperty.call(types, type)) {
                continue;
            }

            var typeConfig = types[type];
            if (typeConfig && typeConfig.defaultRoute === route) {
                return $.extend({type: type}, typeConfig);
            }
        }

        return null;
    };

    const findContainerRule = function (route, params, typeConfig) {
        var rules = getRules();
        var prefixes = (typeConfig && typeConfig.routePrefixes) || [];
        var prefixParts = [];

        if (!prefixes.length || !rules.length) {
            return null;
        }

        for (var i = 0; i < rules.length; i++) {
            var rule = rules[i];
            if (rule.route !== route || !rule.name) {
                continue;
            }

            for (var p = 0; p < prefixes.length; p++) {
                var prefix = prefixes[p];
                if (rule.name.indexOf(prefix + '/') !== 0) {
                    continue;
                }
                var ruleTail = rule.name.substr(prefix.length + 1);
                if (!ruleTail) {
                    continue;
                }

                prefixParts = ruleTail.split('/');
                var ruleParams = $.extend({}, params);
                var urlParts = [];
                var missing = false;

                for (var r = 0; r < prefixParts.length; r++) {
                    var placeholder = resolvePlaceholder(prefixParts[r]);
                    if (placeholder) {
                        if (!object.isDefined(ruleParams[placeholder])) {
                            missing = true;
                            break;
                        }
                        urlParts.push(encodeURIComponent(ruleParams[placeholder]));
                        delete ruleParams[placeholder];
                    } else {
                        urlParts.push(prefixParts[r]);
                    }
                }

                if (missing) {
                    continue;
                }

                return {
                    path: urlParts.join('/'),
                    params: ruleParams
                };
            }
        }

        return null;
    };

    const getContainer = function (guid) {
        return containerMap[guid] || null;
    };

    const setContainer = function (guid, data) {
        if (!guid || !data) {
            return;
        }
        containerMap[guid] = $.extend({}, data);
    };

    const createContentContainerUrl = function (route, params) {
        if (!params || !params.cguid) {
            return null;
        }

        var container = getContainer(params.cguid);
        if (!container || !container.urlPart || !container.prefix) {
            return null;
        }

        var contentContainerConfig = getConfig().contentContainer || {};
        var typeConfig = container.type && contentContainerConfig.types ? contentContainerConfig.types[container.type] : null;
        if (!typeConfig) {
            typeConfig = findContainerConfig(route);
        }

        var containerParams = $.extend({}, params);
        delete containerParams.cguid;

        var routePath = null;
        if (typeConfig) {
            if (route === typeConfig.defaultRoute) {
                routePath = '';
            } else {
                var matched = findContainerRule(route, containerParams, typeConfig);
                if (matched) {
                    routePath = matched.path;
                    containerParams = matched.params;
                }
            }
        }

        if (routePath === null) {
            routePath = normalizeRoute(route);
        }

        var url = container.prefix + '/' + encodeURIComponent(container.urlPart);
        if (routePath) {
            url += '/' + routePath;
        }

        url += buildQuery(containerParams);
        return url;
    };

    const createPrettyUrl = function (route, params) {
        var rules = getRules();
        if (!rules.length) {
            return null;
        }

        for (var i = 0; i < rules.length; i++) {
            var result = createByRule(rules[i], route, params);
            if (result !== null) {
                return result;
            }
        }

        return null;
    };

    const createDefaultUrl = function (route, params) {
        var config = getConfig();
        if (!config.enablePrettyUrl) {
            var url = '?' + encodeURIComponent(config.routeParam || 'r') + '=' + encodeURIComponent(route);
            if (!object.isEmpty(params)) {
                url += '&' + $.param(params);
            }
            return url;
        }

        var urlPath = normalizeRoute(route);
        if (config.suffix) {
            urlPath += config.suffix;
        }

        urlPath += buildQuery(params);
        return urlPath;
    };

    const create = function (route, params) {
        var config = getConfig();
        route = normalizeRoute(route);
        params = normalizeParams(params || {});

        if (params.container && params.container.guid) {
            setContainer(params.container.guid, params.container);
            params.cguid = params.container.guid;
            delete params.container;
        }

        var anchor = extractAnchor(params);
        var url = null;

        if (config.enablePrettyUrl) {
            url = createContentContainerUrl(route, params);
            if (url === null) {
                url = createPrettyUrl(route, params);
            }
            if (url === null) {
                url = createDefaultUrl(route, params);
            }
        } else {
            url = createDefaultUrl(route, params);
        }

        url = joinUrl(getBaseUrl(), url);
        return url + anchor;
    };

    const init = function () {
        var config = getConfig();
        if (config.contentContainerMap) {
            $.each(config.contentContainerMap, function (guid, data) {
                setContainer(guid, data);
            });
        }
    };

    module.export({
        init: init,
        create: create,
        setContainer: setContainer,
        getContainer: getContainer
    });
});
