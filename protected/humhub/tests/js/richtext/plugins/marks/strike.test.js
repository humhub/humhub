import { describe, it, expect } from 'vitest';
import {initEditor, toHtml, serialize, simulateInputRule, setSelection, clickMenuItem, type} from '../../testEditor.js';

describe("Plugin:strike", () => {
    it("test init striked text", () => {
        initEditor('~~This is striked~~');
        expect(toHtml()).to.equal('<p><s>This is striked</s></p>');
        expect(serialize()).to.equal('~~This is striked~~');
    });

    // TODO: Implment strike input rule
    /*
        it("test emphasized input rule", (done) => {
            initEditor();
            expect(simulateInputRule('*Make me emphasized!*')).to.be.true;
            expect(toHtml()).to.equal('<p><strong>Make me bold!</strong></p>');
            expect(serialize()).to.equal('*Make me emphasized!*');
            done();
        });
        */

    it("test striked menu item mark selection", () => {
        let editor = initEditor('Make this striked!');
        setSelection(5,10);
        clickMenuItem('markStrikethrough');
        expect(toHtml()).to.equal('<p>Make <s>this</s> striked!</p>');
    });


   it("test striked menu item mark type", () => {
       let editor = initEditor();
       clickMenuItem('markStrikethrough');
       type('Test', editor);
       expect(toHtml()).to.equal('<p><s>Test</s></p>');
   });

});