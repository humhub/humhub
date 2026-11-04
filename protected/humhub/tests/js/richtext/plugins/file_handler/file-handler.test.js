import { describe, it, expect, vi, afterEach } from 'vitest';

import {initEditor, clickMenuItem} from '../../testEditor.js';

// The handler links a host hands over through the `fileHandlers` option
const handlers = () => $('<ul>'
    + '<li><a href="#" data-action-process="file-handler" data-action-click="file.uploadByType" data-action-params=\'{"type":"image/*"}\'>Attach an image</a></li>'
    + '<li><a href="#" data-action-process="file-handler" data-action-click="mymodule.create">Create a document</a></li>'
    + '</ul>')[0];

// The entries sit in the insert dropdown
const openInsertMenu = () => clickMenuItem('insert-dropdown');

const clickHandlerItem = (index) => {
    openInsertMenu();
    $('.ProseMirror-menu-insertFileHandler')[index].dispatchEvent(new Event('mousedown', {bubbles: true}));
};

describe("Plugin:file_handler, the fileHandlers option", () => {
    afterEach(() => {
        vi.restoreAllMocks();
    });

    it("offers the handlers of the given element", () => {
        initEditor({fileHandlers: handlers()});
        openInsertMenu();
        const items = $('.ProseMirror-menu-insertFileHandler');
        expect(items.length).to.equal(2);
        expect(items.eq(0).text()).to.contain('Attach an image');
        expect(items.eq(1).text()).to.contain('Create a document');
    });

    it("takes a function returning the element", () => {
        initEditor({fileHandlers: () => handlers()});
        openInsertMenu();
        expect($('.ProseMirror-menu-insertFileHandler').length).to.equal(2);
    });

    it("uploads an \"upload this type\" entry through the upload option, with its type", () => {
        const upload = vi.fn(() => Promise.resolve([]));
        const element = handlers();
        const legacyAction = vi.fn();
        $(element).find('a').on('click', legacyAction);
        initEditor({upload, fileHandlers: element});

        clickHandlerItem(0);

        expect(upload).toHaveBeenCalledWith(null, {accept: 'image/*'});
        expect(legacyAction).not.toHaveBeenCalled();
    });

    it("runs any other entry's own action", () => {
        const upload = vi.fn(() => Promise.resolve([]));
        const element = handlers();
        const action = vi.fn();
        $(element).find('a').eq(1).on('click', action);
        initEditor({upload, fileHandlers: element});

        clickHandlerItem(1);

        expect(action).toHaveBeenCalledTimes(1);
        expect(upload).not.toHaveBeenCalled();
    });
});
