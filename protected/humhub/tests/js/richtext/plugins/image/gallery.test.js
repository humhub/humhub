import { describe, expect, it, vi } from 'vitest';

import {initView, viewToHtml} from '../../testEditor.js';

// The file module resolves `file-guid:` sources: the file itself, or its display version for the media viewer
vi.mock('../../../../../resources/js/richtext/core/humhub-bridge.js', async (importOriginal) => ({
    ...(await importOriginal()),
    filterFileUrl: (url, mode) => {
        const guid = url.indexOf('file-guid:') === 0 ? url.substring('file-guid:'.length) : null;
        if (!guid) {
            return {url, guid: null};
        }
        return {url: (mode === 'display' ? 'https://example.com/display/' : 'https://example.com/file/') + guid, guid};
    },
}));

describe('Plugin:image in the media viewer', () => {
    it('shows the display version of an uploaded image, the original stays the source', () => {
        initView('![Cat](file-guid:abcd-1234)');
        const html = viewToHtml();

        expect(html).to.contain('src="https://example.com/file/abcd-1234"');
        expect(html).to.contain('data-ui-gallery-src="https://example.com/display/abcd-1234"');
    });

    it('shows an external image as it is', () => {
        initView('![Cat](https://example.org/cat.jpg)');

        expect(viewToHtml()).to.contain('src="https://example.org/cat.jpg"');
        expect(viewToHtml()).to.not.contain('data-ui-gallery-src');
    });
});
