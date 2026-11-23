import { beforeEach, describe, expect, it } from 'vitest';

// The glue of the content module needs the editor bundle and the widget base only as far as it extends them
globalThis.prosemirror = { MarkdownEditor: class {}, MentionProvider: function () {} };
globalThis.humhub.modules['ui.widget'] = {
    Widget: {
        extend: () => function (node) {
            this.$ = $(node);
            this.options = {};
        },
    },
};
globalThis.humhub.modules.util = { object: { inherits: () => {} } };

await import('../../modules/content/resources/js/humhub.ui.richtext.prosemirror.js');

const { RichTextEditor } = globalThis.humhub.modules['ui.richtext.prosemirror'];

describe('RichTextEditor accessibility', () => {
    let editor;

    beforeEach(() => {
        const label = document.createElement('label');
        label.htmlFor = 'post_input';
        label.textContent = 'Message';
        const input = document.createElement('input');
        input.type = 'hidden';
        input.id = 'post_input';
        input.setAttribute('aria-describedby', 'post-error');
        const container = document.createElement('div');
        container.id = 'post';
        document.body.replaceChildren(label, input, container);
        editor = new RichTextEditor(container);
    });

    it('is a text box named by the label of its field', () => {
        const attributes = editor.getAccessibilityAttributes();

        expect(attributes.role).toBe('textbox');
        expect(attributes['aria-multiline']).toBe('true');
        expect(attributes['aria-labelledby']).toBe('post_input-label');
    });

    it('says how to leave it, after the hint and error of its field', () => {
        const attributes = editor.getAccessibilityAttributes();

        expect(attributes['aria-describedby']).toBe('post-error post-keyboard-hint');
        const hint = document.getElementById('post-keyboard-hint');
        expect(hint.classList.contains('visually-hidden')).toBe(true);
        expect(hint.textContent).toBe('In a list, Tab indents the item. Press Escape, then Tab, to leave the editor.');
    });

    it('adds the hint once', () => {
        editor.getAccessibilityAttributes();
        editor.getAccessibilityAttributes();

        expect(document.querySelectorAll('#post-keyboard-hint')).toHaveLength(1);
    });
});
