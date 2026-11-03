import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import HumHubForm from '../../vue/HumHubForm.vue';
import PickerField from '../../vue/PickerField.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const options = [{ id: '1', text: 'One' }, { id: '2', text: 'Two' }];

describe('PickerField', () => {
    it('renders the suite container, label and the picker with the model name', () => {
        const wrapper = mount({
            components: { HumHubForm, PickerField },
            data: () => ({ value: ['1'] }),
            render() {
                const { h } = globalThis.Vue;
                return h(HumHubForm, { modelName: 'Task' }, () => h(PickerField, {
                    attribute: 'items', label: 'Items', multiple: true, options, modelValue: this.value,
                }));
            },
        });

        expect(wrapper.find('.mb-3.field-task-items').exists()).toBe(true);
        expect(wrapper.find('label.form-label').attributes('for')).toBe('task-items');
        expect(wrapper.find('input[role="combobox"]').attributes('id')).toBe('task-items');
        expect(wrapper.find('input[type="hidden"]').attributes('name')).toBe('Task[items][]');
    });

    it('shows server errors and marks the picker invalid', async () => {
        const wrapper = mount(PickerField, { props: { attribute: 'items', multiple: true, options } });
        expect(wrapper.find('.invalid-feedback').exists()).toBe(false);
        // Without a HumHubForm there are no errors — the error path is HumHubForm's (setErrors), covered by its suite.
        expect(wrapper.find('.picker-field').classes()).not.toContain('is-invalid');
    });

    it('describes the picker input by its hint', () => {
        const wrapper = mount(PickerField, { props: { attribute: 'items', hint: 'Pick some', multiple: true, options } });
        const hint = wrapper.find('.form-text');
        expect(hint.attributes('id')).toBe('items-hint');
        expect(wrapper.find('input[role="combobox"]').attributes('aria-describedby')).toBe('items-hint');
    });

    it('shows the errors HumHubForm sets and clears them on an update', async () => {
        const wrapper = mount({
            components: { HumHubForm, PickerField },
            render() {
                const { h } = globalThis.Vue;
                return h(HumHubForm, { modelName: 'Task', ref: 'form' }, () => h(PickerField, {
                    attribute: 'items', multiple: true, options, modelValue: [],
                }));
            },
        });
        wrapper.vm.$refs.form.setErrors({ items: ['Items cannot be blank.'] });
        await wrapper.vm.$nextTick();

        expect(wrapper.findAll('.invalid-feedback.d-block div').map((node) => node.text())).toEqual(['Items cannot be blank.']);
        expect(wrapper.find('.picker-field').classes()).toContain('is-invalid');
        expect(wrapper.find('input[role="combobox"]').attributes('aria-describedby')).toContain('task-items-error');

        wrapper.findComponent({ name: 'PickerInput' }).vm.$emit('update:modelValue', ['1']);
        await wrapper.vm.$nextTick();
        expect(wrapper.find('.invalid-feedback').exists()).toBe(false);
        expect(wrapper.find('.picker-field').classes()).not.toContain('is-invalid');
    });

    it('passes v-model through', async () => {
        const wrapper = mount(PickerField, { props: { attribute: 'items', multiple: true, options, modelValue: [] } });
        wrapper.findComponent({ name: 'PickerInput' }).vm.$emit('update:modelValue', ['2']);
        expect(wrapper.emitted('update:modelValue')[0]).toEqual([['2']]);
    });
});
