/**
 * Vitest global setup: emulates the browser environment humhub.vue.js runs in —
 * the humhub.module() registration API, the modules it require()s, and the
 * jQuery/Vue globals normally provided by asset bundles.
 */
import jQuery from 'jquery';
import * as Vue from 'vue';

globalThis.Vue = Vue;
globalThis.jQuery = jQuery;
globalThis.$ = jQuery;

const logCalls = { error: [], warn: [], info: [], debug: [] };
const makeLog = () => ({
    error: (...args) => logCalls.error.push(args),
    warn: (...args) => logCalls.warn.push(args),
    info: (...args) => logCalls.info.push(args),
    debug: (...args) => logCalls.debug.push(args),
});

const additions = {
    _registered: [],
    // Core behavior after page init: a newly registered addition is applied to
    // the current document immediately.
    // Simplification vs. core: humhub.ui.additions.js gates this immediate-apply
    // on `humhub.initialized` and only attaches `module.log` at the ready sweep;
    // this stub applies immediately and attaches log upfront for test
    // convenience. Modules under test must therefore not assume module.log
    // exists at call time — that ordering is enforced by separate tests.
    register(id, selector, handler) {
        this._registered.push({ id, selector, handler });
        const $match = jQuery(selector);
        if ($match.length) {
            handler.call($match, $match);
        }
    },
    // Core behavior for injected fragments (pjax, modals, stream entries).
    applyTo($element) {
        this._registered.forEach(({ selector, handler }) => {
            const $match = $element.find(selector).addBack(selector);
            if ($match.length) {
                handler.call($match, $match, $element);
            }
        });
    },
};

const stubs = {
    additions,
    i18n: {
        preload: () => Promise.resolve(),
        t: (category, message) => message,
    },
    client: {
        post: () => Promise.resolve({}),
        get: () => Promise.resolve({}),
    },
    event: { on() {}, off() {}, one() {}, trigger() {} },
    logCalls,
};

globalThis.humhubStubs = stubs;

globalThis.humhub = {
    modules: {},
    config: {
        module: () => ({}),
        get: () => null,
        is: () => false,
    },
    module(id, moduleFunction) {
        const instance = {
            id,
            log: makeLog(),
            config: globalThis.humhub.config.module(id),
            export(exports) {
                Object.assign(instance, exports);
            },
        };
        const req = (name) => ({
            'ui.additions': stubs.additions,
            i18n: stubs.i18n,
            client: stubs.client,
            event: stubs.event,
        })[name] || {};

        moduleFunction(instance, req, jQuery);
        globalThis.humhub.modules[id] = instance;
        if (typeof instance.init === 'function') {
            instance.init(false);
        }
    },
};
