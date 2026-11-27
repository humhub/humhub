import { afterEach, describe, expect, it, vi } from 'vitest';
import { nextTick } from 'vue';
import { mount } from '@vue/test-utils';
import AttachedFiles from '../../vue/AttachedFiles.vue';

await import('../../../../resources/js/humhub/humhub.url.js');
await import('../../../../resources/js/humhub/humhub.vue.js');

// v-additions is registered per Vue *app* by the island mounter, not globally —
// see the note in coreInterop.test.js. Here the component only needs it to resolve.
const global = { directives: { additions: {} } };

// jsdom has no `inert` property, so Vue would render `inert="false"`; a browser's reflects the attribute
Object.defineProperty(HTMLElement.prototype, 'inert', {
    configurable: true,
    get() {
        return this.hasAttribute('inert');
    },
    set(value) {
        this.toggleAttribute('inert', Boolean(value));
    },
});

// The image areas share one observer, created by whichever test lays out images first
const resizeObservers = [];
vi.stubGlobal('ResizeObserver', class {
    constructor(callback) {
        this.callback = callback;
        this.elements = new Set();
        resizeObservers.push(this);
    }

    observe(el) {
        this.elements.add(el);
    }

    unobserve(el) {
        this.elements.delete(el);
    }
});

// The API's file shape (file\serializers\FileSerializer::file()).
const file = (overrides = {}) => ({
    id: 1,
    guid: 'file-guid-1',
    mimeType: 'text/plain',
    size: 2048,
    fileName: 'notes.txt',
    mimeIcon: 'mime-file',
    url: '/file/file/download?guid=file-guid-1',
    downloadUrl: '/file/file/download?download=1&guid=file-guid-1',
    previewUrl: null,
    width: null,
    height: null,
    ...overrides,
});

const render = (files, props = {}) =>
    mount(AttachedFiles, { props: { files, galleryId: 'gallery-post-7', ...props }, global });

// In the document, for the focus; unmounted after the test
const attached = [];
const renderAttached = (files) => {
    const wrapper = mount(AttachedFiles, { props: { files, galleryId: 'gallery-post-7' }, global, attachTo: document.body });
    attached.push(wrapper);

    return wrapper;
};

const image = (guid, width, height) => file({
    guid, fileName: `${guid}.jpg`, mimeType: 'image/jpeg',
    previewUrl: `/preview/${guid}.jpg`, displayUrl: `/display/${guid}.jpg`, width, height,
});

const pdfs = (names, overrides = {}) => names.map((name) => file({ guid: name, fileName: `${name}.pdf`, ...overrides[name] }));

// Collects the frames requested from now on, for the test to run them
const frames = () => {
    const requested = [];
    vi.spyOn(globalThis, 'requestAnimationFrame').mockImplementation((callback) => requested.push(callback));

    return requested;
};

afterEach(() => {
    attached.splice(0).forEach((wrapper) => wrapper.unmount());
    vi.restoreAllMocks();
});

