import { describe, it, expect } from 'vitest';
import {initEditor, toHtml, serialize, simulateInputRule, setSelection, clickMenuItem, type} from '../../testEditor.js';

describe("Plugin:strong", () => {
    it("test init strong text", () => {
        initEditor('**This is bold**');
        expect(toHtml()).to.equal('<p><strong>This is bold</strong></p>');
        expect(serialize()).to.equal('**This is bold**');
    });

    it("test strong input rule", () => {
        initEditor();
        expect(simulateInputRule('**Make me bold!**')).to.be.true;
        expect(toHtml()).to.equal('<p><strong>Make me bold!</strong></p>');
        expect(serialize()).to.equal('**Make me bold!**');
    });

    it("test strong menu item mark selection", () => {
        let editor = initEditor('Make this bold!');
        setSelection(5,10);
        clickMenuItem('markStrong');
        expect(toHtml()).to.equal('<p>Make <strong>this</strong> bold!</p>');
    });

    it("test strong menu item mark type", () => {
        let editor = initEditor();
        clickMenuItem('markStrong');
        type('Test', editor);
        expect(toHtml()).to.equal('<p><strong>Test</strong></p>');

    });
});