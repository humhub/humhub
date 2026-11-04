import { describe, it, expect } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {initEditor} from '../testEditor.js';

describe("Core:translate", () => {

    it("test translate without option", () => {
        let editor = initEditor();
        expect(editor.context.translate('Test me!')).to.equal('Test me!');
    });

    it("test translate with instance option and map", () => {
        let editor = initEditor({
            translate: {
                'Test me!': 'Teste mich!'
            }
        });
        expect(editor.context.translate('Test me!')).to.equal('Teste mich!');
        expect(editor.context.translate('Not translated...')).to.equal('Not translated...');
    });

    it("test translate with global option and callback", () => {
        window.humhub.richtext.globalOptions = { translate: (key) => { return { 'Test me!': 'Teste mich!'}[key]}};
        let editor = initEditor();
        expect(editor.context.translate('Test me!')).to.equal('Teste mich!');
        expect(editor.context.translate('Not translated...')).to.equal('Not translated...');
    });
});