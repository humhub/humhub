import { beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import FileVersions from '../../vue/FileVersions.vue';
import UserImage from '../../../../modules/user/vue/UserImage.vue';

await import('../../../../resources/js/humhub/humhub.url.js');
await import('../../../../resources/js/humhub/humhub.vue.js');

const jane = { id: 2, guid: 'u2', displayName: 'Jane Doe', url: '/u/jane/', imageUrl: '/u/jane/image.jpg' };

const version = (over = {}) => ({
    id: null,
    version: 1,
    current: true,
    size: 2048,
    createdAt: '2026-08-20T10:00:00+00:00',
    createdBy: jane,
    downloadUrl: '/file/file/download?guid=g&download=1',
    ...over,
});

const versions = (props = { fileId: 7 }) => mount(FileVersions, {
    props,
    global: { components: { UserImage } },
});

describe('FileVersions', () => {
    beforeEach(() => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({
            results: [
                version({ version: 2 }),
                version({ id: 3, version: 1, current: false, size: 1024, downloadUrl: '/file/file/download?guid=g&variant=old-3&download=1' }),
            ],
        }));
    });

    it('lists the file\'s versions, newest first, the current one marked', async () => {
        const wrapper = versions();
        await flushPromises();

        expect(globalThis.humhubStubs.client.get.mock.calls[0][0]).toContain('file/7/versions');
        const rows = wrapper.findAll('.file-version');
        expect(rows).toHaveLength(2);
        expect(rows[0].find('.file-version-title').text()).toBe('Jane Doe uploaded v2 Current');
        expect(rows[1].find('.badge').exists()).toBe(false);
        expect(rows[1].find('.file-version-meta').text()).toContain('1 KB');
    });

    it('downloads each version by its own link', async () => {
        const wrapper = versions();
        await flushPromises();

        const links = wrapper.findAll('.file-version a.btn');
        expect(links[1].attributes('href')).toContain('variant=old-3');
        expect(links[1].attributes('aria-label')).toBe('Download version 1');
    });

    it('says so when the versions cannot be loaded', async () => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.reject(new Error('nope')));
        const wrapper = versions();
        await flushPromises();

        expect(wrapper.find('.text-danger').text()).toBe('The versions could not be loaded.');
    });
});
