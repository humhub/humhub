import { describe, it, expect } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {initEditor, type, toHtml, serialize, clickDropdownMenuItem, pressKeyTab, pressKeyShiftTab, clickMenuItem,
    expectMenuItemVisible, pressKeyEnter, insertText, setSelection
} from '../../testEditor.js';

const TABLE_HTML = '<table><tbody>'
    + '<tr><th><p><br class="ProseMirror-trailingBreak"></p></th><th><p><br class="ProseMirror-trailingBreak"></p></th></tr>'
    + '<tr><td><p><br class="ProseMirror-trailingBreak"></p></td><td><p><br class="ProseMirror-trailingBreak"></p></td></tr>'
    + '</tbody></table>';

const MD_TABLE = '|  |  |\n|--|--|\n|  |  |';
const P_EMPTY = '<p><br class="ProseMirror-trailingBreak"></p>';

// Puts the cursor into the last cell of the first table
const cursorInLastCellOfFirstTable = (editor) => {
    let pos = null;
    let found = false;
    editor.view.state.doc.descendants((node, nodePos) => {
        if (found) {
            return false;
        }
        if (node.type.name === 'table') {
            node.descendants((cell, cellPos) => {
                if (cell.type.name === 'table_cell') {
                    pos = nodePos + 1 + cellPos + 2;
                }
            });
            found = true;
            return false;
        }
    });
    setSelection(pos, null, editor);
};

