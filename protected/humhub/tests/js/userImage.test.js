import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import UserImage from '../../vue/UserImage.vue';

// UserImage reads `i18n` from `@humhub/vue` for the online-status
// aria-label/title (the same 'UserModule.base' strings CommentEntry.vue used
// to build inline before this component existed) - needs the real
// humhub.vue.js module registered so the @humhub/vue shim (see
// support/humhubVueShim.mjs) has something to delegate to, mirroring
// likeButton.test.js's own setup for the same reason.
await import('../../resources/js/humhub/humhub.vue.js');

const author = (overrides = {}) => ({
    guid: 'user-guid-1',
    displayName: 'Alice',
    url: '/user/alice',
    imageUrl: '/uploads/alice.jpg',
    imageAlt: 'Profile picture of Alice',
    contentContainerId: 5,
    online: null,
    ...overrides,
});

describe('UserImage', () => {
    describe('link (default true)', () => {
        it('renders an anchor to the profile url wrapping the image', () => {
            const wrapper = mount(UserImage, { props: author() });

            const link = wrapper.find('a');
            expect(link.exists()).toBe(true);
            expect(link.attributes('href')).toBe('/user/alice');
            expect(link.find('img').exists()).toBe(true);
        });

        it('renders a bare span (no anchor) when link is false', () => {
            const wrapper = mount(UserImage, { props: author({ link: false }) });

            expect(wrapper.find('a').exists()).toBe(false);
            expect(wrapper.element.tagName).toBe('SPAN');
            expect(wrapper.find('img').exists()).toBe(true);
        });
    });

    describe('image attributes', () => {
        it('sets src, rounded class and width/height style from size (default 25)', () => {
            const wrapper = mount(UserImage, { props: author() });

            const img = wrapper.find('img');
            expect(img.attributes('src')).toBe('/uploads/alice.jpg');
            expect(img.classes()).toContain('rounded');
            expect(img.attributes('style')).toContain('width: 25px');
            expect(img.attributes('style')).toContain('height: 25px');
        });

        it('drives width/height style from a custom size prop', () => {
            const wrapper = mount(UserImage, { props: author({ size: 60 }) });

            const img = wrapper.find('img');
            expect(img.attributes('style')).toContain('width: 60px');
            expect(img.attributes('style')).toContain('height: 60px');
        });

        it('uses the provided imageAlt verbatim', () => {
            const wrapper = mount(UserImage, { props: author({ imageAlt: 'Custom alt text' }) });

            expect(wrapper.find('img').attributes('alt')).toBe('Custom alt text');
        });

        it('falls back to the raw displayName when imageAlt is not provided (no client-side i18n string building)', () => {
            const wrapper = mount(UserImage, { props: author({ imageAlt: null }) });

            expect(wrapper.find('img').attributes('alt')).toBe('Alice');
        });
    });

    describe('popover attr (contentContainerId/guid)', () => {
        it('carries data-contentcontainer-id and data-guid on the image, driving the user popover card', () => {
            const wrapper = mount(UserImage, { props: author({ contentContainerId: 7, guid: 'user-guid-7' }) });

            const img = wrapper.find('img');
            expect(img.attributes('data-contentcontainer-id')).toBe('7');
            expect(img.attributes('data-guid')).toBe('user-guid-7');
        });

        it('omits data-contentcontainer-id when not provided', () => {
            const wrapper = mount(UserImage, { props: author({ contentContainerId: null }) });

            expect(wrapper.find('img').attributes('data-contentcontainer-id')).toBeUndefined();
        });
    });

    describe('online-status indicator', () => {
        it('renders nothing and no has-online-status class when online is null (default)', () => {
            const wrapper = mount(UserImage, { props: author({ online: null }) });

            expect(wrapper.find('.user-online-status').exists()).toBe(false);
            expect(wrapper.find('a').classes()).not.toContain('has-online-status');
        });

        it('renders the online variant with an accessible label', () => {
            const wrapper = mount(UserImage, { props: author({ online: true }) });

            const overlay = wrapper.find('.user-online-status');
            expect(overlay.exists()).toBe(true);
            expect(overlay.classes()).toEqual(expect.arrayContaining(['tt', 'user-online-status', 'user-is-online']));
            expect(overlay.attributes('aria-label')).toBe('Online');
            expect(overlay.attributes('title')).toBe('Online');
            expect(wrapper.find('a').classes()).toContain('has-online-status');
        });

        it('renders the offline variant with an accessible label', () => {
            const wrapper = mount(UserImage, { props: author({ online: false }) });

            const overlay = wrapper.find('.user-online-status');
            expect(overlay.exists()).toBe(true);
            expect(overlay.classes()).toContain('user-is-offline');
            expect(overlay.attributes('aria-label')).toBe('Offline');
            expect(overlay.attributes('title')).toBe('Offline');
        });

        it('applies the has-online-status class to the span wrapper too when link is false', () => {
            const wrapper = mount(UserImage, { props: author({ online: true, link: false }) });

            expect(wrapper.classes()).toContain('has-online-status');
            expect(wrapper.find('.user-online-status').exists()).toBe(true);
        });
    });

    // Bucket thresholds mirror user\widgets\Image::run(): width < 28 -> small,
    // width > 48 -> large, otherwise medium - only observable via the CSS class
    // when the online-status indicator is actually rendered (see _media.scss:
    // img-size-small/img-size-large only mean anything alongside has-online-status).
    describe('size buckets (mirroring user\\widgets\\Image::run())', () => {
        it.each([
            [10, 'img-size-small'],
            [25, 'img-size-small'],
            [27, 'img-size-small'],
            [28, 'img-size-medium'],
            [40, 'img-size-medium'],
            [48, 'img-size-medium'],
            [49, 'img-size-large'],
            [64, 'img-size-large'],
        ])('size %d -> %s', (size, expectedClass) => {
            const wrapper = mount(UserImage, { props: author({ size, online: true }) });

            const classes = wrapper.find('a').classes();
            ['img-size-small', 'img-size-medium', 'img-size-large'].forEach((bucket) => {
                if (bucket === expectedClass) {
                    expect(classes).toContain(bucket);
                } else {
                    expect(classes).not.toContain(bucket);
                }
            });
        });

        it('does not add any size-bucket class when there is no online indicator to size', () => {
            const wrapper = mount(UserImage, { props: author({ size: 10, online: null }) });

            const classes = wrapper.find('a').classes();
            expect(classes).not.toEqual(expect.arrayContaining(['img-size-small', 'img-size-medium', 'img-size-large']));
        });
    });
});
