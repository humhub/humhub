import { afterEach, describe, expect, it, vi } from 'vitest';
import { mount } from '@vue/test-utils';
import ColorInput, { normalizeHex } from '../../vue/ColorInput.vue';
import ColorField from '../../vue/ColorField.vue';

// `ColorInput` reads `log` from `@humhub/vue` - needs the real humhub.vue.js module registered so
// the shim (see support/humhubVueShim.mjs) has something to delegate to.
await import('../../resources/js/humhub/humhub.vue.js');

const mounted = [];
/** A form holding the given fixture markup (static test markup, parsed by DOMParser). */
const render = (markup) => {
    const form = document.createElement('form');
    const parsed = new DOMParser().parseFromString(markup, 'text/html');
    form.append(...parsed.body.childNodes);
    document.body.appendChild(form);
    return form;
};
const mountOn = (input) => {
    const host = document.createElement('div');
    input.after(host);
    const wrapper = mount(ColorInput, { props: { inputId: input.id }, attachTo: host });
    mounted.push(wrapper);
    return wrapper;
};
const mountModel = (props = {}, options = {}) => {
    const wrapper = mount(ColorInput, { props: { modelValue: '#21a1b3', ...props }, ...options });
    mounted.push(wrapper);
    return wrapper;
};
const flush = () => new Promise((resolve) => setTimeout(resolve, 0));
/** Types a value: sets it and fires `input` alone (`setValue()` would fire `change` too). */
const type = async (field, value) => {
    field.element.value = value;
    await field.trigger('input');
};

afterEach(() => {
    mounted.splice(0).forEach((wrapper) => wrapper.unmount());
    document.body.replaceChildren();
});

describe('normalizeHex', () => {
    it('expands and lowercases a hex colour, rejects anything else', () => {
        expect(normalizeHex('#ABC')).toBe('#aabbcc');
        expect(normalizeHex(' #21A1B3 ')).toBe('#21a1b3');
        expect(normalizeHex('21a1b3')).toBeNull();
        expect(normalizeHex('#21a1')).toBeNull();
        expect(normalizeHex('')).toBeNull();
    });
});

describe('ColorInput with v-model', () => {
    it('shows the colour in the swatch and the text', () => {
        const wrapper = mountModel();

        expect(wrapper.find('input[type=color]').element.value).toBe('#21a1b3');
        expect(wrapper.find('input[type=text]').element.value).toBe('#21a1b3');
    });

    it('moves the swatch on a valid hex typed and keeps it on an invalid one', async () => {
        const wrapper = mountModel();
        const text = wrapper.find('input[type=text]');

        await type(text, '#abc');
        expect(wrapper.emitted('update:modelValue').at(-1)).toEqual(['#abc']);
        expect(wrapper.find('input[type=color]').element.value).toBe('#aabbcc');

        await type(text, '#ab');
        expect(wrapper.emitted('update:modelValue').at(-1)).toEqual(['#ab']);
        expect(wrapper.find('input[type=color]').element.value).toBe('#aabbcc');
    });

    it('normalizes a valid value on change', async () => {
        const wrapper = mountModel({ modelValue: '#ABC' });

        await wrapper.find('input[type=text]').trigger('change');

        expect(wrapper.find('input[type=text]').element.value).toBe('#aabbcc');
        expect(wrapper.emitted('update:modelValue').at(-1)).toEqual(['#aabbcc']);
        expect(wrapper.emitted('change').at(-1)).toEqual(['#aabbcc']);
    });

    it('writes a pick into the text', async () => {
        const wrapper = mountModel();

        await type(wrapper.find('input[type=color]'), '#ff0000');

        expect(wrapper.find('input[type=text]').element.value).toBe('#ff0000');
        expect(wrapper.emitted('update:modelValue').at(-1)).toEqual(['#ff0000']);
    });

    it('follows the model, the name, the id and the states', async () => {
        const wrapper = mountModel({ name: 'M[c]', id: 'm-c', label: 'Accent', invalid: true, disabled: true, describedBy: 'm-c-hint' });
        const text = wrapper.find('input[type=text]');

        expect(text.attributes('name')).toBe('M[c]');
        expect(text.attributes('id')).toBe('m-c');
        expect(text.attributes('aria-label')).toBe('Accent');
        expect(text.classes()).toContain('is-invalid');
        expect(text.attributes('aria-describedby')).toBe('m-c-hint');
        expect(text.element.disabled).toBe(true);
        expect(wrapper.find('input[type=color]').element.disabled).toBe(true);

        await wrapper.setProps({ modelValue: '#123456' });
        expect(text.element.value).toBe('#123456');
    });
});

