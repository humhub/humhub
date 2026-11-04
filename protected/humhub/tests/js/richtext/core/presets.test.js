import { describe, it, expect } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {initEditor, expectMenuItemVisible, expectMenuItemNotVisible, clickMenuItem, toHtml} from '../testEditor.js';

describe("Core:presets", () => {
    function expectContextContainsPlugin(view, id) {
        expect(view.context.getPlugin(id)).not.to.be.undefined;
    }

    function expectContextNotContainsPlugin(view, id) {
        expect(view.context.getPlugin(id)).to.be.undefined;
    }

    function testEditorPresetDoesIncludePlugin(preset, id) {
        it(`${preset} editor preset includes ${id}`, () => {
            let editor = initEditor({preset: preset});
            expectContextContainsPlugin(editor, id);
        });
    }

    function testEditorPresetDoesNotIncludePlugin(preset, id) {
        it(`${preset} editor preset does not include ${id}`, () => {
            let editor = initEditor({preset: preset});
            expectContextNotContainsPlugin(editor, id);
        });
    }

    function testViewPresetDoesNotIncludePlugin(preset, id) {
        it(`${preset} view preset does not include ${id}`, () => {
            let editor = initEditor({preset: preset});
            expectContextNotContainsPlugin(editor.transformToView(), id);
        });
    }

    function testViewPresetDoesIncludePlugin(preset, id) {
        it(`${preset} view preset includes ${id}`, () => {
            let editor = initEditor({preset: preset});
            expectContextContainsPlugin(editor.transformToView(), id);
        });
    }


    ['emoji', 'oembed', 'anchor', 'mention'].forEach(id => testEditorPresetDoesNotIncludePlugin('markdown', id));

    testEditorPresetDoesNotIncludePlugin('normal', 'anchor');
    testEditorPresetDoesNotIncludePlugin('full', 'anchor');

    // Note anchor is a render only plugin
    testEditorPresetDoesNotIncludePlugin('document', 'anchor');
    testViewPresetDoesIncludePlugin('document', 'anchor');

    ['emoji', 'oembed', 'mention'].forEach(id => testEditorPresetDoesIncludePlugin('normal', id));
    ['emoji', 'oembed', 'mention'].forEach(id => testEditorPresetDoesIncludePlugin('full', id));
    ['emoji', 'oembed', 'mention'].forEach(id => testEditorPresetDoesIncludePlugin('document', id));


    it("test exclude plugin from editor instance", () => {
        let editor = initEditor({exclude: ['emoji']});
        expectContextNotContainsPlugin(editor, 'emoji');
        expectMenuItemNotVisible('insertEmoji');
    });

    it("test include plugin in editor instance", () => {
        let editor = initEditor({preset: 'markdown', include: 'emoji'});
        expectContextContainsPlugin(editor, 'emoji');
        expectMenuItemVisible('insertEmoji');
    });

    it("test only plugin filter in editor instance", () => {
        initEditor({only: ['doc', 'text', 'paragraph', 'strong']});
        expectMenuItemVisible('markStrong');
        expectMenuItemNotVisible('markEm');

        /**
         * The following would fail due to markdown-it requiring base markdown plugins
         */
        //let view = editor.transformToView();
        //expect(viewToHtml(view)).equal('<p><strong>Bold Text</strong> _ignored em text_</p>');

    });

    it("test extending a preset", () => {
        window.humhub.richtext.plugin.registerPlugin({
            id: 'test',
            menu: (context) => [
                {
                    id: 'testItem',
                    group: 'format',
                    item: new window.humhub.richtext.menu.MenuItem({
                        run: (state, dispatch) => dispatch(state.tr.insertText("test!")),
                        label: 'test'
                    })
                }
            ]
        }, 'markdown');

        let editor = initEditor({preset: 'markdown'});
        expectContextContainsPlugin(editor, 'test');
        expectMenuItemVisible('testItem');
        clickMenuItem('testItem');
        expect(toHtml()).to.equal('<p>test!</p>');
    });
});
