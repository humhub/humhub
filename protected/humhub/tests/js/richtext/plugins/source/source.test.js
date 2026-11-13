import { describe, it, expect } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {
    initEditor,
    selectSource,
    toHtml,
    serialize,
    clickMenuItem,
} from '../../testEditor.js';

describe("Plugin:source", () => {
    const canAssertFocus = function () {
        const ua = (typeof navigator !== 'undefined' && navigator.userAgent) ? navigator.userAgent : '';
        return !/HeadlessChrome/i.test(ua);
    };
    it("test strong mark in source mode", () => {
        initEditor('Test source mode');
        clickMenuItem('source');
        selectSource(5, 11);
        clickMenuItem('markStrong')
        expect(serialize()).to.equal('Test **source** mode')
        clickMenuItem('source');
        expect(toHtml()).to.equal('<p>Test <strong>source</strong> mode</p>')
    });

    it("test em mark in source mode", () => {
        initEditor('Test source mode');
        clickMenuItem('source');
        selectSource(5, 11);
        clickMenuItem('markEm')
        expect(serialize()).to.equal('Test *source* mode')
        clickMenuItem('source');
        expect(toHtml()).to.equal('<p>Test <em>source</em> mode</p>')
    });

    it("test striketrhough mark in source mode", () => {
        initEditor('Test source mode');
        clickMenuItem('source');
        selectSource(5, 11);
        clickMenuItem('markStrikethrough')
        expect(serialize()).to.equal('Test ~~source~~ mode')
        clickMenuItem('source');
        expect(toHtml()).to.equal('<p>Test <s>source</s> mode</p>')
    });

    it("test prevent redundant marking in source", () => {
        initEditor('Test source mode');
        clickMenuItem('source');
        selectSource(5, 11);
        clickMenuItem('markStrikethrough')
        clickMenuItem('markStrikethrough')
        expect(serialize()).to.equal('Test source mode')
    });

    it("test the source is named as the editor", () => {
        initEditor({edit: true, attributes: {'aria-label': 'Post text', 'aria-describedby': 'post-hint'}}, 'Test');
        clickMenuItem('source');
        const $textarea = $('#stage .ProseMirror-editor-source');
        expect($textarea.attr('aria-label')).to.equal('Post text');
        expect($textarea.attr('aria-describedby')).to.equal('post-hint');
        clickMenuItem('source');
    });

    it("test editor focus on switch mode", () => new Promise((done) => {
        const editor = initEditor('Test source mode');
        clickMenuItem('resizeNav');
        clickMenuItem('source');
        setTimeout(() => {
            if (canAssertFocus()) {
                expect(editor.hasFocus()).to.be.true;
                expect(editor.context.$source.is(':focus')).to.be.true;
            }
            clickMenuItem('source');
            setTimeout(() => {
                if (canAssertFocus()) {
                    expect($(editor.view.dom).is(':focus')).to.be.true;
                    expect(editor.hasFocus()).to.be.true;
                }
                done();
            }, 0);
        }, 0);
    }));
});
