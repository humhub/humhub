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
    _registered: new Map(), // id -> { selector, handler }
    // Core behavior after page init: a newly registered addition is applied to
    // the current document immediately — gated on `humhub.initialized`, like
    // humhub.ui.additions.js gates its own initial-registration branch.
    // `module.log` timing matches the core too: it is only attached after the
    // module function returns, mimicking the ready sweep (see humhub.core.js),
    // so modules under test must not assume module.log exists while their
    // module function is executing (i.e. at register() time) — that ordering
    // is enforced by separate tests.
    // Simplification vs. core: `extend`'s `applyOnInit` re-apply is mirrored
    // unconditionally (real core gates the *first* registration on
    // `humhub.initialized` but not `extend`'s applyOnInit — see
    // humhub.ui.additions.js `extend()`); humhub.vue.js itself never passes
    // `applyOnInit` (see its registerMountPoint() doc comment for why: the
    // real implementation has a bug on that path).
    register(id, selector, handler, options) {
        options = options || {};
        const existing = this._registered.get(id);

        if (existing && options.extend) {
            existing.selector += ', ' + selector;
            const previousHandler = existing.handler;
            existing.handler = function (...args) {
                previousHandler.apply(this, args);
                handler.apply(this, args);
            };
            if (options.applyOnInit) {
                const $match = jQuery(existing.selector);
                if ($match.length) {
                    existing.handler.call($match, $match);
                }
            }
            return;
        }

        this._registered.set(id, { selector, handler });
        if (globalThis.humhub.initialized) {
            const $match = jQuery(selector);
            if ($match.length) {
                handler.call($match, $match);
            }
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

const moduleConfigs = {}; // id -> shared config object, so tests can mutate e.g. humhub.modules.vue.config

globalThis.humhub = {
    modules: {},
    initialized: true,
    config: {
        module: (id) => (moduleConfigs[id] = moduleConfigs[id] || {}),
        get: () => null,
        is: () => false,
    },
    module(id, moduleFunction) {
        const instance = {
            id,
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
        })[name] || globalThis.humhub.modules[name] || {};

        moduleFunction(instance, req, jQuery);
        instance.log = makeLog();
        globalThis.humhub.modules[id] = instance;
        if (typeof instance.init === 'function') {
            instance.init(false);
        }
    },
};
