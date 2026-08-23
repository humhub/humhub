import { describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import CommentAttachments from '../../modules/comment/vue/components/CommentAttachments.vue';

// The API's file shape (file\serializers\FileSerializer::file()).
const file = (overrides = {}) => ({
    id: 1,
    guid: 'file-guid-1',
    mime_type: 'text/plain',
    size: 2048,
    file_name: 'notes.txt',
    url: '/file/file/download?guid=file-guid-1',
    preview_url: null,
    ...overrides,
});

describe('CommentAttachments', () => {
    it('renders images as a gallery-linked preview grid', () => {
        const image = file({ guid: 'img-1', file_name: 'photo.jpg', mime_type: 'image/jpeg', preview_url: '/preview/img-1.jpg' });

        const wrapper = mount(CommentAttachments, { props: { files: [image], contextId: 7 } });

        const link = wrapper.find('.post-files-images a');
        expect(link.attributes('data-ui-gallery')).toBe('gallery-comment-7');
        expect(link.attributes('href')).toBe(image.url + '#.jpeg');
        expect(link.find('img').attributes('src')).toBe('/preview/img-1.jpg');
        expect(link.find('img').attributes('alt')).toBe('photo.jpg');
        // Theme hook parity with showFiles.php
        expect(wrapper.find('.hideOnEdit .post-files').exists()).toBe(true);
        // Single image → full-width column bucket
        expect(wrapper.find('.post-files-images .col-media').classes()).toContain('col-12');
    });

    it('renders videos as native players and audio as native audio elements', () => {
        const video = file({ guid: 'vid-1', file_name: 'clip.mp4', mime_type: 'video/mp4' });
        const audio = file({ guid: 'aud-1', file_name: 'song.mp3', mime_type: 'audio/mpeg' });

        const wrapper = mount(CommentAttachments, { props: { files: [video, audio], contextId: 7 } });

        const videoEl = wrapper.find('.post-files-videos video');
        expect(videoEl.exists()).toBe(true);
        expect(videoEl.attributes('src')).toBe(video.url + '#t=0.001');
        expect(videoEl.attributes('controls')).toBeDefined();

        const audioEl = wrapper.find('.post-files-audio audio');
        expect(audioEl.exists()).toBe(true);
        expect(audioEl.attributes('src')).toBe(audio.url);
    });

    it('renders non-media files as a plain download list with a humanized size', () => {
        const wrapper = mount(CommentAttachments, { props: { files: [file()], contextId: 7 } });

        expect(wrapper.find('.post-files').exists()).toBe(false); // no media grid at all
        const row = wrapper.find('.post-files-list a');
        expect(row.attributes('href')).toBe('/file/file/download?guid=file-guid-1');
        expect(row.attributes('target')).toBe('_blank');
        expect(row.text()).toContain('notes.txt');
        expect(row.text()).toContain('2.0 KB');
    });

    it('splits a mixed set into the right buckets', () => {
        const files = [
            file({ guid: 'img', file_name: 'a.png', preview_url: '/p/a.png' }),
            file({ guid: 'vid', file_name: 'b.webm' }),
            file({ guid: 'doc', file_name: 'c.pdf', mime_type: 'application/pdf' }),
        ];

        const wrapper = mount(CommentAttachments, { props: { files, contextId: 3 } });

        expect(wrapper.findAll('.post-files-images img')).toHaveLength(1);
        expect(wrapper.findAll('.post-files-videos video')).toHaveLength(1);
        expect(wrapper.findAll('.post-files-list a')).toHaveLength(1);
    });
});
