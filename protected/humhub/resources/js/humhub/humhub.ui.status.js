humhub.module('ui.status', function (module, require, $) {

    /** @module ui/status **/

    var event = require('event');
    var log = require('log');
    var util = require('util');
    var object = util.object;
    var client = require('client');

    /**
     * The status bar itself is a Vue island (StatusBar.vue in the core component
     * set, mounted by humhub\widgets\StatusBar). This module stays as the stable
     * entry point every caller already uses - the exported success/info/warn/error
     * functions, and the inline `humhub.modules.ui.status.<type>(...)` snippet
     * humhub\components\View::endBody() registers for session flash messages - and
     * only forwards to the island through the bridge, which queues messages that
     * arrive before it has mounted (see `status()` in humhub.vue.js).
     *
     * What stays HERE rather than moving into the component: flattening the
     * `details` argument. Its interesting shapes are legacy ones only this side
     * can recognise (a `client.Response`, a jQuery-style `{error: Error}`
     * envelope), so the island receives a plain string and needs no knowledge of
     * them.
     */

    /**
     * `stack` already starts with the error's own `toString()` in every engine
     * HumHub supports, so the legacy concatenation of both repeated the message
     * line - only prepend it where an engine leaves it out.
     */
    var formatError = function (error) {
        var text = error.toString();

        if (!error.stack) {
            return text;
        }

        return error.stack.indexOf(text) === 0 ? error.stack : text + '\n' + error.stack;
    };

    /**
     * Turns the `details` argument of warn()/error() into the string the island
     * renders in its trace block. Same cases as the former private
     * getErrorMessage(), minus the HTML escaping: the island renders the result
     * as text, not as markup.
     */
    var normalizeDetails = function (details) {
        if (!details) {
            return undefined;
        }

        try {
            if (object.isString(details)) {
                return details;
            }

            if (details instanceof Error) {
                return formatError(details);
            }

            // `client.Response` is guarded rather than assumed: this module must never
            // turn a status message into an exception just because the client module
            // was not (fully) registered yet.
            if (client.Response && details instanceof client.Response) {
                details = details.getLog();
            } else if (details.error instanceof Error) {
                details = $.extend({}, details, {
                    error: details.error.message,
                    stack: details.error.stack,
                });
            }

            return JSON.stringify(details, null, 4);
        } catch (e) {
            log.error(e);
            return undefined;
        }
    };

    var HTML_ENTITIES = {
        '&amp;': '&',
        '&lt;': '<',
        '&gt;': '>',
        '&quot;': '"',
        '&#39;': '\'',
        '&#039;': '\'',
        '&apos;': '\'',
        '&nbsp;': ' ',
    };

    /**
     * Legacy message argument handling: callers historically passed a string the
     * jQuery bar injected with `.html()`, so some of them (module inline scripts,
     * and this core's own View::endBody() before it switched to a JSON payload)
     * hand over HTML-encoded text. The island renders text, so the entities
     * `Html::encode()`/`htmlspecialchars(ENT_QUOTES)` produces are decoded here.
     *
     * Deliberately a fixed table rather than a DOM round-trip: it decodes exactly
     * those entities, in a single pass (so `&amp;lt;` decodes to `&lt;`, not `<`),
     * and cannot interpret markup. Tags a caller passes literally therefore show
     * up as text now - see docs/develop/module-migrate.md.
     */
    var decodeEntities = function (message) {
        if (!object.isString(message) || message.indexOf('&') === -1) {
            return message;
        }

        return message.replace(/&(?:amp|lt|gt|quot|#0?39|apos|nbsp);/g, function (entity) {
            return HTML_ENTITIES[entity];
        });
    };

    /**
     * `vue` is resolved per call, not captured at definition time: CoreApiAsset
     * happens to load humhub.vue.js first today, but a module-scope require() of a
     * not-yet-registered module hands out an empty placeholder that never fills in
     * (the same trap humhub.vue.js documents for its own `ui.modal` usage), and no
     * asset order should be able to turn every status message into a silent no-op.
     */
    var send = function (level, message, details, closeAfter) {
        require('vue').status(level, decodeEntities(message), normalizeDetails(details), closeAfter);
    };

    var init = function ($pjax) {
        if ($pjax) {
            return;
        }

        event.on('humhub:modules:log:setStatus', function (evt, msg, details, level) {
            switch (level) {
                case log.TRACE_ERROR:
                case log.TRACE_FATAL:
                    send('error', msg, details);
                    break;
                case log.TRACE_WARN:
                    send('warn', msg, details);
                    break;
                case log.TRACE_SUCCESS:
                    send('success', msg);
                    break;
                default:
                    send('info', msg);
                    break;
            }
        });
    };

    module.export({
        init: init,
        sortOrder: 100,
        success: function (msg, closeAfter) {
            send('success', msg, undefined, closeAfter);
        },
        info: function (msg, closeAfter) {
            send('info', msg, undefined, closeAfter);
        },
        warn: function (msg, error, closeAfter) {
            send('warn', msg, error, closeAfter);
        },
        error: function (msg, error, closeAfter) {
            send('error', msg, error, closeAfter);
        },
    });
});
