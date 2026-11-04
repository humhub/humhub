import { describe, it, expect, vi } from 'vitest';

import {initEditor, serialize, clickDropdownMenuItem} from '../../testEditor.js';

const clickUpload = () => clickDropdownMenuItem('insert-dropdown', 'uploadFile');

const flush = () => new Promise((resolve) => setTimeout(resolve, 0));

describe("Plugin:upload, the upload option", () => {
    it("lets the host pick the files from the menu and inserts what it uploaded", async () => {
        const upload = vi.fn(() => Promise.resolve([
            {guid: 'g1', url: 'https://x.test/a.pdf', fileName: 'a.pdf', mimeType: 'application/pdf'},
        ]));
        initEditor({upload});

        clickUpload();
        expect(upload).toHaveBeenCalledWith(null, {accept: null});

        await flush();
        expect(serialize()).to.equal('[a.pdf](file-guid:g1)');
        expect(document.querySelector('#stage .loader')).to.be.null;
    });

    it("inserts an uploaded image as an image", async () => {
        initEditor({upload: () => Promise.resolve([
            {guid: 'g2', url: 'https://x.test/b.png', fileName: 'b.png', mimeType: 'image/png', mimeIcon: 'mime-image'},
        ])});

        clickUpload();
        await flush();
        expect(serialize()).to.equal('![b.png](file-guid:g2 "b.png")');
    });

    it("leaves the text as it was when the host uploads nothing or fails", async () => {
        for (const upload of [() => Promise.resolve([]), () => Promise.reject(new Error('refused'))]) {
            initEditor({upload}, 'Text');
            clickUpload();
            await flush();
            expect(serialize()).to.equal('Text');
            expect(document.querySelector('#stage .loader')).to.be.null;
        }
    });
});
