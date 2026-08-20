import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import { h } from 'vue';
import HumHubForm from '../../vue/HumHubForm.vue';
import TextField from '../../vue/TextField.vue';
import TextareaField from '../../vue/TextareaField.vue';
import CheckboxField from '../../vue/CheckboxField.vue';
import SelectField from '../../vue/SelectField.vue';
import SubmitButton from '../../vue/SubmitButton.vue';

// SubmitButton reads `i18n` from `@humhub/vue` for its busy-loader label - needs the real
// humhub.vue.js module registered so the @humhub/vue shim (see support/humhubVueShim.mjs)
// has something to delegate to, mirroring userImage.test.js's own setup for the same reason.
await import('../../resources/js/humhub/humhub.vue.js');

/** Mounts a `HumHubForm` with the given field vnodes as its default slot content. */
const mountForm = (props, fields, options = {}) => mount(HumHubForm, {
    props,
    slots: { default: () => fields },
    ...options,
});

describe('HumHubForm', () => {
    describe('setErrors()', () => {
        it('accepts the plain {attribute: [messages]} shape', () => {
            const wrapper = mountForm({}, []);
            wrapper.vm.setErrors({ title: ['Title cannot be blank.'] });
            expect(wrapper.vm.errors).toEqual({ title: ['Title cannot be blank.'] });
        });

        it('unwraps the {errors: {...}} envelope', () => {
            const wrapper = mountForm({}, []);
            wrapper.vm.setErrors({ status: 422, errors: { title: ['Title cannot be blank.'] } });
            expect(wrapper.vm.errors).toEqual({ title: ['Title cannot be blank.'] });
        });

        it('unwraps the {error: {errors: {...}}} envelope', () => {
            const wrapper = mountForm({}, []);
            wrapper.vm.setErrors({ error: { errors: { title: ['Title cannot be blank.'] } } });
            expect(wrapper.vm.errors).toEqual({ title: ['Title cannot be blank.'] });
        });

        it('replaces (does not merge with) whatever errors were set before', () => {
            const wrapper = mountForm({}, []);
            wrapper.vm.setErrors({ title: ['first'] });
            wrapper.vm.setErrors({ body: ['second'] });
            expect(wrapper.vm.errors).toEqual({ body: ['second'] });
        });

        it('clears (rather than corrupts) the error map when given a non-object payload, once unwrapped', () => {
            const wrapper = mountForm({}, []);
            wrapper.vm.setErrors({ title: ['first'] });
            wrapper.vm.setErrors('oops');
            expect(wrapper.vm.errors).toEqual({});
        });

        it('also clears on an array or null payload (same non-plain-object guard)', () => {
            const wrapper = mountForm({}, []);
            wrapper.vm.setErrors({ title: ['first'] });
            wrapper.vm.setErrors(['oops']);
            expect(wrapper.vm.errors).toEqual({});

            wrapper.vm.setErrors({ title: ['first'] });
            wrapper.vm.setErrors(null);
            expect(wrapper.vm.errors).toEqual({});
        });
    });

    describe('form-level error summary (unowned errors)', () => {
        it('renders the message of an attribute with no registered field', async () => {
            const wrapper = mountForm({}, []);
            wrapper.vm.setErrors({ phantom: ['nope'] });
            await wrapper.vm.$nextTick();

            const summary = wrapper.find('.error-summary');
            expect(summary.exists()).toBe(true);
            expect(summary.text()).toContain('nope');
        });

        it('does not duplicate a registered field\'s error into the summary', async () => {
            const wrapper = mountForm({}, [h(TextField, { attribute: 'title', modelValue: '' })]);
            wrapper.vm.setErrors({ title: ['Title cannot be blank.'] });
            await wrapper.vm.$nextTick();

            expect(wrapper.find('.error-summary').exists()).toBe(false);
            expect(wrapper.find('.invalid-feedback').exists()).toBe(true);
        });

        it('renders only the unowned message when both an owned and an unowned attribute are in error', async () => {
            const wrapper = mountForm({}, [h(TextField, { attribute: 'title', modelValue: '' })]);
            wrapper.vm.setErrors({ title: ['Title cannot be blank.'], phantom: ['nope'] });
            await wrapper.vm.$nextTick();

            const summary = wrapper.find('.error-summary');
            expect(summary.exists()).toBe(true);
            expect(summary.text()).toContain('nope');
            expect(summary.text()).not.toContain('Title cannot be blank.');
        });
    });

    describe('clearErrors()', () => {
        it('empties the error map', () => {
            const wrapper = mountForm({}, []);
            wrapper.vm.setErrors({ title: ['Title cannot be blank.'] });
            wrapper.vm.clearErrors();
            expect(wrapper.vm.errors).toEqual({});
        });
    });

    describe('submit', () => {
        it('emits "submit" on native form submission and never performs an HTTP call itself', async () => {
            const wrapper = mountForm({}, []);
            await wrapper.find('form').trigger('submit');
            expect(wrapper.emitted('submit')).toHaveLength(1);
        });
    });

    describe('focusFirstError()', () => {
        it('focuses the first-REGISTERED erroring field, not the first key in the errors object', () => {
            // Focus only ever moves document.activeElement for an element actually
            // connected to the live document - attachTo is required here.
            const wrapper = mountForm({}, [
                h(TextField, { attribute: 'title', modelValue: '' }),
                h(TextField, { attribute: 'body', modelValue: '' }),
            ], { attachTo: document.body });

            // 'body' listed first in the payload - registration (template) order must win.
            wrapper.vm.setErrors({ body: ['Body cannot be blank.'], title: ['Title cannot be blank.'] });
            wrapper.vm.focusFirstError();

            const titleInput = wrapper.findAll('input').find((input) => input.attributes('name') === 'title');
            expect(titleInput.element).toBe(document.activeElement);
            wrapper.unmount();
        });

        it('is a no-op when no registered field is in error', () => {
            const wrapper = mountForm({}, [h(TextField, { attribute: 'title', modelValue: '' })], { attachTo: document.body });
            expect(() => wrapper.vm.focusFirstError()).not.toThrow();
            expect(document.activeElement).not.toBe(wrapper.find('input').element);
            wrapper.unmount();
        });

        it('stops targeting a field once it unmounts (unregisterField)', async () => {
            const wrapper = mount({
                data() {
                    return { showTitle: true };
                },
                render() {
                    return h(HumHubForm, { ref: 'form' }, {
                        default: () => (this.showTitle ? [h(TextField, { attribute: 'title', modelValue: '' })] : []),
                    });
                },
            });

            wrapper.vm.$refs.form.setErrors({ title: ['Title cannot be blank.'] });
            wrapper.vm.showTitle = false;
            await wrapper.vm.$nextTick();

            expect(() => wrapper.vm.$refs.form.focusFirstError()).not.toThrow();
        });
    });

    describe('busy reactivity', () => {
        it('reflects a live busy prop change into every nested field', async () => {
            const wrapper = mountForm({ busy: false }, [h(TextField, { attribute: 'title', modelValue: '' })]);
            expect(wrapper.find('input').attributes('disabled')).toBeUndefined();

            await wrapper.setProps({ busy: true });
            expect(wrapper.find('input').attributes('disabled')).toBeDefined();
        });
    });
});

