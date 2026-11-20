import { describe, it, expect, vi, beforeAll, afterAll } from 'vitest';

import {Slice} from 'prosemirror-model';

import {initEditor, serialize} from '../../testEditor.js';

const flush = () => new Promise((resolve) => setTimeout(resolve, 0));

const file = (name = 'a.pdf') => new File(['content'], name, {type: 'application/pdf'});

const uploaded = (guid, name) => [{guid, url: 'https://x.test/' + name, fileName: name, mimeType: 'application/pdf'}];

// jsdom has no DragEvent and lays nothing out: drop a file at the given document position
const dispatchDrop = (editor, files, pos = null) => {
    const event = new Event('drop', {bubbles: true, cancelable: true});
    Object.defineProperty(event, 'dataTransfer', {value: {files}});

    if (pos !== null) {
        editor.view.posAtCoords = () => ({pos, inside: -1});
    }

    let reachedBody = false;
    const onBody = () => reachedBody = true;
    document.body.addEventListener('drop', onBody);
    editor.view.dom.dispatchEvent(event);
    document.body.removeEventListener('drop', onBody);

    return {event, reachedBody};
};

describe("Plugin:clipboard, dropped files", () => {
    // jsdom does not implement it, but ProseMirror asks for the element under the drop point
    beforeAll(() => {
        document.elementFromPoint = () => null;
    });

    afterAll(() => {
        delete document.elementFromPoint;
    });

    it("uploads the dropped files and inserts them into the text, not letting the event reach the form", async () => {
        const upload = vi.fn(() => Promise.resolve(uploaded('g1', 'a.pdf')));
        const editor = initEditor({upload});
        const dropped = file();

        const {event, reachedBody} = dispatchDrop(editor, [dropped]);

        expect(upload).toHaveBeenCalledWith([dropped], {accept: null});
        expect(event.defaultPrevented).to.be.true;
        expect(reachedBody).to.be.false;

        await flush();
        expect(serialize()).to.equal('[a.pdf](file-guid:g1)');
        expect(document.querySelector('#stage .loader')).to.be.null;
    });

    it("inserts the dropped file at the drop position", async () => {
        const editor = initEditor({upload: () => Promise.resolve(uploaded('g2', 'b.pdf'))}, 'Text');

        dispatchDrop(editor, [file('b.pdf')], 3);

        await flush();
        expect(serialize()).to.equal('Te[b.pdf](file-guid:g2)xt');
    });

    it("leaves moving of the editor's own content to the editor, even when the browser carries it as a file", () => {
        const upload = vi.fn();
        const editor = initEditor({upload});
        editor.view.dragging = {slice: Slice.empty, move: true};

        const {event, reachedBody} = dispatchDrop(editor, [file()]);

        expect(upload).not.toHaveBeenCalled();
        expect(event.defaultPrevented).to.be.false;
        expect(reachedBody).to.be.false;
    });

    it("leaves a drop without files to the editor", () => {
        const upload = vi.fn();
        const editor = initEditor({upload});

        const {event} = dispatchDrop(editor, []);

        expect(upload).not.toHaveBeenCalled();
        expect(event.defaultPrevented).to.be.false;
    });

    it("leaves the drop to the form when the editor cannot upload", () => {
        const editor = initEditor({});

        const {event, reachedBody} = dispatchDrop(editor, [file()]);

        expect(event.defaultPrevented).to.be.false;
        expect(reachedBody).to.be.true;
    });
});