describe('ColorInput and the picker', () => {
    it('opens the picker on a click into the text field, not within the focused field', async () => {
        // In the document, so the field can be the active element
        const wrapper = mountModel({}, { attachTo: document.body });
        const text = wrapper.find('input[type=text]');
        const showPicker = vi.fn();
        wrapper.find('input[type=color]').element.showPicker = showPicker;

        await text.trigger('mousedown');
        await text.trigger('click');
        expect(showPicker).toHaveBeenCalledTimes(1);

        text.element.focus();
        await text.trigger('mousedown');
        await text.trigger('click');
        expect(showPicker).toHaveBeenCalledTimes(1);
    });

    it('does not open the picker while disabled or where the browser has no showPicker()', async () => {
        const wrapper = mountModel({ disabled: true });
        const text = wrapper.find('input[type=text]');
        const showPicker = vi.fn();
        wrapper.find('input[type=color]').element.showPicker = showPicker;

        await text.trigger('mousedown');
        await text.trigger('click');
        expect(showPicker).not.toHaveBeenCalled();

        const plain = mountModel();
        delete plain.find('input[type=color]').element.showPicker;
        await plain.find('input[type=text]').trigger('mousedown');
        await plain.find('input[type=text]').trigger('click');
    });
});

describe('ColorInput over a native input', () => {
    it('hides the input, shows its value and states, and takes the label', () => {
        const form = render(`
            <label for="c1">Accent color</label>
            <input id="c1" name="M[c]" value="#21a1b3" class="form-control is-invalid" aria-describedby="c1-hint" data-color-default="#000000">`);
        const native = form.querySelector('#c1');
        const wrapper = mountOn(native);
        const text = wrapper.find('input[type=text]');

        expect(native.classList.contains('visually-hidden')).toBe(true);
        expect(native.getAttribute('tabindex')).toBe('-1');
        expect(native.getAttribute('aria-hidden')).toBe('true');
        expect(text.element.value).toBe('#21a1b3');
        expect(text.attributes('name')).toBeUndefined();
        expect(text.attributes('id')).toBe('c1-hex');
        expect(text.attributes('aria-label')).toBe('Accent color');
        expect(text.classes()).toContain('is-invalid');
        expect(text.attributes('aria-describedby')).toBe('c1-hint');
        expect(wrapper.find('input[type=color]').element.value).toBe('#21a1b3');
    });

    it('writes every change back with input and change events', async () => {
        const form = render('<input id="c2" name="M[c]" value="#21a1b3">');
        const native = form.querySelector('#c2');
        const events = [];
        native.addEventListener('input', () => events.push('input:' + native.value));
        native.addEventListener('change', () => events.push('change:' + native.value));
        const wrapper = mountOn(native);

        await type(wrapper.find('input[type=text]'), '#ABC');
        await wrapper.find('input[type=text]').trigger('change');
        await type(wrapper.find('input[type=color]'), '#ff0000');

        expect(native.value).toBe('#ff0000');
        expect(events).toEqual(['input:#ABC', 'input:#aabbcc', 'change:#aabbcc', 'input:#ff0000']);
    });

    it('follows a value set by a script, a form reset and the attributes', async () => {
        const form = render('<input id="c3" name="M[c]" value="#21a1b3">');
        const native = form.querySelector('#c3');
        const wrapper = mountOn(native);
        const text = wrapper.find('input[type=text]');

        native.value = '#123456';
        native.dispatchEvent(new Event('input', { bubbles: true }));
        await flush();
        expect(text.element.value).toBe('#123456');
        expect(wrapper.find('input[type=color]').element.value).toBe('#123456');

        form.reset();
        await flush();
        expect(text.element.value).toBe('#21a1b3');

        native.disabled = true;
        native.classList.add('is-invalid');
        await flush();
        expect(text.element.disabled).toBe(true);
        expect(text.classes()).toContain('is-invalid');
    });

    it('focuses the text field from the label and restores the input when unmounted', () => {
        const form = render('<label for="c4">Accent</label><input id="c4" name="M[c]" value="#21a1b3" tabindex="2">');
        const native = form.querySelector('#c4');
        const wrapper = mountOn(native);
        const focus = vi.spyOn(wrapper.find('input[type=text]').element, 'focus');

        form.querySelector('label').click();
        expect(focus).toHaveBeenCalled();

        wrapper.unmount();
        mounted.splice(0);
        expect(native.classList.contains('visually-hidden')).toBe(false);
        expect(native.getAttribute('tabindex')).toBe('2');
        expect(native.hasAttribute('aria-hidden')).toBe(false);
    });
});

describe('ColorField', () => {
    it('renders label, hint and the input with the attribute as its name', () => {
        const wrapper = mount(ColorField, { props: { attribute: 'color', label: 'Colour', hint: 'A hex value', modelValue: '#21a1b3' } });
        mounted.push(wrapper);

        expect(wrapper.find('label').text()).toBe('Colour');
        expect(wrapper.find('label').attributes('for')).toBe('color');
        expect(wrapper.find('input[type=text]').attributes('name')).toBe('color');
        expect(wrapper.find('.form-text').text()).toBe('A hex value');
    });
});
