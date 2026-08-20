/**
 * Test-side stand-in for the `@humhub/vue` import used in Vue sources. The
 * production build maps that import onto the humhub.modules.vue global; this
 * shim does the same, lazily, so import order does not matter in tests.
 */
const vueModule = () => globalThis.humhub.modules.vue;

export const register = (...args) => vueModule().register(...args);
export const isRegistered = (...args) => vueModule().isRegistered(...args);
export const registerSlotComponent = (...args) => vueModule().registerSlotComponent(...args);
export const getSlotComponents = (...args) => vueModule().getSlotComponents(...args);
export const registerMenuEntry = (...args) => vueModule().registerMenuEntry(...args);
export const removeMenuEntry = (...args) => vueModule().removeMenuEntry(...args);
export const getMenuEntries = (...args) => vueModule().getMenuEntries(...args);
export const mountElement = (...args) => vueModule().mountElement(...args);
export const getConfig = (...args) => vueModule().getConfig(...args);
export const url = (...args) => vueModule().url(...args);

export const client = {
    post: (...args) => vueModule().client.post(...args),
    get: (...args) => vueModule().client.get(...args),
};

export const i18n = {
    t: (...args) => vueModule().i18n.t(...args),
    preload: (...args) => vueModule().i18n.preload(...args),
};

export const log = {
    error: (...args) => vueModule().log.error(...args),
    warn: (...args) => vueModule().log.warn(...args),
    info: (...args) => vueModule().log.info(...args),
    debug: (...args) => vueModule().log.debug(...args),
};

export const modal = {
    confirm: (...args) => vueModule().modal.confirm(...args),
    load: (...args) => vueModule().modal.load(...args),
};

export const events = {
    on: (...args) => vueModule().events.on(...args),
    off: (...args) => vueModule().events.off(...args),
    trigger: (...args) => vueModule().events.trigger(...args),
};
