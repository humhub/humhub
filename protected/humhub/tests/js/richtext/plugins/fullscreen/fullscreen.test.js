import { describe, it, expect } from 'vitest';

import { initEditor, clickMenuItem } from '../../testEditor.js';

describe("Plugin:fullscreen", () => {
    const button = () => $('#stage .ProseMirror-menu-fullScreen')[0];
    const keydown = (target, key, options = {}) => {
        const event = new KeyboardEvent('keydown', Object.assign({key, bubbles: true, cancelable: true}, options));
        target.dispatchEvent(event);
        return event;
    };

    it("test the button tells whether the editor is in full screen", () => {
        initEditor('Test');
        expect(button().getAttribute('aria-pressed')).to.equal('false');

        clickMenuItem('fullScreen');
        expect($('#stage').is('.fullscreen')).to.be.true;
        expect(button().getAttribute('aria-pressed')).to.equal('true');

        clickMenuItem('fullScreen');
        expect($('#stage').is('.fullscreen')).to.be.false;
        expect(button().getAttribute('aria-pressed')).to.equal('false');
    });

    it("test Escape leaves the full screen and focuses the button", () => {
        const editor = initEditor('Test');
        clickMenuItem('resizeNav');
        clickMenuItem('fullScreen');

        keydown(editor.view.dom, 'Escape');

        expect($('#stage').is('.fullscreen')).to.be.false;
        expect(button().getAttribute('aria-pressed')).to.equal('false');
        expect(document.activeElement).to.equal(button());
    });

    it("test Tab stays inside the editor in full screen", () => {
        const editor = initEditor('Test');
        clickMenuItem('fullScreen');

        editor.view.dom.focus();
        const event = keydown(editor.view.dom, 'Tab');
        expect(event.defaultPrevented).to.be.true;
        expect($('#stage')[0].contains(document.activeElement)).to.be.true;
        expect(document.activeElement).not.to.equal(editor.view.dom);

        keydown(editor.view.dom, 'Escape');
    });
});
