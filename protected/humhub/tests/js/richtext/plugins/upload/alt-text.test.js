import { describe, it, expect, afterEach, vi } from 'vitest';

import {initEditor, serialize, clickDropdownMenuItem} from '../../testEditor.js';

const clickUpload = () => clickDropdownMenuItem('insert-dropdown', 'uploadFile');

const flush = () => new Promise((resolve) => setTimeout(resolve, 0));

const image = (guid, name) => ({guid, url: 'https://x.test/' + name, fileName: name, mimeType: 'image/png', mimeIcon: 'mime-image'});

// The platform's Vue runtime, as far as the editor uses it: the dialog itself is
// ImageAltTextModal.vue (tests/js/imageAltTextModal.test.js)
const stubVue = (registered = true) => {
    const vue = {
        isRegistered: vi.fn((name) => registered && name === 'ImageAltTextModal'),
        mountElement: vi.fn(() => Promise.resolve({unmount() {}})),
    };
    // Only now: with `humhub.modules` the editor would take itself for the platform's while uploading
    globalThis.humhub.modules = {vue};
    return vue;
};

const dialogs = () => document.querySelectorAll('body > [data-vue-component="ImageAltTextModal"]');

const answer = (alt) => dialogs()[0].dispatchEvent(new CustomEvent('altText:save', {detail: {alt}}));

const skip = () => dialogs()[0].dispatchEvent(new CustomEvent('altText:cancel'));

describe("Plugin:upload, the alt text of an uploaded image", () => {
    afterEach(() => {
        delete globalThis.humhub.modules;
        dialogs().forEach((host) => host.remove());
    });

    it("is asked for in the platform's dialog and set on the image", async () => {
        initEditor({upload: () => Promise.resolve([image('g1', 'a.png')])});

        clickUpload();
        const vue = stubVue();
        await flush();

        expect(dialogs()).toHaveLength(1);
        expect(JSON.parse(dialogs()[0].getAttribute('data-props'))).toEqual({src: 'https://x.test/a.png', required: false});
        expect(vue.mountElement).toHaveBeenCalledWith(dialogs()[0]);

        answer('A red bicycle');
        expect(dialogs()).toHaveLength(0);
        expect(serialize()).to.equal('![A red bicycle](file-guid:g1 "a.png")');
    });

    it("keeps the file name when skipped, and is empty for a decorative image", async () => {
        initEditor({upload: () => Promise.resolve([image('g1', 'a.png')])});
        clickUpload();
        stubVue();
        await flush();
        skip();
        expect(dialogs()).toHaveLength(0);
        expect(serialize()).to.equal('![a.png](file-guid:g1 "a.png")');

        delete globalThis.humhub.modules;
        initEditor({upload: () => Promise.resolve([image('g1', 'a.png')])});
        clickUpload();
        stubVue();
        await flush();
        answer('');
        expect(serialize()).to.equal('![](file-guid:g1 "a.png")');
    });

    it("is asked for each image, one after another, and not for other files", async () => {
        initEditor({upload: () => Promise.resolve([
            image('g1', 'a.png'),
            {guid: 'g2', url: 'https://x.test/b.pdf', fileName: 'b.pdf', mimeType: 'application/pdf'},
            image('g3', 'c.png'),
        ])});

        clickUpload();
        stubVue();
        await flush();

        expect(dialogs()).toHaveLength(1);
        expect(dialogs()[0].getAttribute('data-props')).to.contain('a.png');
        answer('First');
        expect(dialogs()).toHaveLength(1);
        expect(dialogs()[0].getAttribute('data-props')).to.contain('c.png');
        answer('Second');
        expect(dialogs()).toHaveLength(0);

        expect(serialize()).to.equal('![First](file-guid:g1 "a.png")[b.pdf](file-guid:g2)![Second](file-guid:g3 "c.png")');
    });

    it("when required, cannot be skipped: cancelling removes the image", async () => {
        initEditor({upload: () => Promise.resolve([image('g1', 'a.png')]), image: {altText: 'required'}}, 'Before');
        clickUpload();
        stubVue();
        await flush();

        expect(JSON.parse(dialogs()[0].getAttribute('data-props'))).toEqual({src: 'https://x.test/a.png', required: true});
        expect(serialize()).to.equal('![a.png](file-guid:g1 "a.png")Before');

        skip();
        expect(dialogs()).toHaveLength(0);
        expect(serialize()).to.equal('Before');

        delete globalThis.humhub.modules;
        initEditor({upload: () => Promise.resolve([image('g1', 'a.png')]), image: {altText: 'required'}});
        clickUpload();
        stubVue();
        await flush();
        answer('A red bicycle');
        expect(serialize()).to.equal('![A red bicycle](file-guid:g1 "a.png")');
    });

    it("is not asked for when off, or without the platform's dialog", async () => {
        initEditor({upload: () => Promise.resolve([image('g1', 'a.png')]), image: {altText: 'off'}});
        clickUpload();
        const vue = stubVue();
        await flush();
        expect(vue.mountElement).not.toHaveBeenCalled();

        delete globalThis.humhub.modules;
        initEditor({upload: () => Promise.resolve([image('g1', 'a.png')])});
        clickUpload();
        stubVue(false);
        await flush();
        expect(dialogs()).toHaveLength(0);
        expect(serialize()).to.equal('![a.png](file-guid:g1 "a.png")');
    });

    it("removes its mount point when the dialog cannot be mounted", async () => {
        initEditor({upload: () => Promise.resolve([image('g1', 'a.png')])});
        clickUpload();
        const vue = stubVue();
        vue.mountElement.mockImplementation(() => Promise.resolve(null));
        await flush();
        await flush();

        expect(dialogs()).toHaveLength(0);
    });
});
