import { describe, it, expect } from 'vitest';
import {initEditor, initView, viewToHtml, serialize} from '../../testEditor.js';
import {isExtensionHref, extensionTargetUrl} from '../../../../../resources/js/richtext/core/util/linkUtil.js';

describe("Plugin:link, links of unknown extensions", () => {
    it("recognizes an extension href", () => {
        expect(isExtensionHref('linkpreview:https://example.com')).to.equal(true);
        expect(isExtensionHref('my-extension:id')).to.equal(true);
        expect(isExtensionHref('JavaScript:alert(1)')).to.equal(false);
        expect(isExtensionHref('data:text/html,x')).to.equal(false);
        expect(isExtensionHref('/relative')).to.equal(false);
        expect(isExtensionHref('c:\\path')).to.equal(false);
        expect(isExtensionHref('')).to.equal(false);
        expect(isExtensionHref(null)).to.equal(false);
    });

    it("takes the url out of an extension href", () => {
        expect(extensionTargetUrl('linkpreview:https://example.com/a?b=c')).to.equal('https://example.com/a?b=c');
        expect(extensionTargetUrl('linkpreview:http://example.com')).to.equal('http://example.com');
        expect(extensionTargetUrl('linkpreview:id')).to.equal(null);
        expect(extensionTargetUrl('https://example.com')).to.equal(null);
        expect(extensionTargetUrl('javascript:https://example.com')).to.equal(null);
    });

    it("keeps the title of a link through the editor round trip", () => {
        const markdown = 'Test [Title](https://example.com "Description")';
        initEditor({exclude: ['oembed']}, markdown);

        expect($('#stage .ProseMirror a').attr('href')).to.equal('https://example.com');
        expect(serialize()).to.equal(markdown);
    });

    it("keeps an unknown extension link through the editor round trip", () => {
        const markdown = 'Test [Title](my-extension:https://example.com/a "Description")';
        initEditor({exclude: ['oembed']}, markdown);

        const $link = $('#stage .ProseMirror a');
        expect($link.length).to.equal(1);
        expect($link.text()).to.equal('Title');
        // The editor shows a link a reader without the extension would get
        expect($link.attr('href')).to.equal('https://example.com/a');
        // ...but the markdown keeps the extension value
        expect(serialize()).to.equal(markdown);
    });

    it("keeps an unknown extension link without an url through the editor round trip", () => {
        initEditor({exclude: ['oembed']}, 'Test [Title](my-extension:some-id)');

        // Nothing to lead to in the editor, ...
        expect($('#stage .ProseMirror a').attr('href')).to.equal('#');
        // ...but the markdown keeps the extension value
        expect(serialize()).to.equal('Test [Title](my-extension:some-id)');
    });

    it("does not keep a script link", () => {
        initEditor({exclude: ['oembed']}, 'Test [Title](javascript:alert(1))');

        expect($('#stage .ProseMirror a[href^="javascript"]').length).to.equal(0);
        // Only text is left of it
        expect(serialize()).to.equal('Test \\[Title\\](javascript:alert(1))');
    });

    it("renders an unknown extension link of the view as a link to its url", () => {
        initView({exclude: ['oembed']}, '[Title](my-extension:https://example.com/a "Description")');

        const $link = $('#result a');
        expect($link.length).to.equal(1);
        expect($link.attr('href')).to.equal('https://example.com/a');
        expect($link.attr('title')).to.equal('Description');
        expect($link.text()).to.equal('Title');
    });
});
