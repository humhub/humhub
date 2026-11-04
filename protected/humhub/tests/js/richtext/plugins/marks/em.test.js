import { describe, it, expect } from 'vitest';
import {initEditor, toHtml, serialize, setSelection, clickMenuItem, type} from '../../testEditor.js';

describe("Plugin:em", () => {
    it("test init emphasized text", () => {
        initEditor('_This is emphasized_');
        expect(toHtml()).to.equal('<p><em>This is emphasized</em></p>');
        expect(serialize()).to.equal('*This is emphasized*');
    });

    it("test init emphasized text with asterisk", () => {
        initEditor('*This is emphasized*');
        expect(toHtml()).to.equal('<p><em>This is emphasized</em></p>');
        expect(serialize()).to.equal('*This is emphasized*');
    });

    it("test init emphasized text inside a word", () => {
        initEditor('This is em_phas_ized');
        expect(toHtml()).to.equal('<p>This is em_phas_ized</p>');
        expect(serialize()).to.equal('This is em_phas_ized');
    });

    it("test init emphasized text with asterisk inside a word", () => {
        initEditor('This is em*phas*ized');
        expect(toHtml()).to.equal('<p>This is em<em>phas</em>ized</p>');
        expect(serialize()).to.equal('This is em*phas*ized');
    });

    // TODO: Implement em input rule
    /*
        it("test emphasized input rule", (done) => {
            initEditor();
            expect(simulateInputRule('*Make me emphasized!*')).to.be.true;
            expect(toHtml()).to.equal('<p><strong>Make me bold!</strong></p>');
            expect(serialize()).to.equal('*Make me emphasized!*');
            done();
        });
    */

    it("test emphasized menu item mark selection", () => {
        let editor = initEditor('Make this emphasized!');
        setSelection(5,10);
        clickMenuItem('markEm');
        expect(toHtml()).to.equal('<p>Make <em>this</em> emphasized!</p>');
    });

   it("test emphasized menu item mark type", () => {
       let editor = initEditor();
       clickMenuItem('markEm');
       type('Test', editor);
       expect(toHtml()).to.equal('<p><em>Test</em></p>');
   });
});
