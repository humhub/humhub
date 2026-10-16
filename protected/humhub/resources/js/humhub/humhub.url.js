/**
 * Client-side counterpart of yii\helpers\Url::to() for default-routed
 * endpoints (module/controller/action). Fills a server-provided URL template
 * (see CoreJsConfig) — deliberately no client-side routing.
 */
humhub.module('url', function (module, require, $) {
    var to = function (route, params) {
        var template = module.config.template;
        var normalized = String(route).replace(/^\/+/, '');
        var result;

        if (!template) {
            // No CoreJsConfig template reached the client (e.g. a stray
            // island mounted before ready, or a broken jsConfig pipeline) —
            // a hardcoded '/index.php?r=' guess would be wrong for
            // subdirectory installs, so fall back to a root-relative URL
            // instead and surface the misconfiguration.
            // module.log is only attached at the ready sweep — fall back to the console
            (module.log || console).error('humhub.url: missing template config — using a root-relative fallback URL');
            result = '/' + normalized;
        } else if (template.indexOf('?r=__route__') !== -1) {
            result = template.replace('__route__', encodeURIComponent(normalized));
        } else {
            result = template.replace('__route__', normalized);
        }

        if (params) {
            var query = $.param(params);
            if (query) {
                result += (result.indexOf('?') !== -1 ? '&' : '?') + query;
            }
        }

        return result;
    };

    module.export({
        to: to,
    });
});
