import { afterEach, describe, expect, it, vi } from 'vitest';
import { createSource, legacySource, matches, normalizeItem, staticSource } from '../../vue/picker/sources.js';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

describe('picker items', () => {
    it('normalizes the legacy shape', () => {
        expect(normalizeItem({ id: 5, text: 'Anna', subText: 'PM', image: '/a.jpg', disabled: true }))
            .toEqual({ id: '5', text: 'Anna', subText: 'PM', image: { type: 'url', url: '/a.jpg' }, disabled: true, locked: false });
    });

    it('falls back to title/displayName and displayNameSub', () => {
        expect(normalizeItem({ id: 'g1', title: 'Space' }).text).toBe('Space');
        expect(normalizeItem({ id: 1, displayName: 'Bob', displayNameSub: 'Dev' })).toMatchObject({ text: 'Bob', subText: 'Dev' });
    });

    it('reads the id from the item key (legacy space answers carry guid only)', () => {
        expect(normalizeItem({ guid: 'g1', title: 'Space' }, 'guid').id).toBe('g1');
        expect(normalizeItem({ id: 7, guid: 'g7', text: 'X' }, 'guid').id).toBe('g7');
        expect(normalizeItem({ id: 7, text: 'X' }, 'guid').id).toBe('7');
    });

    it('matches case- and accent-insensitively', () => {
        expect(matches('Jürgen Müller', 'muller')).toBe(true);
        expect(matches('Anna', 'bob')).toBe(false);
        expect(matches('Anna', '')).toBe(true);
    });
});

describe('static source', () => {
    it('filters its items by the term', async () => {
        const source = staticSource([{ id: 'a', text: 'Apple' }, { id: 'b', text: 'Banana' }]);

        expect((await source.search('ban')).map((item) => item.id)).toEqual(['b']);
        expect((await source.search('')).map((item) => item.id)).toEqual(['a', 'b']);
    });
});

describe('legacy source', () => {
    afterEach(() => {
        globalThis.humhubStubs.client.get = vi.fn();
    });

    it('requests keyword= and normalizes the answer', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve([{ id: 1, text: 'Anna', image: '/a.jpg' }]));
        const source = legacySource('/user/search/json?space_id=3');

        const items = await source.search('an');

        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/user/search/json?space_id=3&keyword=an');
        expect(items).toEqual([{ id: '1', text: 'Anna', subText: null, image: { type: 'url', url: '/a.jpg' }, disabled: false, locked: false }]);
    });

    it('reads an answer wrapped in a client response', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ data: [{ id: 2, text: 'Bob' }] }));

        expect((await legacySource('/x').search('b')).map((item) => item.id)).toEqual(['2']);
    });

    it('reads ids by the item key', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve([{ guid: 'g1', title: 'Space' }]));

        expect((await legacySource('/space/browse/search-json', 'guid').search('s')).map((item) => item.id)).toEqual(['g1']);
    });
});

describe('createSource', () => {
    it('prefers a named source, then a url, then the static items', async () => {
        const factory = vi.fn(() => ({ search: () => Promise.resolve([{ id: 'n', text: 'Named' }]) }));
        vueModule.registerPickerSource('test.sources.named', factory);

        const named = createSource({ source: 'test.sources.named', url: '/x', options: [], itemKey: 'guid', params: { a: 1 } });
        expect(factory).toHaveBeenCalledWith({ itemKey: 'guid', params: { a: 1 } });
        expect((await named.search('')).map((item) => item.id)).toEqual(['n']);

        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve([]));
        await createSource({ url: '/legacy', options: [] }).search('q');
        expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/legacy?keyword=q');

        expect((await createSource({ options: [{ id: 's', text: 'Static' }] }).search('')).map((item) => item.id)).toEqual(['s']);
    });

    it('hands over the image of a new item a named source gives, none otherwise', () => {
        vueModule.registerPickerSource('test.sources.newImage', () => ({ search: () => Promise.resolve([]), newImage: { type: 'icon', name: 'star' } }));

        expect(createSource({ source: 'test.sources.newImage' }).newImage).toEqual({ type: 'icon', name: 'star' });
        expect(createSource({ source: 'test.sources.named' }).newImage).toBeNull();
        expect(createSource({ url: '/legacy' }).newImage).toBeNull();
        expect(createSource({ options: [] }).newImage).toBeNull();
    });

    it('drops raw items without an id', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve([{ text: 'No id' }, null, { id: 5, text: 'Ok' }]));
        expect((await createSource({ url: '/legacy' }).search('')).map((item) => item.id)).toEqual(['5']);

        vueModule.registerPickerSource('test.sources.malformed', () => ({ search: () => Promise.resolve([{ text: 'No id' }, { id: 'k', text: 'Ok' }]) }));
        expect((await createSource({ source: 'test.sources.malformed' }).search('')).map((item) => item.id)).toEqual(['k']);
    });

    it('falls back to the static items for an unregistered source', async () => {
        const source = createSource({ source: 'test.sources.missing', options: [{ id: 's', text: 'Static' }] });

        expect(source.remote).toBe(false);
        expect((await source.search('')).map((item) => item.id)).toEqual(['s']);
    });
});
