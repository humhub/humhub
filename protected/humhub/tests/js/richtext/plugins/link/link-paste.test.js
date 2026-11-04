import { describe, it, expect } from 'vitest';
import {initEditor, toHtml, serialize} from '../../testEditor.js';

describe("Plugin:link", () => {
    const dispatchPaste = (view, text) => {
        const dataTransfer = new DataTransfer();
        dataTransfer.setData('text/plain', text);
        const event = new ClipboardEvent('paste', {
            clipboardData: dataTransfer,
            bubbles: true,
            cancelable: true
        });
        view.dom.dispatchEvent(event);
    };

    it("does not swallow a trailing quote into a pasted link (regression for VS Code copy of a quoted URL)", () => {
        // oembed is excluded: loadOembeds() reaches into the full HumHub app runtime
        // (humhub.require(...)) which isn't available in this standalone test harness,
        // and it's unrelated to the link-paste bug under test here.
        const editor = initEditor({exclude: ['oembed']});
        dispatchPaste(editor.view, '="http://domain.tld:80"');

        const $link = $('#stage .ProseMirror a');

        expect($link.length).to.equal(1);
        expect($link.text()).to.equal('http://domain.tld:80');
        expect($link.attr('href')).to.equal('http://domain.tld:80');
        expect(toHtml()).to.include('http://domain.tld:80</a>"');

        expect(serialize()).to.equal('="[http://domain.tld:80](http://domain.tld:80)"');

    });

    it("renders and serializes a relative link without throwing (#655)", () => {
        initEditor({exclude: ['oembed']}, 'Test [link](/dashboard)');

        const $link = $('#stage .ProseMirror a');

        expect($link.length).to.equal(1);
        expect($link.text()).to.equal('link');
        expect($link.attr('href')).to.equal('/dashboard');
        expect(serialize()).to.equal('Test [link](/dashboard)');

    });
});
