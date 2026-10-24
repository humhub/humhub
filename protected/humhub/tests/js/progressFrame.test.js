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
        const bar = wrapper.find('.c-progress-frame__bar');

        expect(wrapper.attributes('viewBox')).toBeUndefined();
        expect(bar.attributes('pathLength')).toBe('100');
        expect(bar.attributes('style')).toContain('stroke-dashoffset: 60');
    });

    it('clamps values outside 0..100', () => {
        expect(mount(ProgressFrame, { props: { value: 140 } }).attributes('aria-valuenow')).toBe('100');
        expect(mount(ProgressFrame, { props: { value: -5 } }).attributes('aria-valuenow')).toBe('0');
    });

    it('rounds to whole percent', () => {
        const wrapper = mount(ProgressFrame, { props: { value: 39.6 } });
        const bar = wrapper.find('.c-progress-frame__bar');

        expect(wrapper.attributes('aria-valuenow')).toBe('40');
        expect(bar.attributes('style')).toContain('stroke-dashoffset: 60');
    });
});
