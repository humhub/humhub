import { afterEach, beforeAll, beforeEach, describe, expect, it, vi } from 'vitest';

// The ICU formatter is a global of the browser bundle; a pass-through stands in for it
globalThis.IntlMessageFormat = class {
    constructor(template) {
        this.template = template;
    }

    format() {
        return this.template;
    }
};

// The `config` module the i18n module reads its core settings from (debug off: the cache is used)
const coreConfig = { debug: false, systemRevision: '' };
globalThis.humhub.modules.config = { module: () => coreConfig };

await import('../../resources/js/humhub/humhub.i18n.js');

const i18n = globalThis.humhub.modules.i18n;
const $ = globalThis.jQuery;

describe('humhub.i18n', () => {
    let ajax;

    // Every test uses its own category names: loaded categories live in module state
    let n = 0;
    const cat = (name) => `${name}${++n}`;

    const answer = (messages) => ajax.mockImplementation(() => $.Deferred().resolve({ locale: 'en', messages }).promise());
    const warnings = () => globalThis.humhubStubs.logCalls.warn;

    beforeAll(() => {
        i18n.config.language = 'de';
        i18n.config.translationUrl = '/i18n/translations';
    });

    beforeEach(() => {
        localStorage.clear();
        warnings().length = 0;
        ajax = vi.spyOn($, 'ajax');
    });

    afterEach(() => {
        ajax.mockRestore();
    });

    it('marks a category without translations as loaded and does not warn on translate', async () => {
        const empty = cat('empty');
        const full = cat('full');
        answer({ [empty]: [], [full]: { Hello: 'Hallo' } });

        await i18n.loadTranslations([empty, full]);

        expect(i18n.t(empty, 'Hello')).toBe('Hello');
        expect(i18n.t(full, 'Hello')).toBe('Hallo');
        expect(warnings().length).toBe(0);
    });

    it('marks a requested category as loaded even when the answer omits it', async () => {
        const omitted = cat('omitted');
        answer({});

        await i18n.loadTranslations(omitted);

        i18n.t(omitted, 'Hello');
        expect(warnings().length).toBe(0);
    });

    it('caches an empty category and does not request it again', async () => {
        const empty = cat('cached');
        answer({ [empty]: [] });

        await i18n.loadTranslations(empty);

        expect(JSON.parse(localStorage.getItem(`humhub.i18n..de.${empty}`))).toEqual([]);

        await i18n.loadTranslations(empty);
        expect(ajax).toHaveBeenCalledTimes(1);
    });

    it('restores an empty category from the cache as loaded', async () => {
        const empty = cat('restored');
        localStorage.setItem(`humhub.i18n..de.${empty}`, '{}');

        await i18n.loadTranslations(empty);

        expect(ajax).not.toHaveBeenCalled();
        i18n.t(empty, 'Hello');
        expect(warnings().length).toBe(0);
    });

    it('still warns for a category that was never preloaded', () => {
        const never = cat('never');

        expect(i18n.t(never, 'Hello')).toBe('Hello');
        expect(warnings().length).toBe(1);
        expect(String(warnings()[0][0])).toContain(never);
    });
});