describe('TextField', () => {
    it('renders label/hint/name/id/classes matching the ActiveForm reference markup', () => {
        const wrapper = mount(TextField, {
            props: { attribute: 'title', label: 'Title', hint: 'Enter a title', modelValue: '' },
        });

        expect(wrapper.classes()).toContain('mb-3');
        expect(wrapper.classes()).toContain('field-title');

        const label = wrapper.find('label');
        expect(label.text()).toBe('Title');
        expect(label.classes()).toContain('form-label');
        expect(label.attributes('for')).toBe('title');

        const input = wrapper.find('input');
        expect(input.attributes('id')).toBe('title');
        expect(input.attributes('name')).toBe('title'); // no modelName -> bare attribute (unhosted)
        expect(input.attributes('type')).toBe('text');
        expect(input.classes()).toContain('form-control');

        const hint = wrapper.find('.form-text');
        expect(hint.text()).toBe('Enter a title');
        expect(hint.classes()).toContain('text-muted');
    });

    it('prefixes name/id with Model[attribute] when hosted under a HumHubForm modelName', () => {
        const wrapper = mountForm({ modelName: 'Comment' }, [h(TextField, { attribute: 'title', modelValue: '' })]);

        const input = wrapper.find('input');
        expect(input.attributes('name')).toBe('Comment[title]');
        expect(input.attributes('id')).toBe('comment-title'); // mirrors Html::getInputIdByName()
    });

    it('supports the type prop (email/password/number/...)', () => {
        const wrapper = mount(TextField, { props: { attribute: 'age', modelValue: '', type: 'number' } });
        expect(wrapper.find('input').attributes('type')).toBe('number');
    });

    it('v-model: typing emits update:modelValue', async () => {
        const wrapper = mount(TextField, { props: { attribute: 'title', modelValue: 'a' } });
        await wrapper.find('input').setValue('b');
        expect(wrapper.emitted('update:modelValue')).toEqual([['b']]);
    });

    it('renders every error message with is-invalid/aria-invalid/aria-describedby, and clears on input', async () => {
        const wrapper = mountForm({}, [h(TextField, { attribute: 'title', modelValue: 'x' })]);
        wrapper.vm.setErrors({ title: ['Title cannot be blank.', 'Title is too short.'] });
        await wrapper.vm.$nextTick();

        const input = wrapper.find('input');
        expect(input.classes()).toContain('is-invalid');
        expect(input.attributes('aria-invalid')).toBe('true');
        expect(input.attributes('aria-describedby')).toBe('title-error');

        const messages = wrapper.findAll('.invalid-feedback div').map((el) => el.text());
        expect(messages).toEqual(['Title cannot be blank.', 'Title is too short.']);

        await input.setValue('y');
        expect(wrapper.vm.errors).toEqual({});
        await wrapper.vm.$nextTick();
        expect(wrapper.find('input').classes()).not.toContain('is-invalid');
        expect(wrapper.find('.invalid-feedback').exists()).toBe(false);
    });

    it('combines aria-describedby from both hint and error when both are present', async () => {
        const wrapper = mountForm({}, [h(TextField, { attribute: 'title', modelValue: '', hint: 'A hint' })]);
        wrapper.vm.setErrors({ title: ['Title cannot be blank.'] });
        await wrapper.vm.$nextTick();

        expect(wrapper.find('input').attributes('aria-describedby')).toBe('title-hint title-error');
    });

    it('disables the input while the form is busy, on top of its own disabled prop', () => {
        const busyWrapper = mountForm({ busy: true }, [h(TextField, { attribute: 'title', modelValue: '' })]);
        expect(busyWrapper.find('input').attributes('disabled')).toBeDefined();

        const disabledWrapper = mount(TextField, { props: { attribute: 'title', modelValue: '', disabled: true } });
        expect(disabledWrapper.find('input').attributes('disabled')).toBeDefined();
    });

    it('adds the required wrapper class and aria-required (visual marker only)', () => {
        const wrapper = mount(TextField, { props: { attribute: 'title', modelValue: '', required: true } });
        expect(wrapper.classes()).toContain('required');
        expect(wrapper.find('input').attributes('aria-required')).toBe('true');
    });

    it('focus() focuses the underlying input', () => {
        const wrapper = mount(TextField, { props: { attribute: 'title', modelValue: '' }, attachTo: document.body });
        wrapper.vm.focus();
        expect(wrapper.find('input').element).toBe(document.activeElement);
        wrapper.unmount();
    });
});

