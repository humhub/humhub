import { describe, it, expect } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {initEditor, toHtml, serialize, simulateInputRule, pressKeyArrowDown, clickMenuItem, type, insertText} from '../../testEditor.js';

describe("Plugin:blockquote", () => {
    it("test init blockquote", () => {
        initEditor('> This is a block quote');
        expect(toHtml()).to.equal('<blockquote><p>This is a block quote</p></blockquote>');
        expect(serialize()).to.equal('> This is a block quote');
    });

    it("test strong input rule", () => {
        let editor = initEditor();
        expect(simulateInputRule('> ')).to.be.true;
        insertText('This is a block quote');
        expect(toHtml()).to.equal('<blockquote><p>This is a block quote</p></blockquote>');
        expect(serialize()).to.equal('> This is a block quote');
    });

    it("test blockquote menu item wrap line", () => {
        let editor = initEditor();
        type('This is a block quote');
        clickMenuItem('wrapBlockQuote');
        expect(toHtml()).to.equal('<blockquote><p>This is a block quote</p></blockquote>');
    });

    it("test double blockquote menu item wrap line", () => {
        let editor = initEditor();
        type('This is a block quote');
        clickMenuItem('wrapBlockQuote');
        clickMenuItem('wrapBlockQuote');
        expect(toHtml()).to.equal('<blockquote><blockquote><p>This is a block quote</p></blockquote></blockquote>');
    });

    it("test leave code block on arrow down", () => {
        let editor = initEditor();
        type('This is a block quote');
        clickMenuItem('wrapBlockQuote');
        pressKeyArrowDown();
        expect(toHtml()).to.equal('<blockquote><p>This is a block quote</p></blockquote><p><br class="ProseMirror-trailingBreak"></p>');
    });
});
