import { describe, it, expect } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {initEditor, insertText, toHtml, clickMenuItem, expectMenuItemVisible, expectMenuItemNotVisible} from '../../testEditor.js';

describe("Plugin:history", () => {

    it("test init with history", () => {
        initEditor({exclude: ['resizeNav']});
        expectMenuItemVisible('undo');
        expectMenuItemVisible('redo');
    });

    it("test disable history", () => {
        initEditor({exclude: ['resizeNav', 'history']});
        expectMenuItemNotVisible('undo');
        expectMenuItemNotVisible('redo');
    });

    it("test undo", () => new Promise((done) => {
        initEditor({exclude: ['resizeNav']});
        insertText('TEST1');

        setTimeout(() => {
            insertText('TEST2');
            clickMenuItem('undo');
            setTimeout(() => {
                const html = toHtml();
                expect(html).to.not.equal('<p>TEST1TEST2</p>');
                expect(['<p>TEST1</p>', '<p><br class="ProseMirror-trailingBreak"></p>']).to.include(html);
                done();
            }, 0);
        }, 500);
    }));

    it("test redo", () => new Promise((done) => {
        initEditor({exclude: ['resizeNav']});
        insertText('TEST1');

        setTimeout(() => {
            insertText('TEST2');
            clickMenuItem('undo');
            clickMenuItem('redo');
            setTimeout(() => {
                expect(toHtml()).to.equal('<p>TEST1TEST2</p>');
                done();
            }, 0);
        }, 500);
    }));
});