describe('AttachedFiles', () => {
    // Before the area is measured the layout assumes 576px width, 192px rows, 320px for a single image and 8px gaps
    it('links every image to the gallery and lays them out in justified rows', () => {
        const images = [1, 2, 3].map((n) => image(`photo-${n}`, 400, 300));

        const wrapper = render(images);

        const links = wrapper.findAll('.post-files-images a.post-files-image');
        expect(links).toHaveLength(3);
        expect(links[0].attributes('data-ui-gallery')).toBe('gallery-post-7');
        // The original is the link and the download, the media viewer shows the display version
        expect(links[0].attributes('href')).toBe(images[0].url);
        expect(links[0].attributes('data-ui-gallery-src')).toBe('/display/photo-1.jpg');
        const img = links[0].find('img');
        expect(img.attributes('alt')).toBe('photo-1.jpg');
        expect(img.attributes('loading')).toBe('lazy');

        // Two 4:3 images fill the first row at 213px (three would come to 140px), the third keeps 192px
        const rows = wrapper.findAll('.post-files-images-row');
        expect(rows.map((row) => row.attributes('style'))).toEqual(['height: 213px;', 'height: 192px;']);
        expect(wrapper.findAll('.post-files-image img').map((tile) => [tile.attributes('width'), tile.attributes('height')]))
            .toEqual([['284', '213'], ['284', '213'], ['256', '192']]);
    });

    it('shows "+N" on the last tile and keeps the hidden images in the gallery', () => {
        const t = vi.spyOn(globalThis.humhubStubs.i18n, 't');
        const images = Array.from({ length: 12 }, (_, n) => image(`photo-${n}`, 300, 300));

        const wrapper = render(images);

        // Two rows of three squares
        const tiles = wrapper.findAll('a.post-files-image');
        const hidden = wrapper.findAll('.post-files-images a[hidden]');
        expect(tiles).toHaveLength(6);
        expect(hidden).toHaveLength(6);
        hidden.forEach((link, n) => {
            expect(link.attributes('data-ui-gallery')).toBe('gallery-post-7');
            expect(link.text()).toBe(`photo-${n + 6}.jpg`);
        });

        const last = tiles[5];
        expect(last.find('.post-files-image-more').text()).toBe('+6');
        expect(t).toHaveBeenCalledWith('FileModule.base', expect.stringContaining('other{# more images}'), {
            fileName: 'photo-5.jpg',
            count: 6,
        });
        expect(last.attributes('aria-label')).toContain('photo-5.jpg');
        // Only the last tile says how many follow
        expect(wrapper.findAll('.post-files-image-more')).toHaveLength(1);
        expect(tiles[0].attributes('aria-label')).toBeUndefined();
    });

    it('lays out an image without dimensions as a square', () => {
        const square = file({ guid: 'img', fileName: 'old.jpg', mimeType: 'image/jpeg', previewUrl: '/p/old.jpg' });

        const img = render([square]).find('a.post-files-image img');

        expect(img.attributes('width')).toBe(img.attributes('height'));
    });

    it('shows the preview in a small tile and the display version in a larger one', () => {
        // A 3:1 panorama alone fills the width: 576 × 192, larger than the 400px preview (with 25% tolerance)
        const panorama = render([image('pano', 3000, 1000)]).find('a.post-files-image img');
        expect(panorama.attributes('width')).toBe('576');
        expect(panorama.attributes('height')).toBe('192');
        expect(panorama.attributes('src')).toBe('/display/pano.jpg');

        // Three squares share a row at 186.67 × 186.67, the preview is enough
        const squares = render([image('a', 1200, 1200), image('b', 1200, 1200), image('c', 1200, 1200)])
            .findAll('a.post-files-image img');
        expect(squares.map((img) => img.attributes('src'))).toEqual(['/preview/a.jpg', '/preview/b.jpg', '/preview/c.jpg']);
        expect(squares[0].attributes('width')).toBe('187');

        // A single square: 320px, the preview at 1x, the display version on a 2x screen (640px)
        expect(render([image('sq', 1200, 1200)]).find('img').attributes('src')).toBe('/preview/sq.jpg');
        const dpr = window.devicePixelRatio;
        window.devicePixelRatio = 2;
        try {
            expect(render([image('sq', 1200, 1200)]).find('img').attributes('src')).toBe('/display/sq.jpg');
        } finally {
            window.devicePixelRatio = dpr;
        }
    });

    it('keeps the preview where the original fits into it', () => {
        // 576 × 144: larger than the preview's long side, but the preview of a 400 × 100 original is the original
        const img = render([image('banner', 400, 100)]).find('a.post-files-image img');

        expect(img.attributes('width')).toBe('576');
        expect(img.attributes('src')).toBe('/preview/banner.jpg');
    });

    it('keeps the preview for an image without a display version', () => {
        const panorama = file({ guid: 'img', fileName: 'a.jpg', previewUrl: '/p/a.jpg', width: 3000, height: 1000 });

        expect(render([panorama]).find('a.post-files-image img').attributes('src')).toBe('/p/a.jpg');
    });

    it('takes no display version for the fallback width before the area is measured', async () => {
        vi.spyOn(Element.prototype, 'clientWidth', 'get').mockReturnValue(300);

        const wrapper = render([image('pano', 3000, 1000)]);
        await nextTick();

        // 300 × 100 at the measured width
        expect(wrapper.find('img').attributes('width')).toBe('300');
        expect(wrapper.find('img').attributes('src')).toBe('/preview/pano.jpg');
    });

    it('lays the images out at the measured width, in one frame for all image areas', async () => {
        const requested = frames();
        const clientWidth = vi.spyOn(Element.prototype, 'clientWidth', 'get').mockReturnValue(1000);
        const panorama = [image('pano', 3000, 1000)];
        const first = render(panorama);
        const second = render(panorama, { galleryId: 'gallery-post-8' });
        clientWidth.mockRestore();
        const areas = [first, second].map((wrapper) => wrapper.find('.post-files-images').element);

        expect(resizeObservers).toHaveLength(1);
        const [observer] = resizeObservers;
        expect(areas.every((area) => observer.elements.has(area))).toBe(true);

        // Measured on mount, 1000px wide: a 3:1 image alone is limited to the 320px single height
        await nextTick();
        expect(first.find('img').attributes('width')).toBe('960');
        expect(first.find('img').attributes('src')).toBe('/display/pano.jpg');
        expect(second.find('img').attributes('width')).toBe('960');

        // The notification after observe(), or a change of height only: nothing to do
        observer.callback(areas.map((target) => ({ target, contentRect: { width: 1000.2, height: 400 } })));
        expect(requested).toHaveLength(0);

        // Resized to 400px: both areas in one frame, the image fills the width and keeps the display version
        observer.callback(areas.map((target) => ({ target, contentRect: { width: 400, height: 320 } })));
        expect(requested).toHaveLength(1);
        requested[0]();
        await nextTick();
        expect(first.find('img').attributes('width')).toBe('400');
        expect(first.find('img').attributes('height')).toBe('133');
        expect(first.find('img').attributes('src')).toBe('/display/pano.jpg');
        expect(second.find('img').attributes('width')).toBe('400');

        // The image area goes with the images (inline edit) and the unmount
        await first.setProps({ files: [file()] });
        expect(observer.elements.has(areas[0])).toBe(false);
        second.unmount();
        expect(observer.elements.has(areas[1])).toBe(false);
    });

    it('reads the sizes from the CSS variables, in rem or px', async () => {
        const requested = frames();
        const squares = render([image('a', 1000, 1000), image('b', 1000, 1000)]);
        const single = render([image('c', 1000, 1000)]);
        const areas = [squares, single].map((wrapper) => wrapper.find('.post-files-images').element);
        areas[0].style.setProperty('--hh-attached-images-row-height', '6.25rem');
        areas[0].style.setProperty('--hh-attached-images-gap', '0');
        areas[1].style.setProperty('--hh-attached-images-single-height', '50px');

        resizeObservers[0].callback(areas.map((target) => ({ target, contentRect: { width: 200, height: 0 } })));
        requested[0]();
        await nextTick();

        // 100px rows without a gap: two squares fill 200px exactly
        expect(squares.findAll('img').map((img) => img.attributes('width'))).toEqual(['100', '100']);
        expect(single.find('img').attributes('height')).toBe('50');
    });

    it('renders videos as native players and audio as labelled native audio elements', () => {
        const video = file({ guid: 'vid-1', fileName: 'clip.mp4', mimeType: 'video/mp4', mimeIcon: 'mime-video' });
        const audio = file({ guid: 'aud-1', fileName: 'song.mp3', mimeType: 'audio/mpeg', mimeIcon: 'mime-audio' });

        const wrapper = render([video, audio]);

        const videoEl = wrapper.find('.post-files-videos video');
        expect(videoEl.attributes('src')).toBe(video.url + '#t=0.001');
        expect(videoEl.attributes('controls')).toBeDefined();
        // without the type the media viewer shows the video as an image
        expect(wrapper.find('.post-files-videos a').attributes('type')).toBe('video/mp4');
        expect(wrapper.find('.post-files-videos a').attributes('href')).toBe(video.url);

        const audioBucket = wrapper.find('.post-files-audio-item');
        expect(audioBucket.find('audio').attributes('src')).toBe(audio.url);
        expect(audioBucket.find('audio').attributes('aria-label')).toBe('song.mp3');
        expect(audioBucket.text()).toContain('song.mp3');
    });

    it('renders a file row with icon, name, size and a download button', () => {
        const wrapper = render([file({ icon: 'file-text' })]);

        expect(wrapper.find('.post-files').exists()).toBe(false); // no media area at all

        const row = wrapper.find('.list-group.file-list .list-group-item.file-row');
        expect(row.attributes('data-preview-guid')).toBe('file-guid-1');
        expect(row.attributes('role')).toBe('listitem');
        expect(row.find('.file-row-icon').classes()).toContain('ti-file-text');
        expect(row.find('.file-row-meta').text()).toBe('(2 KB)');

        const link = row.find('a.file-row-name');
        expect(link.attributes('href')).toBe('/file/file/download?guid=file-guid-1');
        expect(link.attributes('target')).toBe('_blank');
        // Shortened in the middle: the extension stays
        expect(link.find('.file-row-name-head').text()).toBe('notes');
        expect(link.find('.file-row-name-tail').text()).toBe('.txt');
        // Attributes the mobile app reads off a download link (FileDownload::getFileDataAttributes())
        expect(link.attributes('data-file-url')).toBe('/file/file/download?download=1&guid=file-guid-1');
        expect(link.attributes('data-file-name')).toBe('notes.txt');
        expect(link.attributes('data-file-mime')).toBe('text/plain');

        const download = row.find('a.file-row-action');
        expect(download.attributes('href')).toBe('/file/file/download?download=1&guid=file-guid-1');
        expect(download.attributes('aria-label')).toBe('Download notes.txt');
        // The tooltip as Button::iconOnly() sets it, no native title beside it
        expect(download.attributes('data-bs-title')).toBe('Download notes.txt');
        expect(download.attributes('data-bs-toggle')).toBe('tooltip');
        expect(download.attributes('title')).toBeUndefined();
        expect(download.attributes('data-file-download')).toBeDefined();
    });

    it('opens a file with a viewer in the global modal', () => {
        const wrapper = render([file({ viewUrl: '/file/view?guid=file-guid-1' })]);

        const link = wrapper.find('a.file-row-name');
        expect(link.attributes('href')).toBe('/file/view?guid=file-guid-1');
        expect(link.attributes('data-bs-target')).toBe('#globalModal');
        expect(link.attributes('data-file-download')).toBeUndefined();
    });

    it('marks search hits', () => {
        const wrapper = render([file({ highlight: true })]);

        expect(wrapper.find('a.file-row-name').classes()).toContain('highlight');
    });

    it('attaches a thumbnail popover to previewable rows only', () => {
        const photo = file({ guid: 'img-1', fileName: 'photo.jpg', previewUrl: '/preview/img-1.jpg' });

        const wrapper = render([photo, file()], { preview: false });

        const [previewable, plain] = wrapper.findAll('.file-row-text');
        expect(previewable.classes()).toContain('po');
        expect(previewable.attributes('data-bs-content')).toBe('<img alt="photo.jpg" src="/preview/img-1.jpg" />');
        expect(plain.classes()).not.toContain('po');
    });

    // The test's i18n leaves the plural unformatted: the counts are checked on the call
    it('shows long lists from four files on as two rows and "Show all"', async () => {
        const t = vi.spyOn(globalThis.humhubStubs.i18n, 't');

        const wrapper = render(pdfs(['a', 'b', 'c', 'd']));

        const more = wrapper.find('.file-list-more');
        expect(wrapper.findAll('.file-list-rows > .file-row[data-preview-guid]')).toHaveLength(2);
        expect(more.findAll('.file-row')).toHaveLength(2);
        expect(more.classes()).not.toContain('show');
        expect(more.attributes('inert')).toBe('');

        const toggle = wrapper.find('.file-list-toggle');
        // "2 of 4 files"
        expect(t).toHaveBeenCalledWith('FileModule.base', '{shown} of {total,plural,one{# file} other{# files}}', {
            shown: 2,
            total: 4,
        });
        const button = toggle.find('button');
        expect(button.attributes('aria-expanded')).toBe('false');
        // The list, the wrapper of the hidden rows has no role
        expect(button.attributes('aria-controls')).toBe(wrapper.find('[role="list"]').attributes('id'));
        // The count belongs to the button
        expect(button.attributes('aria-describedby')).toBe(toggle.find('.file-row-text').attributes('id'));
        expect(button.text()).toContain('Show all');

        await button.trigger('click');

        expect(more.classes()).toContain('show');
        expect(more.attributes('inert')).toBeUndefined();
        expect(button.attributes('aria-expanded')).toBe('true');
        expect(button.text()).toContain('Show less');
        // "4 files"
        expect(t).toHaveBeenCalledWith('FileModule.base', '{total,plural,one{# file} other{# files}}', { total: 4 });
    });

    it('hands the focus to the first revealed row on "Show all" and keeps it on "Show less"', async () => {
        const wrapper = renderAttached(pdfs(['a', 'b', 'c', 'd']));
        const more = wrapper.find('.file-list-more');
        const button = wrapper.find('.file-list-toggle button');

        button.element.focus();
        await button.trigger('click');
        await nextTick();
        expect(document.activeElement).toBe(more.find('a.file-row-name').element);
        expect(document.activeElement.textContent).toBe('c.pdf');

        button.element.focus();
        await button.trigger('click');
        await nextTick();
        expect(more.classes()).not.toContain('show');
        expect(more.attributes('inert')).toBe('');
        expect(button.attributes('aria-expanded')).toBe('false');
        expect(document.activeElement).toBe(button.element);
    });

    it('leaves the focus alone when "Show all" is clicked without it', async () => {
        const wrapper = renderAttached(pdfs(['a', 'b', 'c', 'd']));

        await wrapper.find('.file-list-toggle button').trigger('click');
        await nextTick();
        expect(document.activeElement).toBe(document.body);
    });

    it('shows all rows when a search hit is among the hidden ones', () => {
        const hitInTail = render(pdfs(['a', 'b', 'c', 'd'], { d: { highlight: true } }));
        const more = hitInTail.find('.file-list-more');
        expect(more.classes()).toContain('show');
        expect(more.attributes('inert')).toBeUndefined();
        expect(hitInTail.find('.file-list-toggle button').attributes('aria-expanded')).toBe('true');

        const hitInHead = render(pdfs(['a', 'b', 'c', 'd'], { a: { highlight: true } }));
        expect(hitInHead.find('.file-list-more').classes()).not.toContain('show');
    });

    it('announces the rows as a list, without the toggle row', () => {
        const wrapper = render(pdfs(['a', 'b', 'c', 'd']));

        const lists = wrapper.findAll('[role="list"]');
        expect(lists).toHaveLength(1);
        const list = lists[0];
        expect(list.findAll('.file-row[data-preview-guid]').map((row) => row.attributes('role')))
            .toEqual(['listitem', 'listitem', 'listitem', 'listitem']);
        // Nothing between the list and its items
        expect(list.find('.file-list-more').attributes('role')).toBe('none');
        expect(list.find('.file-list-more-inner').attributes('role')).toBe('none');
        expect(list.find('.file-list-toggle').exists()).toBe(false);
        expect(wrapper.find('.file-list-toggle').attributes('role')).toBeUndefined();
    });

    it('gives each list ids of its own', () => {
        const ids = [render(pdfs(['a', 'b', 'c', 'd'])), render(pdfs(['a', 'b', 'c', 'd']))]
            .flatMap((wrapper) => [
                wrapper.find('[role="list"]').attributes('id'),
                wrapper.find('.file-list-toggle .file-row-text').attributes('id'),
            ]);

        expect(new Set(ids).size).toBe(4);
    });

    it('never hides a single row', () => {
        const files = ['a', 'b', 'c'].map((name) => file({ guid: name, fileName: `${name}.pdf` }));

        const wrapper = render(files);

        expect(wrapper.findAll('.file-row[data-preview-guid]')).toHaveLength(3);
        expect(wrapper.find('.file-list-more').exists()).toBe(false);
        expect(wrapper.find('.file-list-toggle').exists()).toBe(false);
    });

    it('splits a mixed set into the right buckets', () => {
        const files = [
            file({ guid: 'img', fileName: 'a.png', mimeIcon: 'mime-image', previewUrl: '/p/a.png' }),
            file({ guid: 'vid', fileName: 'b.webm', mimeIcon: 'mime-video' }),
            file({ guid: 'doc', fileName: 'c.pdf', mimeType: 'application/pdf', mimeIcon: 'mime-pdf' }),
        ];

        const wrapper = render(files);

        expect(wrapper.findAll('.post-files-images img')).toHaveLength(1);
        expect(wrapper.findAll('.post-files-videos video')).toHaveLength(1);
        expect(wrapper.findAll('.file-row[data-preview-guid]')).toHaveLength(3);
    });

    it('drops media entries from the list when excludeMedia is set', () => {
        const files = [
            file({ guid: 'img', fileName: 'a.png', mimeIcon: 'mime-image', previewUrl: '/p/a.png' }),
            file({ guid: 'doc', fileName: 'c.pdf', mimeType: 'application/pdf', mimeIcon: 'mime-pdf' }),
        ];

        const wrapper = render(files, { excludeMedia: true });

        expect(wrapper.findAll('.post-files-images img')).toHaveLength(1);
        expect(wrapper.findAll('.file-row[data-preview-guid]')).toHaveLength(1);
        expect(wrapper.find('.file-row[data-preview-guid]').attributes('data-preview-guid')).toBe('doc');
    });

    it('renders the audio formats browsers play natively as audio players', () => {
        const files = ['a.mp3', 'b.wav', 'c.m4a', 'd.aac', 'e.FLAC'].map((fileName) =>
            file({ guid: fileName, fileName, mimeIcon: 'mime-audio' }));

        const wrapper = render(files, { excludeMedia: true });

        expect(wrapper.findAll('.post-files-audio audio')).toHaveLength(5);
        expect(wrapper.find('.file-list').exists()).toBe(false);
    });

    it('keeps media files without a preview in the list when excludeMedia is set', () => {
        const files = [
            file({ guid: 'mp3', fileName: 'a.mp3', mimeIcon: 'mime-audio' }),
            file({ guid: 'ogg', fileName: 'b.ogg', mimeIcon: 'mime-audio' }),
            file({ guid: 'avi', fileName: 'c.avi', mimeIcon: 'mime-video' }),
            file({ guid: 'img', fileName: 'd.tiff', mimeIcon: 'mime-image', previewUrl: null }),
        ];

        const wrapper = render(files, { excludeMedia: true });

        expect(wrapper.findAll('.post-files-audio audio')).toHaveLength(1);
        expect(wrapper.findAll('.file-row[data-preview-guid]').map((item) => item.attributes('data-preview-guid')))
            .toEqual(['ogg', 'avi', 'img']);
    });

    it('renders no media area at all when preview is off, and lists everything', () => {
        const files = [
            file({ guid: 'img', fileName: 'a.png', mimeIcon: 'mime-image', previewUrl: '/p/a.png' }),
            file({ guid: 'vid', fileName: 'b.webm', mimeIcon: 'mime-video' }),
        ];

        const wrapper = render(files, { preview: false });

        expect(wrapper.find('.post-files').exists()).toBe(false);
        expect(wrapper.findAll('.file-row[data-preview-guid]')).toHaveLength(2);
    });
});
