import { describe, it, expect } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {initEditor, toHtml, serialize, simulateInputRule,
    clickDropdownMenuItem, type, insertText,
    menuItemDisabled, pressKeyArrowDown} from '../../testEditor.js';

describe("Plugin:code block", () => {
    it("test init code block", () => {
        window.debugblock = true;
        initEditor('```\nThis is a code block\n```');
        expect(toHtml('#stage .ProseMirror .cm-editor .cm-content')).to.equal('<div class="cm-line">This is a code block</div>');
        expect(serialize()).to.equal('```\nThis is a code block\n```');
    });

    it("test code block input rule", () => {
        let editor = initEditor();
        expect(simulateInputRule('```')).to.be.true;
        insertText('This is a code block');
        expect(toHtml('#stage .ProseMirror .cm-editor .cm-content')).to.equal('<div class="cm-line">This is a code block</div>');
        expect(serialize()).to.equal('```\nThis is a code block\n```');
    });

    it("test code block menu item wrap line", () => {
        let editor = initEditor();
        type('This is a code block');
        clickDropdownMenuItem('type','makeCodeBlock');
        expect(toHtml('#stage .ProseMirror .cm-editor .cm-content')).to.equal('<div class="cm-line">This is a code block</div>');
        expect(serialize()).to.equal('```\nThis is a code block\n```');
    });

    it("test code block marks disabled", () => {
        let editor = initEditor();
        type('This is a code block');
        clickDropdownMenuItem('type','makeCodeBlock');
        expect(menuItemDisabled('markStrong')).to.be.true;
        expect(menuItemDisabled('markEm')).to.be.true;
        expect(menuItemDisabled('markStrikethrough ')).to.be.true;
        expect(menuItemDisabled('insertEmoji ')).to.be.true;
    });

    it("test leave code block on arrow down", () => {
        let editor = initEditor();
        type('This is a code block');
        clickDropdownMenuItem('type','makeCodeBlock');
        pressKeyArrowDown();
        expect(toHtml('#stage .ProseMirror .cm-editor .cm-content')).to.equal('<div class="cm-line">This is a code block</div>');
    });
});
