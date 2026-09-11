import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import SpaceImage from '../../modules/space/vue/SpaceImage.vue';

// The serialized space shape (space\serializers\SpaceSerializer::short()).
const space = (overrides = {}) => ({
    id: 7,
    guid: 'space-guid-7',
    name: 'Product Team',
    url: 'https://example.com/s/product-team/',
    color: '#123456',
    imageUrl: null,
    contentContainerId: 42,
    ...overrides,
});

const mountImage = (props = {}) => mount(SpaceImage, { props: { ...space(), ...props } });

describe('SpaceImage', () => {
    it('renders the coloured acronym tile for a space without its own image', () => {
        const wrapper = mountImage();

        const acronym = wrapper.find('.space-acronym');
        expect(acronym.exists()).toBe(true);
        expect(acronym.text()).toBe('PT');
        expect(acronym.classes()).toContain('space-profile-acronym-7');
        expect(acronym.classes()).not.toContain('d-none-space-image');
        expect(acronym.attributes('style')).toContain('background-color: rgb(18, 52, 86)');
        expect(acronym.attributes('data-contentcontainer-id')).toBe('42');
        // No <img> at all rather than a hidden default image.
        expect(wrapper.find('img').exists()).toBe(false);
    });

    it('renders the image and hides the acronym when the space has one', () => {
        const wrapper = mountImage({ imageUrl: 'https://example.com/space.jpg' });

        const img = wrapper.find('img');
        expect(img.exists()).toBe(true);
        expect(img.attributes('src')).toBe('https://example.com/space.jpg');
        expect(img.attributes('alt')).toBe('Product Team');
        expect(img.classes()).toEqual(expect.arrayContaining(['rounded', 'profile-user-photo', 'space-profile-image-7']));
        expect(wrapper.find('.space-acronym').classes()).toContain('d-none-space-image');
    });

    it('falls back to the theme background when the space has no colour', () => {
        const wrapper = mountImage({ color: null });

        expect(wrapper.find('.space-acronym').attributes('style')).toContain('background-color: var(--background3)');
    });

    it('applies the size to both variants and picks the border radius by width', () => {
        expect(mountImage({ width: 20 }).find('.space-acronym').attributes('style'))
            .toContain('border-radius: 2px');
        expect(mountImage({ width: 50 }).find('.space-acronym').attributes('style'))
            .toContain('border-radius: 3px');
        expect(mountImage({ width: 200 }).find('.space-acronym').attributes('style'))
            .toContain('border-radius: 4px');

        const sized = mountImage({ width: 32, imageUrl: 'https://example.com/space.jpg' });
        expect(sized.find('img').attributes('style')).toContain('width: 32px');
        expect(sized.find('img').attributes('style')).toContain('height: 32px');
        expect(mountImage({ width: 32, height: 64 }).find('.space-acronym').attributes('style'))
            .toContain('height: 64px');
    });

    it('derives the acronym like the PHP widget does', () => {
        expect(mountImage({ name: 'Space 2' }).find('.space-acronym').text()).toBe('S2');
        expect(mountImage({ name: '#hash-tag team!' }).find('.space-acronym').text()).toBe('HT');
        expect(mountImage({ name: 'one two three four' }).find('.space-acronym').text()).toBe('OT');
        expect(mountImage({ name: 'one two three', acronymCount: 3 }).find('.space-acronym').text()).toBe('OTT');
        expect(mountImage({ name: '' }).find('.space-acronym').text()).toBe('');
    });

    it('wraps in a link only when asked to', () => {
        expect(mountImage().find('a').exists()).toBe(false);
        expect(mountImage({ link: true }).find('a').attributes('href'))
            .toBe('https://example.com/s/product-team/');
    });
});
