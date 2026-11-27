import { afterEach, describe, expect, it, vi } from 'vitest';
import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import AttachedMedia from '../../vue/components/AttachedMedia.vue';

await import('../../../../resources/js/humhub/humhub.vue.js');

vi.stubGlobal('ResizeObserver', class {
    observe() {}
    unobserve() {}
});

const media = (guid, overrides = {}) => ({
    guid, fileName: `${guid}.mp4`, url: `/download/${guid}`, previewUrl: null, width: null, height: null, ...overrides,
});
const image = (guid) => media(guid, { fileName: `${guid}.jpg`, previewUrl: `/preview/${guid}`, width: 300, height: 300 });

const render = (items) => mount(AttachedMedia, { props: { media: items, galleryId: 'gallery-1' } });
const style = (wrapper) => wrapper.find('a.post-files-video').attributes('style');

// Before the area is measured the layout assumes 576px width and 192px rows
describe('AttachedMedia video tile', () => {
    afterEach(() => {
        vi.stubGlobal('IntersectionObserver', undefined);
        vi.restoreAllMocks();
    });

    it('takes the ratio from the shape, then from the measured frame, then 16:9', async () => {
        // 2:1 at the 320px single height would be 640px, the area is 576px wide
        expect(style(render([media('a', { width: 400, height: 200 })]))).toBe('width: 576px;');
        // 16:9 at the 320px single height is 568.9px, it fits
        expect(style(render([media('b')]))).toBe(`width: ${(320 * 16) / 9}px;`);

        const wrapper = render([media('c')]);
        const video = wrapper.find('video').element;
        Object.defineProperty(video, 'videoWidth', { value: 300 });
        Object.defineProperty(video, 'videoHeight', { value: 600 });
        await wrapper.find('video').trigger('loadedmetadata');
        expect(style(wrapper)).toBe('width: 160px;');

        // The shape wins over the measured ratio
        const known = render([media('d', { width: 100, height: 100 })]);
        Object.defineProperty(known.find('video').element, 'videoWidth', { value: 300 });
        Object.defineProperty(known.find('video').element, 'videoHeight', { value: 600 });
        await known.find('video').trigger('loadedmetadata');
        expect(style(known)).toBe('width: 320px;');
    });

    it('sets the source only once the tile is near the viewport', async () => {
        const observers = [];
        vi.stubGlobal('IntersectionObserver', class {
            constructor(callback, options) {
                this.callback = callback;
                this.options = options;
                this.elements = new Set();
                observers.push(this);
            }

            observe(el) { this.elements.add(el); }
            unobserve(el) { this.elements.delete(el); }
        });

        const wrapper = render([media('a'), media('b')]);
        const [first, second] = wrapper.findAll('video');
        expect(first.attributes('src')).toBeUndefined();
        expect(first.attributes('preload')).toBeUndefined();

        const observer = observers.at(-1);
        expect(observer.options.rootMargin).toBe('200px');
        expect(observer.elements.has(first.element)).toBe(true);
        observer.callback([{ target: first.element, isIntersecting: true }]);
        await nextTick();

        expect(first.attributes('src')).toBe('/download/a#t=0.001');
        expect(first.attributes('preload')).toBe('metadata');
        expect(observer.elements.has(first.element)).toBe(false);
        expect(second.attributes('src')).toBeUndefined();

        wrapper.unmount();
        expect(observer.elements.size).toBe(0);
    });

    it('keeps watching a video whose tile moves to another row, and has no play symbol once it failed', async () => {
        const observers = [];
        vi.stubGlobal('IntersectionObserver', class {
            constructor(callback) {
                this.callback = callback;
                this.elements = new Set();
                observers.push(this);
            }

            observe(el) { this.elements.add(el); }
            unobserve(el) { this.elements.delete(el); }
        });

        // A fresh component module: its IntersectionObserver is created lazily and shared
        vi.resetModules();
        const { default: Fresh } = await import('../../vue/components/AttachedMedia.vue');

        // At the fallback width (576px) the video is in the second row, at 760px all three share one
        const wrapper = mount(Fresh, { props: { galleryId: 'gallery-1', media: [
            media('a', { fileName: 'a.jpg', previewUrl: '/p/a', width: 400, height: 300 }),
            media('b', { fileName: 'b.jpg', previewUrl: '/p/b', width: 400, height: 300 }),
            media('v', { width: 1600, height: 900 }),
        ] } });
        expect(wrapper.findAll('.post-files-images-row')).toHaveLength(2);
        const before = wrapper.find('video').element;

        wrapper.vm.measure(760, 16);
        await nextTick();

        expect(wrapper.findAll('.post-files-images-row')).toHaveLength(1);
        const video = wrapper.find('video');
        const observer = observers.at(-1);
        expect(observer.elements.has(video.element)).toBe(true);
        expect(observer.elements.has(before) && before !== video.element).toBe(false);

        observer.callback([{ target: video.element, isIntersecting: true }]);
        await nextTick();
        expect(video.attributes('src')).toBe('/download/v#t=0.001');

        await video.trigger('error');
        expect(wrapper.find('.post-files-video-play').exists()).toBe(false);
        expect(wrapper.find('.post-files-video-duration').exists()).toBe(false);
    });

    it('sets the source at once without an IntersectionObserver', async () => {
        const wrapper = render([media('a')]);
        await nextTick();
        const video = wrapper.find('video');

        expect(video.attributes('src')).toBe('/download/a#t=0.001');
        expect(video.attributes('preload')).toBe('metadata');
    });

    it('shows the duration of the shape or the measured one, and names it', async () => {
        const t = vi.spyOn(globalThis.humhubStubs.i18n, 't');
        const wrapper = render([media('a', { duration: 125 }), media('b')]);
        const [known, unknown] = wrapper.findAll('a.post-files-video');

        expect(known.find('.post-files-video-duration').text()).toBe('2:05');
        expect(known.find('.post-files-video-duration').attributes('aria-hidden')).toBe('true');
        expect(known.find('.post-files-video-play .ti-player-play-filled').exists()).toBe(true);
        expect(t).toHaveBeenCalledWith('FileModule.base', '{fileName}, video, {duration}', { fileName: 'a.mp4', duration: '2:05' });
        expect(unknown.find('.post-files-video-duration').exists()).toBe(false);
        expect(t).toHaveBeenCalledWith('FileModule.base', '{fileName}, video', { fileName: 'b.mp4' });

        const video = unknown.find('video');
        Object.defineProperty(video.element, 'duration', { value: 61 });
        await video.trigger('loadedmetadata');
        expect(unknown.find('.post-files-video-duration').text()).toBe('1:01');
    });

    it('shows an icon on the tile surface for a video the browser can not load', async () => {
        const wrapper = render([media('a', { width: 200, height: 100 })]);
        const tile = wrapper.find('a.post-files-video');
        const width = style(wrapper);

        await tile.find('video').trigger('error');

        expect(tile.classes()).toContain('is-unplayable');
        expect(tile.find('video').exists()).toBe(false);
        expect(tile.find('.ti-movie').exists()).toBe(true);
        expect(style(wrapper)).toBe(width);
    });

    it('has a video without controls or autoplay', () => {
        const video = render([media('a')]).find('video');

        expect(video.attributes('controls')).toBeUndefined();
        expect(video.attributes('autoplay')).toBeUndefined();
        expect(video.element.muted).toBe(true);
    });
});

