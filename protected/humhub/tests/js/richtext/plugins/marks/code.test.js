import { describe, it, expect } from 'vitest';
import {initEditor, toHtml, serialize, simulateInputRule, setSelection, clickMenuItem, type} from '../../testEditor.js';

describe("Plugin:code", () => {
    it("test init code text", () => {
        initEditor('`This is code`');
        expect(toHtml()).to.equal('<p><code>This is code</code></p>');
        expect(serialize()).to.equal('`This is code`');
    });

    it("test code input rule", () => {
        initEditor();
        expect(simulateInputRule('`Make me code!`')).to.be.true;
        expect(toHtml()).to.equal('<p><code>Make me code!</code></p>');
        expect(serialize()).to.equal('`Make me code!`');
    });

    it("test code menu item mark selection", () => {
        let editor = initEditor('Make this code!');
        setSelection(5,10);
        clickMenuItem('markCode');
        expect(toHtml()).to.equal('<p>Make <code>this</code> code!</p>');
    });

    it("test code menu item mark type", () => {
        let editor = initEditor();
        clickMenuItem('markCode');
        type('Test', editor);
        expect(toHtml()).to.equal('<p><code>Test</code></p>');
    });
});