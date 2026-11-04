import { describe, it, expect } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {initEditor, serialize, toHtml, clickMenuItem} from '../../testEditor.js';

describe("Plugin:video", () => {
    it("test parse and serialize video media flags", () => {
        initEditor('![My video](file-guid:abcd-1234 {video controls autoplay muted loop})');

        expect(serialize()).to.equal('![My video](file-guid:abcd-1234 video controls autoplay muted loop)');
    });

    it("test render video HTML from markdown media flags", () => {
        initEditor('![My video><](https://example.com/video.mp4 {video controls muted})');

        const html = toHtml();
        expect(html).to.contain('<video');
        expect(html).to.contain('src="https://example.com/video.mp4"');
        expect(html).to.contain('title="My video"');
        expect(html).to.contain('controls');
        expect(html).to.contain('muted');
        expect(html).to.not.contain('float=');
    });

    it("test parse video media flags without braces", () => {
        initEditor('![My video](file-guid:abcd-1234 video controls muted loop)');

        expect(serialize()).to.equal('![My video](file-guid:abcd-1234 video controls muted loop)');
    });

    it("test keep video media flags on source mode switch", () => {
        initEditor('![My video><](file-guid:abcd-1234 video controls autoplay muted loop =300x200)');

        clickMenuItem('source');
        clickMenuItem('source');

        expect(serialize()).to.equal('![My video><](file-guid:abcd-1234 video controls autoplay muted loop =300x200)');
    });

    it("test serialize float with title suffix", () => {
        initEditor('![My video><](file-guid:abcd-1234 video controls =300x200)');
        expect(serialize()).to.equal('![My video><](file-guid:abcd-1234 video controls =300x200)');
    });
});
