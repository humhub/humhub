import { describe, it, expect, afterEach } from 'vitest';

import {initEditor} from '../testEditor.js';

const wait = (delay) => new Promise((resolve) => setTimeout(resolve, delay));

// An inline editor (`.focusMenu`) shows its menu while it has the focus
const initFocusMenuEditor = () => {
    $('#stage').addClass('focusMenu');
    const editor = initEditor();
    const menu = $('#stage .ProseMirror-menubar');
    $('#stage .ProseMirror').trigger('focus');
    return menu;
};

const isHidden = (menu) => menu.hasClass('d-none') || !!menu.data('isHiding');

describe("Menu: focus menu", () => {
    afterEach(() => {
        $('#stage').removeClass('focusMenu');
    });

    it("shows the menu on focus", () => {
        const menu = initFocusMenuEditor();
        expect(isHidden(menu)).to.be.false;
    });

    it("hides the menu when the editor loses the focus", async () => {
        const menu = initFocusMenuEditor();
        $('#stage .ProseMirror').trigger('blur');
        await wait(0);
        expect(isHidden(menu)).to.be.true;
    });

    it("hides the menu only once a press elsewhere is released, so it does not move what was pressed", async () => {
        const menu = initFocusMenuEditor();

        document.body.dispatchEvent(new MouseEvent('mousedown', {bubbles: true}));
        $('#stage .ProseMirror').trigger('blur');
        await wait(0);
        expect(isHidden(menu)).to.be.false;

        document.body.dispatchEvent(new MouseEvent('mouseup', {bubbles: true}));
        await wait(0);
        expect(isHidden(menu)).to.be.true;
    });
});
