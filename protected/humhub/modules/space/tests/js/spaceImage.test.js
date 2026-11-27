import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import SpaceImage from '../../vue/SpaceImage.vue';

// colorContrast() of @humhub/vue
await import('../../../../resources/js/humhub/humhub.url.js');
await import('../../../../resources/js/humhub/humhub.vue.js');

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

    it('falls back to the theme background and the text colour when the space has no colour', () => {
        const style = mountImage({ color: null }).find('.space-acronym').attributes('style');

        expect(style).toContain('background-color: var(--hh-space-acronym-bg, var(--bs-border-color))');
        expect(style).toContain('color: var(--bs-body-color)');
    });

    it('writes the initials in white or black, whichever is readable on the colour, as the PHP widget', () => {
        // White reaches 4.5:1 on a dark colour, only 4.08:1 on this teal
        expect(mountImage({ color: '#123456' }).find('.space-acronym').attributes('style')).toMatch(/(^|; )color: rgb\(255, 255, 255\)/);
        expect(mountImage({ color: '#028c8e' }).find('.space-acronym').attributes('style')).toMatch(/(^|; )color: rgb\(0, 0, 0\)/);
    });

    it('applies the size to both variants and picks the border radius by width', () => {
        expect(mountImage({ width: 20 }).find('.space-acronym').attributes('style'))
            .toContain('border-radius: 2px');
        expect(mountImage({ width: 50 }).find('.space-acronym').attributes('style'))
            .toContain('border-radius: 3px');
        expect(mountImage({ width: 200 }).find('.space-acronym').attributes('style'))
            .toContain('border-radius: 4px');

        const sized = mountImage({ width: 32, imageUrl: 'https://example.com/space.jpg' });
        expect(sized.find('img').attributes('style')).toContain('width: var(--hh-content-container-image-width, 32px)');
        expect(sized.find('img').attributes('style')).toContain('height: var(--hh-content-container-image-height, 32px)');
        expect(mountImage({ width: 32, height: 64 }).find('.space-acronym').attributes('style'))
            .toContain('height: var(--hh-content-container-image-height, 64px)');
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

    it('hides the initials from screen readers and names the link by the space', () => {
        const wrapper = mountImage({ link: true });

        expect(wrapper.find('.space-acronym').attributes('aria-hidden')).toBe('true');
        expect(wrapper.find('a').attributes('aria-label')).toBe('Product Team');
        expect(mountImage().find('span').attributes('aria-label')).toBeUndefined();
    });
});