describe('TextareaField', () => {
    it('renders a textarea with rows, name/id parity and working v-model', async () => {
        const wrapper = mount(TextareaField, { props: { attribute: 'body', modelValue: 'hi', rows: 6 } });

        const textarea = wrapper.find('textarea');
        expect(textarea.attributes('rows')).toBe('6');
        expect(textarea.attributes('name')).toBe('body');
        expect(textarea.classes()).toContain('form-control');
        expect(textarea.element.value).toBe('hi');

        await textarea.setValue('bye');
        expect(wrapper.emitted('update:modelValue')).toEqual([['bye']]);
    });

    it('renders errors the same way TextField does (shared template order: label, input, hint, error)', async () => {
        const wrapper = mountForm({}, [h(TextareaField, { attribute: 'body', modelValue: '', hint: 'A hint' })]);
        wrapper.vm.setErrors({ body: ['Body cannot be blank.'] });
        await wrapper.vm.$nextTick();

        const children = [...wrapper.find('.mb-3').element.children];
        const tagOrder = children.map((el) => el.tagName);
        expect(tagOrder).toEqual(['TEXTAREA', 'DIV', 'DIV']); // hint then error
        expect(children[1].className).toContain('form-text');
        expect(children[2].className).toContain('invalid-feedback');
    });
});

