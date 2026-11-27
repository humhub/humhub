import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

const STORAGE_KEY = 'RichTextEditor.backup';

// The editor bundle as far as the widget uses it: the markdown it was given is what it serializes
class FakeMarkdownEditor {
    constructor() {
        this.markdown = '';
    }

    init(markdown = '') {
        this.markdown = markdown;
        this.view = { focus() {} };
    }

    serialize() {
        return this.markdown;
    }

    clear() {
        this.init();
    }
}

globalThis.prosemirror = { MarkdownEditor: FakeMarkdownEditor, MentionProvider: function () {} };
globalThis.humhub.modules['ui.widget'] = {
    Widget: {
        extend: () => function (node) {
            this.$ = $(node);
            this.options = {};
        },
    },
};
globalThis.humhub.modules.util = { object: { inherits: () => {} } };
let userGuid = 'user-a';
globalThis.humhub.modules.user = { guid: () => userGuid };

await import('../../modules/content/resources/js/humhub.ui.richtext.prosemirror.js');

const { RichTextEditor } = globalThis.humhub.modules['ui.richtext.prosemirror'];

// Renders the field as the PHP widget does (the saved text in the mount element, the input
// next to it) and boots the editor on it
const mount = (savedText = '', { id = 'post_input_1', backupInterval = 3, parent = document.body } = {}) => {
    document.getElementById(id)?.remove();
    document.getElementById(id + '_input')?.remove();

    const input = document.createElement('textarea');
    input.id = id + '_input';
    const node = document.createElement('div');
    node.id = id;
    const saved = document.createElement('div');
    saved.setAttribute('data-ui-richtext', '');
    saved.textContent = savedText;
    node.append(saved);
    parent.append(input, node);

    const editor = new RichTextEditor(node);
    editor.options = { backupInterval, backupCookieKey: STORAGE_KEY };
    editor.init();
    return editor;
};

const type = (editor, text) => {
    editor.editor.markdown = text;
};

const stored = () => JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '{}');

describe('RichTextEditor draft backup', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        sessionStorage.clear();
        userGuid = 'user-a';
        window.history.replaceState({}, '', '/s/test/');
    });

    afterEach(() => {
        vi.useRealTimers();
        document.body.replaceChildren();
    });

    it('restores the draft of a form opened again', () => {
        type(mount(), 'unsent comment');
        vi.advanceTimersByTime(3000);

        const editor = mount();

        expect(editor.editor.serialize()).toBe('unsent comment');
        // The form can be submitted without the editor ever losing the focus
        expect(document.getElementById('post_input_1_input').value).toBe('unsent comment');
    });

    it('keeps the draft to the page it was typed on', () => {
        type(mount(), 'for the space test');
        vi.advanceTimersByTime(3000);

        window.history.replaceState({}, '', '/s/test2/');

        expect(mount().editor.serialize()).toBe('');
    });

    it('keeps the draft to the user who typed it', () => {
        type(mount(), 'private');
        vi.advanceTimersByTime(3000);

        userGuid = 'user-b';

        expect(mount().editor.serialize()).toBe('');
    });

    it('stores nothing while the text is the one the editor opened with', () => {
        mount('saved text');
        vi.advanceTimersByTime(9000);

        expect(sessionStorage.getItem(STORAGE_KEY)).toBeNull();
    });

    it('restores an edit onto the text it was made on', () => {
        type(mount('saved text'), 'saved text, edited');
        vi.advanceTimersByTime(3000);

        expect(mount('saved text').editor.serialize()).toBe('saved text, edited');
    });

    it('does not restore an edit once the saved text has changed', () => {
        type(mount('saved text'), 'saved text, edited');
        vi.advanceTimersByTime(3000);

        // Saved in the meantime, by this user or another one
        expect(mount('newer text').editor.serialize()).toBe('newer text');
    });

    it('restores onto a text set after the editor booted', () => {
        type(mount('saved text'), 'saved text, edited');
        vi.advanceTimersByTime(3000);

        // As RichTextField.vue's setValue(): the editor boots empty, the text comes after
        const editor = mount('');
        expect(editor.editor.serialize()).toBe('');

        editor.editor.init('saved text');
        editor.$.trigger('restoreBackup');

        expect(editor.editor.serialize()).toBe('saved text, edited');
    });

    it('drops the draft when the editor is cleared after a submit', () => {
        const editor = mount();
        type(editor, 'sent');
        vi.advanceTimersByTime(3000);

        editor.$.trigger('clear');
        vi.advanceTimersByTime(3000);

        expect(stored()).toEqual({});
        expect(mount().editor.serialize()).toBe('');
    });

    it('drops the draft when the text is discarded, and backs up no more', () => {
        const editor = mount('saved text');
        type(editor, 'saved text, abandoned');
        vi.advanceTimersByTime(3000);

        editor.$.trigger('discardBackup');
        type(editor, 'saved text, abandoned more');
        vi.advanceTimersByTime(3000);

        expect(stored()).toEqual({});
        expect(mount('saved text').editor.serialize()).toBe('saved text');
    });

    it('drops the draft when its form is submitted, and backs up what is typed after that', () => {
        const form = document.createElement('form');
        document.body.append(form);
        const editor = mount('saved text', { parent: form });
        const button = document.createElement('button');
        button.type = 'submit';
        form.append(button);

        type(editor, 'saved text, sent');
        vi.advanceTimersByTime(3000);
        button.dispatchEvent(new MouseEvent('click', { bubbles: true }));
        vi.advanceTimersByTime(3000);

        // Saved now, or back with an error: the text is still in the editor
        expect(stored()).toEqual({});

        type(editor, 'saved text, sent, corrected');
        vi.advanceTimersByTime(3000);
        expect(Object.values(stored())[0].text).toBe('saved text, sent, corrected');
    });

    it('backs up no more once its form is gone from the page', () => {
        const editor = mount();
        type(editor, 'old draft');
        vi.advanceTimersByTime(3000);

        // Another page loaded by pjax: the form is gone, and the draft is dropped by a submit there
        editor.$.remove();
        sessionStorage.clear();
        vi.advanceTimersByTime(9000);

        expect(sessionStorage.getItem(STORAGE_KEY)).toBeNull();
    });

    it('ignores drafts stored in the former format', () => {
        sessionStorage.setItem(STORAGE_KEY, JSON.stringify({ post_input_1_input: 'old format' }));

        expect(mount().editor.serialize()).toBe('');
    });

    it('keeps no backup when switched off', () => {
        type(mount('', { backupInterval: 0 }), 'not backed up');
        vi.advanceTimersByTime(9000);

        expect(sessionStorage.getItem(STORAGE_KEY)).toBeNull();
    });
});
