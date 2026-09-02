import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import AttachedFiles from '../../modules/file/vue/AttachedFiles.vue';

// v-additions is registered per Vue *app* by the island mounter, not globally —
// see the note in coreInterop.test.js. Here the component only needs it to resolve.
const global = { directives: { additions: {} } };

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
    ...overrides,
});

const render = (files, props = {}) =>
    mount(AttachedFiles, { props: { files, galleryId: 'gallery-post-7', ...props }, global });

describe('AttachedFiles', () => {
    it('renders images as a gallery-linked preview grid', () => {
        const image = file({ guid: 'img-1', fileName: 'photo.jpg', mimeType: 'image/jpeg', mimeIcon: 'mime-image', previewUrl: '/preview/img-1.jpg' });

        const wrapper = render([image]);

        const link = wrapper.find('.post-files-images a');
        expect(link.attributes('data-ui-gallery')).toBe('gallery-post-7');
        expect(link.attributes('href')).toBe(image.url + '#.jpeg');
        expect(link.find('img').attributes('src')).toBe('/preview/img-1.jpg');
        expect(link.find('img').attributes('alt')).toBe('photo.jpg');
        // Single image → full-width column bucket
        expect(wrapper.find('.post-files-images .col-media').classes()).toContain('col-12');
    });

    it('renders videos as native players and audio as labelled native audio elements', () => {
        const video = file({ guid: 'vid-1', fileName: 'clip.mp4', mimeType: 'video/mp4', mimeIcon: 'mime-video' });
        const audio = file({ guid: 'aud-1', fileName: 'song.mp3', mimeType: 'audio/mpeg', mimeIcon: 'mime-audio' });

        const wrapper = render([video, audio]);

        const videoEl = wrapper.find('.post-files-videos video');
        expect(videoEl.attributes('src')).toBe(video.url + '#t=0.001');
        expect(videoEl.attributes('controls')).toBeDefined();

        const audioBucket = wrapper.find('.post-files-audio .col-media');
        expect(audioBucket.find('audio').attributes('src')).toBe(audio.url);
        expect(audioBucket.text()).toContain('song.mp3');
    });

    it('renders the file list with the legacy preview markup and a humanized size', () => {
        const wrapper = render([file()]);

        expect(wrapper.find('.post-files').exists()).toBe(false); // no media grid at all

        const item = wrapper.find('.well.post-file-list ul.files li.file-preview-item');
        expect(item.classes()).toContain('mime-file');
        expect(item.attributes('data-preview-guid')).toBe('file-guid-1');
        expect(item.find('.file-fileInfo').text()).toBe('- 2 KB');

        const link = item.find('a');
        expect(link.attributes('href')).toBe('/file/file/download?guid=file-guid-1');
        expect(link.attributes('target')).toBe('_blank');
        // Attributes the mobile app reads off a download link (FileDownload::getFileDataAttributes()).
        expect(link.attributes('data-file-url')).toBe('/file/file/download?download=1&guid=file-guid-1');
        expect(link.attributes('data-file-name')).toBe('notes.txt');
        expect(link.attributes('data-file-mime')).toBe('text/plain');
        expect(link.text()).toBe('notes.txt');
    });

    it('opens a file with a viewer in the global modal instead of downloading it', () => {
        const wrapper = render([file({ viewUrl: '/file/view?guid=file-guid-1' })]);

        const link = wrapper.find('.post-file-list a');
        expect(link.attributes('href')).toBe('/file/view?guid=file-guid-1');
        expect(link.attributes('data-bs-target')).toBe('#globalModal');
        expect(link.attributes('data-file-download')).toBeUndefined();
    });

    it('marks search hits', () => {
        const wrapper = render([file({ highlight: true })]);

        expect(wrapper.find('.file-preview-content .highlight').exists()).toBe(true);
    });

    it('attaches a thumbnail popover to previewable list entries only', () => {
        const image = file({ guid: 'img-1', fileName: 'photo.jpg', mimeIcon: 'mime-image', previewUrl: '/preview/img-1.jpg' });

        const wrapper = render([image, file()]);

        const [previewable, plain] = wrapper.findAll('.file-preview-content');
        expect(previewable.classes()).toContain('po');
        expect(previewable.attributes('data-bs-content')).toBe('<img alt="photo.jpg" src="/preview/img-1.jpg" />');
        expect(plain.classes()).not.toContain('po');
        expect(plain.attributes('data-bs-content')).toBeUndefined();
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
        expect(wrapper.findAll('.post-file-list li')).toHaveLength(3);
    });

    it('drops media entries from the list when excludeMedia is set', () => {
        const files = [
            file({ guid: 'img', fileName: 'a.png', mimeIcon: 'mime-image', previewUrl: '/p/a.png' }),
            file({ guid: 'doc', fileName: 'c.pdf', mimeType: 'application/pdf', mimeIcon: 'mime-pdf' }),
        ];

        const wrapper = render(files, { excludeMedia: true });

        expect(wrapper.findAll('.post-files-images img')).toHaveLength(1);
        expect(wrapper.findAll('.post-file-list li')).toHaveLength(1);
        expect(wrapper.find('.post-file-list li').attributes('data-preview-guid')).toBe('doc');
    });

    it('renders no media grid at all when preview is off, and lists everything', () => {
        const files = [
            file({ guid: 'img', fileName: 'a.png', mimeIcon: 'mime-image', previewUrl: '/p/a.png' }),
            file({ guid: 'vid', fileName: 'b.webm', mimeIcon: 'mime-video' }),
        ];

        const wrapper = render(files, { preview: false });

        expect(wrapper.find('.post-files').exists()).toBe(false);
        expect(wrapper.findAll('.post-file-list li')).toHaveLength(2);
    });

    it('widens the large-breakpoint buckets for the fluid theme layout', () => {
        const files = [
            file({ guid: 'a', fileName: 'a.png', mimeIcon: 'mime-image', previewUrl: '/p/a.png' }),
            file({ guid: 'b', fileName: 'b.png', mimeIcon: 'mime-image', previewUrl: '/p/b.png' }),
            file({ guid: 'c', fileName: 'c.png', mimeIcon: 'mime-image', previewUrl: '/p/c.png' }),
        ];

        expect(render(files).find('.post-files-images .col-media').classes())
            .toEqual(expect.arrayContaining(['col-6', 'col-lg-6', 'col-xl-4']));
        expect(render(files, { fluid: true }).find('.post-files-images .col-media').classes())
            .toEqual(expect.arrayContaining(['col-6', 'col-lg-4', 'col-xl-3']));
    });
});
