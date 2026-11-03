import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import PickerInput from '../../vue/PickerInput.vue';
import { readConfig, readSelection, writeSelection } from '../../vue/picker/nativeSelect.js';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const mounted = [];
const render = (html) => {
    const form = document.createElement('form');
    form.innerHTML = html;
    document.body.appendChild(form);
    return form;
};
const mountOn = (select) => {
    const host = document.createElement('div');
    host.className = 'picker-host';
    select.after(host);
    const wrapper = mount(PickerInput, { props: { selectId: select.id }, attachTo: host });
    mounted.push(wrapper);
    return wrapper;
};
const selected = (select) => [...select.options].filter((option) => option.selected).map((option) => option.value);

describe('nativeSelect', () => {
    beforeEach(() => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve([]));
    });

    afterEach(() => {
        mounted.splice(0).forEach((wrapper) => wrapper.unmount());
        document.body.innerHTML = '';
    });

    it('reads settings from data-picker-* and data-picker-config', () => {
        const form = render(`
            <label for="p1">People</label>
            <select id="p1" name="M[p][]" multiple data-ui-picker data-picker-url="/search" data-picker-max-selection="3"
                    data-picker-allow-new data-picker-placeholder="Add people" data-picker-item-key="guid"
                    data-picker-config='{"lockedIds":["g1"],"messages":{"locked":"No"},"maxSelection":9}'>
                <option value="g1" selected data-text="Anna" data-sub-text="PM" data-image="/a.jpg">Anna</option>
            </select>`);
        const config = readConfig(form.querySelector('select'));

        expect(config).toMatchObject({
            multiple: true, url: '/search', maxSelection: 3, allowNew: true, placeholder: 'Add people', itemKey: 'guid',
            lockedIds: ['g1'], messages: { locked: 'No' }, label: 'People', options: [], name: null,
        });
        expect(config.initialItems).toEqual([{ id: 'g1', text: 'Anna', subText: 'PM', image: '/a.jpg', disabled: false, locked: false, group: null }]);
        expect(config.modelValue).toEqual(['g1']);
    });

    it('reads an enhanced select: all options, groups, empty option, alias attributes', () => {
        const form = render(`
            <select id="s1" name="M[s]" data-ui-select2 data-minimum-results-for-search="-1" data-ui-select2-allow-new>
                <option value="">Please select</option>
                <optgroup label="G"><option value="a" data-color="#f00">A</option></optgroup>
                <option value="b" selected disabled>B</option>
            </select>`);
        const config = readConfig(form.querySelector('select'));

        expect(config.search).toBe(-1);
        expect(config.allowNew).toBe(true);
        expect(config.allowClear).toBe(true);
        expect(config.options.map((option) => [option.id, option.group, option.disabled])).toEqual([['', null, false], ['a', 'G', false], ['b', null, true]]);
        expect(config.options[1].image).toEqual({ type: 'color', color: '#f00' });
        expect(config.modelValue).toBe('b');
    });

    it('hides the select visually when mounted and restores it when unmounted', () => {
        const form = render('<select id="h1" data-ui-picker autofocus><option value="x">X</option></select>');
        const select = form.querySelector('select');
        const wrapper = mountOn(select);
        expect(select.hidden).toBe(false);
        expect(select.classList.contains('visually-hidden')).toBe(true);
        expect(select.getAttribute('tabindex')).toBe('-1');
        expect(select.getAttribute('aria-hidden')).toBe('true');
        expect(select.hasAttribute('autofocus')).toBe(false);
        expect(wrapper.find('input.picker-input').attributes('autofocus')).toBeDefined();
        wrapper.unmount();
        mounted.length = 0;
        expect(select.classList.contains('visually-hidden')).toBe(false);
        expect(select.hasAttribute('tabindex')).toBe(false);
        expect(select.hasAttribute('aria-hidden')).toBe(false);
        expect(select.hasAttribute('autofocus')).toBe(true);
    });

    it('writes a choice back as selected option and fires change', async () => {
        const form = render(`<select id="w1" name="M[f][]" multiple data-ui-picker data-picker-url="/search"></select>`);
        const select = form.querySelector('select');
        const changes = [];
        select.addEventListener('change', () => changes.push(selected(select)));
        const wrapper = mountOn(select);

        wrapper.vm.choose({ id: '5', text: 'Five', subText: null, image: { type: 'icon', name: 'star' }, disabled: false, locked: false });
        expect(selected(select)).toEqual(['5']);
        expect(select.options[0].dataset.text).toBe('Five');
        expect(JSON.parse(select.options[0].dataset.image)).toEqual({ type: 'icon', name: 'star' });
        expect(changes).toEqual([['5']]);

        wrapper.vm.remove('5');
        expect(select.options).toHaveLength(0);
    });

    it('keeps the options of an enhanced select and only moves the selection', () => {
        const form = render('<select id="w2" data-ui-picker><option value="a">A</option><option value="b">B</option></select>');
        const select = form.querySelector('select');
        const wrapper = mountOn(select);
        wrapper.vm.choose({ id: 'b', text: 'B', subText: null, image: null, disabled: false, locked: false });
        expect(select.value).toBe('b');
        expect(select.options).toHaveLength(2);
    });

    it('follows a change made from outside, and a form reset', async () => {
        const form = render('<select id="w3" multiple data-ui-picker><option value="a" selected>A</option><option value="b">B</option></select>');
        const select = form.querySelector('select');
        const wrapper = mountOn(select);

        select.options[1].selected = true;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        await wrapper.vm.$nextTick();
        expect(wrapper.findAll('.picker-chip-label').map((node) => node.text())).toEqual(['A', 'B']);

        form.reset();
        form.dispatchEvent(new Event('reset'));
        await new Promise((resolve) => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();
        expect(wrapper.findAll('.picker-chip-label').map((node) => node.text())).toEqual(['A']);
    });

    it('mirrors disabled and is-invalid of the select', async () => {
        const form = render('<select id="w4" data-ui-picker><option value="a">A</option></select>');
        const select = form.querySelector('select');
        const wrapper = mountOn(select);

        select.disabled = true;
        select.classList.add('is-invalid');
        await new Promise((resolve) => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();
        expect(wrapper.find('input').attributes('disabled')).toBeDefined();
        expect(wrapper.find('.picker-field').classes()).toContain('is-invalid');
    });

    it('describes the input by what describes the select (hint, error), also when that changes', async () => {
        const form = render('<select id="w5" aria-describedby="w5-hint" data-ui-picker><option value="a">A</option></select>');
        const select = form.querySelector('select');
        const wrapper = mountOn(select);
        expect(wrapper.find('input').attributes('aria-describedby')).toBe('w5-hint');

        select.setAttribute('aria-describedby', 'w5-hint w5-error');
        await new Promise((resolve) => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();
        expect(wrapper.find('input').attributes('aria-describedby')).toBe('w5-hint w5-error');
    });

    it('reads the selection in option order', () => {
        const form = render('<select id="r1" multiple><option value="a">A</option><option value="b" selected data-locked>B</option></select>');
        expect(readSelection(form.querySelector('select')).map((item) => [item.id, item.locked])).toEqual([['b', true]]);
    });
    it('follows disabled and is-invalid set from the first render', async () => {
        const form = render('<select id="d1" disabled class="is-invalid" data-ui-picker><option value="a">A</option></select>');
        const select = form.querySelector('select');
        const wrapper = mountOn(select);
        expect(wrapper.find('input').attributes('disabled')).toBeDefined();
        expect(wrapper.find('.picker-field').classes()).toContain('is-invalid');

        select.disabled = false;
        select.classList.remove('is-invalid');
        await new Promise((resolve) => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();
        expect(wrapper.find('input').attributes('disabled')).toBeUndefined();
        expect(wrapper.find('.picker-field').classes()).not.toContain('is-invalid');
    });

    it('re-reads the options of an enhanced select on a change from outside', async () => {
        const form = render('<select id="o1" data-ui-picker><option value="a">A</option></select>');
        const select = form.querySelector('select');
        const wrapper = mountOn(select);

        select.innerHTML = '<option value="x">X</option><option value="y" selected>Y</option>';
        select.dispatchEvent(new Event('change', { bubbles: true }));
        await wrapper.vm.$nextTick();
        await wrapper.find('.picker-field').trigger('click');
        expect(wrapper.findAll('.picker-option-label').map((node) => node.text())).toEqual(['X', 'Y']);
        expect(wrapper.find('input').element.value).toBe('Y');
    });

    it('follows a jQuery triggered change', async () => {
        const form = render('<select id="j1" multiple data-ui-picker><option value="a">A</option><option value="b">B</option></select>');
        const select = form.querySelector('select');
        const wrapper = mountOn(select);

        globalThis.jQuery(select).val(['a', 'b']).trigger('change');
        await wrapper.vm.$nextTick();
        expect(wrapper.findAll('.picker-chip-label').map((node) => node.text())).toEqual(['A', 'B']);
    });

    it('restores the initial items of a picker on form reset', async () => {
        const form = render(`<select id="rs1" name="M[f][]" multiple data-ui-picker data-picker-url="/search">
            <option value="a" selected>A</option><option value="b" selected>B</option></select>`);
        const select = form.querySelector('select');
        const wrapper = mountOn(select);

        wrapper.vm.remove('b');
        wrapper.vm.choose({ id: 'c', text: 'C', subText: null, image: null, disabled: false, locked: false });
        expect(selected(select)).toEqual(['a', 'c']);

        form.reset();
        form.dispatchEvent(new Event('reset'));
        await new Promise((resolve) => setTimeout(resolve, 0));
        await wrapper.vm.$nextTick();
        expect(wrapper.findAll('.picker-chip-label').map((node) => node.text())).toEqual(['A', 'B']);
        expect([...select.options].map((option) => option.value)).not.toContain('c');
    });

    it('names the picker after a label wrapping the select, without the options', () => {
        const form = render('<label>Wrapper <select id="l1" data-ui-picker><option value="a">A</option></select> text</label>');
        const select = form.querySelector('select');
        expect(readConfig(select).label).toBe('Wrapper text');
        const wrapper = mountOn(select);
        expect(wrapper.find('input[role="combobox"]').attributes('aria-label')).toBe('Wrapper text');
    });

    it('keeps a click inside the picker from reaching the select of a wrapping label', () => {
        const form = render('<label>Wrapper <select id="l2" data-ui-picker><option value="a">A</option></select></label>');
        const select = form.querySelector('select');
        const wrapper = mountOn(select);
        const event = new MouseEvent('click', { bubbles: true, cancelable: true });
        wrapper.find('.picker-field').element.dispatchEvent(event);
        expect(event.defaultPrevented).toBe(true);
        expect(document.activeElement).not.toBe(select);
    });

    it('leaves out empty and invalid number attributes', () => {
        const form = render('<select id="n1" data-picker-max-selection="" data-picker-min-input="abc" data-picker-suggested="4"></select>');
        const config = readConfig(form.querySelector('select'));
        expect(config).not.toHaveProperty('maxSelection');
        expect(config).not.toHaveProperty('minInput');
        expect(config.suggested).toBe(4);
    });

    it('writes a cleared single picker as its empty option and stays quiet without a change', () => {
        const form = render('<select id="w1" name="icon"><option value="pencil" selected>pencil</option></select>');
        const select = form.querySelector('select');
        const changes = vi.fn();
        select.addEventListener('change', changes);

        writeSelection(select, [{ id: 'pencil', text: 'pencil' }], { prune: true });
        expect(changes).not.toHaveBeenCalled();

        writeSelection(select, [], { prune: true });
        expect(new FormData(form).get('icon')).toBe('');
        expect(changes).toHaveBeenCalledTimes(1);
    });
});
