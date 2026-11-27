import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import ProgressFrame from '../../vue/ProgressFrame.vue';

describe('ProgressFrame', () => {
    it('is a progressbar with the value as its state', () => {
        const wrapper = mount(ProgressFrame, { props: { value: 40, label: 'Uploading' } });

        expect(wrapper.attributes('role')).toBe('progressbar');
        expect(wrapper.attributes('aria-valuenow')).toBe('40');
        expect(wrapper.attributes('aria-valuemin')).toBe('0');
        expect(wrapper.attributes('aria-valuemax')).toBe('100');
        expect(wrapper.attributes('aria-label')).toBe('Uploading');
    });

    it('draws the bar as the share of its path length', () => {
        const wrapper = mount(ProgressFrame, { props: { value: 40 } });
        const bar = wrapper.find('.progress-frame-bar');

        expect(wrapper.attributes('viewBox')).toBeUndefined();
        expect(bar.attributes('pathLength')).toBe('100');
        expect(bar.attributes('style')).toContain('stroke-dashoffset: 60');
    });

    it('clamps values outside 0..100', () => {
        expect(mount(ProgressFrame, { props: { value: 140 } }).attributes('aria-valuenow')).toBe('100');
        expect(mount(ProgressFrame, { props: { value: -5 } }).attributes('aria-valuenow')).toBe('0');
    });

    it('runs a segment without a value while indeterminate', () => {
        const wrapper = mount(ProgressFrame, { props: { value: 100, label: 'Processing', indeterminate: true } });
        const bar = wrapper.find('.progress-frame-bar');

        // An indeterminate progressbar has no value (WAI-ARIA), it keeps its role and name
        expect(wrapper.attributes('role')).toBe('progressbar');
        expect(wrapper.attributes('aria-valuenow')).toBeUndefined();
        expect(wrapper.attributes('aria-label')).toBe('Processing');
        expect(wrapper.classes()).toContain('is-indeterminate');
        // The segment runs by its CSS animation, not by the value
        expect(bar.attributes('style')).toBeUndefined();
    });

    it('is determinate by default', () => {
        const wrapper = mount(ProgressFrame, { props: { value: 100 } });

        expect(wrapper.classes()).not.toContain('is-indeterminate');
        expect(wrapper.attributes('aria-valuenow')).toBe('100');
    });

    it('rounds to whole percent', () => {
        const wrapper = mount(ProgressFrame, { props: { value: 39.6 } });
        const bar = wrapper.find('.progress-frame-bar');

        expect(wrapper.attributes('aria-valuenow')).toBe('40');
        expect(bar.attributes('style')).toContain('stroke-dashoffset: 60');
    });
});