describe('CheckboxField', () => {
    it('renders a form-check checkbox bound to modelValue via v-model', async () => {
        const wrapper = mount(CheckboxField, { props: { attribute: 'accept', label: 'Accept terms', modelValue: false } });

        const input = wrapper.find('input[type="checkbox"]');
        expect(input.classes()).toContain('form-check-input');
        expect(input.element.checked).toBe(false);

        const label = wrapper.find('label');
        expect(label.text()).toBe('Accept terms');
        expect(label.classes()).toContain('form-check-label');

        await input.setValue(true);
        expect(wrapper.emitted('update:modelValue')).toEqual([[true]]);
    });

    it('orders error BEFORE hint, matching ActiveField::$checkTemplate (unlike every other field)', async () => {
        const wrapper = mountForm({}, [h(CheckboxField, { attribute: 'accept', modelValue: false, hint: 'Required by law' })]);
        wrapper.vm.setErrors({ accept: ['Accept must be checked.'] });
        await wrapper.vm.$nextTick();

        const children = [...wrapper.find('.form-check').element.children].map((el) => el.className);
        const errorIndex = children.findIndex((c) => c.includes('invalid-feedback'));
        const hintIndex = children.findIndex((c) => c.includes('form-text'));
        expect(errorIndex).toBeGreaterThan(-1);
        expect(hintIndex).toBeGreaterThan(errorIndex);
    });
});

describe('SelectField', () => {
    const options = [{ value: 'a', label: 'Option A' }, { value: 'b', label: 'Option B' }];

    it('renders form-select with a prompt option first, and v-model works', async () => {
        const wrapper = mount(SelectField, {
            props: { attribute: 'kind', modelValue: 'a', options, prompt: 'Please select' },
        });

        const select = wrapper.find('select');
        expect(select.classes()).toContain('form-select');
        expect(wrapper.findAll('option').map((option) => option.text())).toEqual(['Please select', 'Option A', 'Option B']);
        expect(select.element.value).toBe('a');

        await select.setValue('b');
        expect(wrapper.emitted('update:modelValue')).toEqual([['b']]);
    });

    it('omits the prompt option when none is given', () => {
        const wrapper = mount(SelectField, { props: { attribute: 'kind', modelValue: 'a', options } });
        expect(wrapper.findAll('option')).toHaveLength(2);
    });
});

describe('SubmitButton', () => {
    it('is a type="submit" button rendering its default slot when not busy', () => {
        const wrapper = mount(SubmitButton, { slots: { default: 'Save' } });
        expect(wrapper.attributes('type')).toBe('submit');
        expect(wrapper.text()).toBe('Save');
        expect(wrapper.attributes('disabled')).toBeUndefined();
    });

    it('disables via its own disabled prop even outside a HumHubForm', () => {
        const wrapper = mount(SubmitButton, { props: { disabled: true }, slots: { default: 'Save' } });
        expect(wrapper.attributes('disabled')).toBeDefined();
    });

    it('disables and swaps in the loader spinner while the injected form is busy (default)', async () => {
        const wrapper = mountForm({ busy: true }, [h(SubmitButton, {}, { default: () => 'Save' })]);

        const button = wrapper.find('button');
        expect(button.attributes('disabled')).toBeDefined();
        expect(button.find('.spinner-border').exists()).toBe(true);
        expect(button.text()).not.toContain('Save');
    });

    it('keeps the slot content unchanged (only disabling) while busy when loader is false', () => {
        const wrapper = mountForm({ busy: true }, [h(SubmitButton, { loader: false }, { default: () => 'Save' })]);

        const button = wrapper.find('button');
        expect(button.attributes('disabled')).toBeDefined();
        expect(button.find('.spinner-border').exists()).toBe(false);
        expect(button.text()).toBe('Save');
    });
});