describe("Plugin:tab_behavior", () => {
    it("test tab in paragraph", () => {
        const editor = initEditor();
        type('Test', editor);

        editor.focus();

        expect(toHtml()).to.equal('<p>Test</p>');
        expect(serialize()).to.equal('Test');

        pressKeyTab();
        expect(toHtml()).to.equal('<p>Test</p>');
        expect(serialize()).to.equal('Test');
        // expect(editor.hasFocus()).to.be.false;

        pressKeyShiftTab();
        expect(toHtml()).to.equal('<p>Test</p>');
        expect(serialize()).to.equal('Test');

    });

    it("test tab in bullet list", () => {
        const editor = initEditor();
        clickMenuItem('resizeNav');
        expectMenuItemVisible('group');
        clickMenuItem('wrapBulletList');

        insertText('first item');
        pressKeyEnter();
        insertText('second item');
        expect(toHtml()).to.equal('<ul data-tight="true"><li><p>first item</p></li><li><p>second item</p></li></ul>');
        expect(serialize()).to.equal('- first item\n- second item');

        pressKeyTab();
        expect(toHtml()).to.equal('<ul data-tight="true"><li><p>first item</p><ul data-tight="true"><li><p>second item</p></li></ul></li></ul>');
        expect(serialize()).to.equal('- first item\n  - second item');

        pressKeyShiftTab();
        expect(toHtml()).to.equal('<ul data-tight="true"><li><p>first item</p></li><li><p>second item</p></li></ul>');
        expect(serialize()).to.equal('- first item\n- second item');

    });

    it("test tab in ordered list", () => {
        const editor = initEditor();
        clickMenuItem('resizeNav');
        expectMenuItemVisible('group');
        clickMenuItem('wrapOrderedList');

        insertText('first item');
        pressKeyEnter();
        insertText('second item');
        expect(toHtml()).to.equal('<ol data-tight="true"><li><p>first item</p></li><li><p>second item</p></li></ol>');
        expect(serialize()).to.equal('1. first item\n2. second item');

        pressKeyTab();
        expect(toHtml()).to.equal('<ol data-tight="true"><li><p>first item</p><ol data-tight="true"><li><p>second item</p></li></ol></li></ol>');
        expect(serialize()).to.equal('1. first item\n   1. second item');

        pressKeyShiftTab();
        expect(toHtml()).to.equal('<ol data-tight="true"><li><p>first item</p></li><li><p>second item</p></li></ol>');
        expect(serialize()).to.equal('1. first item\n2. second item');

    });

    it("test tab in table", () => {
        const editor = initEditor();
        clickMenuItem('resizeNav');
        expectMenuItemVisible('insertTable');
        clickMenuItem('insertTable');

        // Checking create table
        const formInputs = $('.ProseMirror-prompt input');
        $(formInputs[0]).val(2);
        $(formInputs[1]).val(2);
        $('.ProseMirror-prompt-submit').click();

        // Checking init cursor position and table rows
        let $from = editor.view.state.selection.$from;
        let depth = $from.depth;
        let table = $from.node(depth - 3);

        expect($from.index(depth - 3)).to.equal(0);
        expect($from.index(depth - 2)).to.equal(0);
        expect(table.childCount).to.equal(2);
        expect(toHtml()).to.equal(TABLE_HTML);

        // Tab walks the remaining three cells...
        for (let i = 0; i < 3; i++) {
            pressKeyTab();
        }
        $from = editor.view.state.selection.$from;
        depth = $from.depth;
        expect($from.index(depth - 3)).to.equal(1);
        expect($from.index(depth - 2)).to.equal(1);

        // ...and the fourth Tab leaves the table into a new paragraph after it, instead of adding a row
        pressKeyTab();
        $from = editor.view.state.selection.$from;
        expect($from.depth).to.equal(1);
        expect($from.parent.type.name).to.equal('paragraph');
        expect(editor.view.state.doc.firstChild.childCount).to.equal(2);
        expect(toHtml()).to.equal(TABLE_HTML + '<p><br class="ProseMirror-trailingBreak"></p>');

        // Outside a table and a list the editor does not handle Shift+Tab
        pressKeyShiftTab();
        expect(toHtml()).to.equal(TABLE_HTML + '<p><br class="ProseMirror-trailingBreak"></p>');

    });

    it("test shift tab in the first table cell leaves the table", () => {
        const editor = initEditor();
        clickMenuItem('resizeNav');
        expectMenuItemVisible('insertTable');
        clickMenuItem('insertTable');
        const formInputs = $('.ProseMirror-prompt input');
        $(formInputs[0]).val(2);
        $(formInputs[1]).val(2);
        $('.ProseMirror-prompt-submit').click();
        expect(toHtml()).to.equal(TABLE_HTML);

        pressKeyShiftTab();

        const $from = editor.view.state.selection.$from;
        expect($from.depth).to.equal(1);
        expect($from.parent.type.name).to.equal('paragraph');
        expect($from.pos).to.equal(1);
        expect(toHtml()).to.equal('<p><br class="ProseMirror-trailingBreak"></p>' + TABLE_HTML);
    });

    it("test tab after the last table cell moves into the following paragraph", () => {
        const editor = initEditor(MD_TABLE + '\n\nafter');
        expect(toHtml()).to.equal(TABLE_HTML + '<p>after</p>');
        const childCount = editor.view.state.doc.childCount;
        cursorInLastCellOfFirstTable(editor);

        pressKeyTab();

        const $from = editor.view.state.selection.$from;
        expect($from.parent.type.name).to.equal('paragraph');
        expect($from.parent.textContent).to.equal('after');
        expect($from.parentOffset).to.equal(0);
        expect(editor.view.state.doc.childCount).to.equal(childCount);
        expect(toHtml()).to.equal(TABLE_HTML + '<p>after</p>');
    });

    it("test tab after the last table cell before another table inserts a paragraph", () => {
        const editor = initEditor(MD_TABLE + '\n\n' + MD_TABLE);
        expect(toHtml()).to.equal(TABLE_HTML + TABLE_HTML);
        cursorInLastCellOfFirstTable(editor);

        pressKeyTab();

        const $from = editor.view.state.selection.$from;
        expect($from.parent.type.name).to.equal('paragraph');
        expect($from.depth).to.equal(1);
        expect(toHtml()).to.equal(TABLE_HTML + P_EMPTY + TABLE_HTML);
    });

    it("test tab in code block", () => {
        const editor = initEditor();
        type('This is a code block');
        clickDropdownMenuItem('type','makeCodeBlock');
        const initHtml = '<div class="cm-line">This is a code block</div>';
        expect(toHtml('#stage .ProseMirror .cm-editor .cm-content')).to.equal(initHtml);
        expect(serialize()).to.equal('```\nThis is a code block\n```');
        // editor.focus();
        $('<div class="cm-line">This is a code block</div>').click().focus();
        pressKeyTab();
        expect(toHtml('#stage .ProseMirror .cm-editor .cm-content')).to.equal('<div class="cm-line">This is a code block</div>');
        // expect(toHtml('#stage .ProseMirror .cm-editor .cm-content')).to.equal('<div class="cm-line">This is a code block</div');
        // expect(serialize()).to.equal('```\n\tThis is a code block\n```');
        pressKeyShiftTab();
        expect(toHtml('#stage .ProseMirror .cm-editor .cm-content')).to.equal(initHtml);
        expect(serialize()).to.equal('```\nThis is a code block\n```');
    });
});
