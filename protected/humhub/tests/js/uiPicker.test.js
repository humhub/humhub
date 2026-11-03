import { afterEach, beforeAll, describe, expect, it, vi } from 'vitest';

// The core `config` module (humhub.core.js): `get(moduleId)` answers the module's config object.
globalThis.humhub.module('config', function (module) {
    module.export({
        get: (id) => globalThis.humhub.config.module(id),
        module: (id) => globalThis.humhub.config.module(id),
    });
});

await import('../../resources/js/humhub/humhub.util.js');
await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');
await import('../../resources/js/humhub/humhub.ui.picker.js');

const picker = globalThis.humhub.modules['ui.picker'];
const vueModule = globalThis.humhub.modules.vue;
const $ = globalThis.jQuery;

const render = (html) => {
    document.body.innerHTML = `<form>${html}</form>`;
    return document.body.querySelector('select');
};

describe('ui.picker bridge', () => {
    beforeAll(() => {
        vi.spyOn(vueModule, 'mountElement').mockImplementation(() => Promise.resolve());
    });

    afterEach(() => {
        document.body.innerHTML = '';
        vueModule.mountElement.mockClear();
    });

    it('mounts a PickerInput right after each picker select, once', () => {
        const select = render('<select id="a1" data-ui-picker></select><div class="invalid-feedback"></div>');
        picker.bind(select);
        picker.bind(select);

        const host = select.nextElementSibling;
        expect(host.classList.contains('picker-host')).toBe(true);
        expect(host.getAttribute('data-vue-component')).toBe('PickerInput');
        expect(JSON.parse(host.getAttribute('data-props'))).toEqual({ selectId: 'a1' });
        expect(host.nextElementSibling.classList.contains('invalid-feedback')).toBe(true);
        expect(vueModule.mountElement).toHaveBeenCalledTimes(1);
    });

    it('binds the matches of the picker UI addition', () => {
        const { selector, handler } = globalThis.humhubStubs.additions._registered.get('picker');
        render('<select id="d1" data-ui-picker></select><select id="d2" data-ui-select2></select><select id="d3"></select>');
        const $match = $(selector);
        handler.call($match, $match);

        expect(vueModule.mountElement).toHaveBeenCalledTimes(2);
        expect(document.getElementById('d3').nextElementSibling).toBeNull();
    });

    it('gives a select without id one', () => {
        const select = render('<select data-ui-select2></select>');
        picker.bind(select);
        expect(select.id).toMatch(/^picker-\d+$/);
    });

    it('reads and sets the chosen items of a select, firing change', () => {
        const select = render('<select id="b1" multiple><option value="1" selected data-text="One" data-image="#f00">One</option></select>');
        const changes = vi.fn();
        select.addEventListener('change', changes);

        expect(picker.getItems(select)).toEqual([{ id: '1', text: 'One', subText: null, image: '#f00' }]);

        picker.setItems(select, [{ id: '2', text: 'Two', image: { type: 'icon', name: 'star' } }]);
        // The server-selected option stays (unselected), so form.reset() can restore it.
        expect([...select.options].map((option) => [option.value, option.selected])).toEqual([['1', false], ['2', true]]);
        expect(select.options[1].dataset.image).toBe('{"type":"icon","name":"star"}');

        picker.addItem($(select), { id: '3', text: 'Three' });
        picker.removeItem(select, '2');
        expect(picker.getItems(select).map((item) => item.id)).toEqual(['3']);
        expect(changes).toHaveBeenCalledTimes(3);
    });

    it('keeps the options of an enhanced select', () => {
        const select = render('<select id="e1" multiple data-ui-picker><option value="a" selected>A</option><option value="b">B</option></select>');

        picker.setItems(select, [{ id: 'b', text: 'B' }]);

        expect([...select.options].map((option) => [option.value, option.selected])).toEqual([['a', false], ['b', true]]);
    });

    it('keeps a server-selected option when it is removed, so form.reset() restores it', () => {
        const select = render('<select id="f1" multiple data-picker-source="user">'
            + '<option value="a" selected>A</option><option value="b" selected>B</option></select>');

        picker.removeItem(select, 'b');
        expect(picker.getItems(select).map((item) => item.id)).toEqual(['a']);
        expect([...select.options].map((option) => [option.value, option.selected]).sort()).toEqual([['a', true], ['b', false]]);

        select.form.reset();
        expect(picker.getItems(select).map((item) => item.id).sort()).toEqual(['a', 'b']);
    });

    it('appends the chosen options of a picker in the chosen order', () => {
        const select = render('<select id="g1" multiple data-picker-url="/search">'
            + '<option value="1" selected>One</option><option value="2" selected>Two</option></select>');

        picker.setItems(select, [{ id: '2', text: 'Two' }, { id: '3', text: 'Three' }, { id: '1', text: 'One' }]);

        expect([...select.options].map((option) => option.value)).toEqual(['2', '3', '1']);
    });

    it('treats a select with source or url in data-picker-config as picker', () => {
        const select = render('<select id="h1" multiple data-ui-picker data-picker-config=\'{"source":"space"}\'>'
            + '<option value="x">X</option></select>');

        picker.setItems(select, [{ id: 'y', text: 'Y' }]);

        // A picker drops unchosen options (an enhanced select would keep `x`).
        expect([...select.options].map((option) => option.value)).toEqual(['y']);
    });

    it('keeps one item in a single select and empties it when none is chosen', () => {
        const select = render('<select id="i1" data-ui-picker><option value="">-</option>'
            + '<option value="a" selected>A</option><option value="b">B</option></select>');

        picker.setItems(select, [{ id: 'b', text: 'B' }, { id: 'a', text: 'A' }]);
        expect(select.value).toBe('b');
        expect(picker.getItems(select).map((item) => item.id)).toEqual(['b']);

        picker.setItems(select, []);
        expect(select.value).toBe('');
        expect(picker.getItems(select)).toEqual([]);
    });

    it('reads options as the picker does and round-trips image descriptions', () => {
        const select = render('<select id="j1" multiple data-picker-source="topic">'
            + '<option value="1" selected data-text="" data-image=\'{"type":"icon","name":"star"}\'>Shown</option>'
            + '<option value="2" selected data-color="#f00">Two</option>'
            + '<option value="3" selected data-image="{broken">Three</option></select>');

        const items = picker.getItems(select);
        expect(items).toEqual([
            { id: '1', text: '', subText: null, image: { type: 'icon', name: 'star' } },
            { id: '2', text: 'Two', subText: null, image: { type: 'color', color: '#f00' } },
            { id: '3', text: 'Three', subText: null, image: '{broken' },
        ]);

        picker.setItems(select, items.slice(0, 2));
        picker.setItems(select, picker.getItems(select));
        expect(picker.getItems(select).map((item) => item.image)).toEqual([{ type: 'icon', name: 'star' }, { type: 'color', color: '#f00' }]);
    });

    it('selects the current user', () => {
        Object.assign(globalThis.humhub.config.module('user'), { isGuest: false, guid: 'me-guid', text: 'Me', image: '/me.jpg' });
        const select = render('<select id="c1" multiple data-ui-picker data-picker-source="user"></select>');

        picker.selectSelf({ $target: $(select) });

        expect(picker.getItems(select)).toEqual([{ id: 'me-guid', text: 'Me', subText: null, image: '/me.jpg' }]);
    });

    it('fires no change when the selection stays the same', () => {
        const select = render('<select id="k1" multiple data-picker-source="topic"><option value="1" selected>One</option></select>');
        const changes = vi.fn();
        select.addEventListener('change', changes);

        picker.setItems(select, [{ id: '1', text: 'One' }]);
        expect(changes).not.toHaveBeenCalled();
        picker.setItems(select, [{ id: '1', text: 'One' }, { id: '2', text: 'Two' }]);
        expect(changes).toHaveBeenCalledTimes(1);
    });

    it('clears a single picker to its empty option, also when there was none, so the empty value is posted', () => {
        const withPrompt = render('<select id="k2" data-picker-source="icon"><option value=""></option><option value="pencil" selected>pencil</option></select>');
        picker.setItems(withPrompt, []);
        expect(withPrompt.selectedIndex).toBeGreaterThanOrEqual(0);
        expect(withPrompt.value).toBe('');
        expect(new FormData(withPrompt.form).get('')).toBeNull();

        const without = render('<select id="k3" name="icon" data-picker-source="icon"><option value="pencil" selected>pencil</option></select>');
        picker.setItems(without, []);
        expect(new FormData(without.form).get('icon')).toBe('');
    });

    it('replaces the item of a single select on addItem', () => {
        const select = render('<select id="d1" data-picker-source="user"><option value="old" selected>Old</option></select>');

        picker.addItem(select, { id: 'new', text: 'New' });

        expect(picker.getItems(select).map((item) => item.id)).toEqual(['new']);
    });

    it('registers the icon source, which searches the names of the ui.picker config', async () => {
        const factory = vueModule.getPickerSource('icon');
        expect(factory).toBe(picker.iconSource);

        // Names are read on search: set after the source was created.
        const source = factory({ itemKey: 'id', params: { lib: 'ti' } });
        Object.assign(globalThis.humhub.config.module('ui.picker'), { iconNames: { ti: 'pencil,star,star-filled', fa: ['star'] } });

        expect(await source.search('STAR')).toEqual([
            { id: 'star', text: 'star', image: { type: 'icon', name: 'star' } },
            { id: 'star-filled', text: 'star-filled', image: { type: 'icon', name: 'star-filled' } },
        ]);
        expect((await factory({ params: {} }).search('')).map((item) => item.id)).toEqual(['pencil', 'star', 'star-filled']);
        expect(await factory({ params: { lib: 'fa' } }).search('st')).toEqual([{ id: 'star', text: 'star', image: null }]);
        expect(await factory({ params: { lib: 'none' } }).search('')).toEqual([]);
    });
});