describe('AttachedMedia "+N"', () => {
    afterEach(() => vi.restoreAllMocks());

    // Two rows of three 1:1 tiles
    const many = (extra) => [...Array.from({ length: 6 }, (_, n) => image(`i${n}`)), ...extra];

    it('says "more images" when only images are hidden', () => {
        const t = vi.spyOn(globalThis.humhubStubs.i18n, 't');

        const wrapper = render(many([image('x'), image('y')]));

        expect(wrapper.find('.post-files-image-more').text()).toBe('+2');
        expect(t).toHaveBeenCalledWith('FileModule.base', expect.stringContaining('other{# more images}'), { fileName: 'i5.jpg', count: 2 });
    });

    it('says "more images and videos" when a video is hidden, and keeps its type', () => {
        const t = vi.spyOn(globalThis.humhubStubs.i18n, 't');

        const wrapper = render(many([image('x'), media('clip', { fileName: 'clip.webm' })]));

        expect(t).toHaveBeenCalledWith('FileModule.base', expect.stringContaining('other{# more images and videos}'), { fileName: 'i5.jpg', count: 2 });
        const hidden = wrapper.findAll('a[hidden]');
        expect(hidden.map((link) => link.attributes('type'))).toEqual([undefined, 'video/webm']);
        expect(hidden.every((link) => link.attributes('data-ui-gallery') === 'gallery-1')).toBe(true);
    });
});
