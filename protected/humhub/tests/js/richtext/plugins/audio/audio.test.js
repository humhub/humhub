import { describe, it, expect } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {initEditor, serialize, toHtml, clickMenuItem} from '../../testEditor.js';

describe("Plugin:audio", () => {
    it("test parse and serialize audio media flags", () => {
        initEditor('![My audio](file-guid:abcd-1234 {audio controls autoplay muted loop})');

        expect(serialize()).to.equal('![My audio](file-guid:abcd-1234 audio controls autoplay muted loop)');
    });

    it("test render audio HTML from markdown media flags", () => {
        initEditor('![My audio><](https://example.com/audio.mp3 {audio controls muted})');

        const html = toHtml();
        expect(html).to.contain('<audio');
        expect(html).to.contain('src="https://example.com/audio.mp3"');
        expect(html).to.contain('title="My audio"');
        expect(html).to.contain('controls');
        expect(html).to.contain('muted');
        expect(html).to.not.contain('float=');
    });

    it("test parse audio media flags without braces", () => {
        initEditor('![My audio](file-guid:abcd-1234 audio controls muted loop)');

        expect(serialize()).to.equal('![My audio](file-guid:abcd-1234 audio controls muted loop)');
    });

    it("test keep audio media flags on source mode switch", () => {
        initEditor('![My audio><](file-guid:abcd-1234 audio controls autoplay muted loop)');

        clickMenuItem('source');
        clickMenuItem('source');

        expect(serialize()).to.equal('![My audio><](file-guid:abcd-1234 audio controls autoplay muted loop)');
    });
});
