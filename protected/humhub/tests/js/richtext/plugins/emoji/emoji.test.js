import { describe, it, expect } from 'vitest';
import {initEditor, toHtml, serialize, simulateInputRule, setSelection, getEditor} from '../../testEditor.js';
import {findQuery, rankEmojis} from '../../../../../resources/js/richtext/core/plugins/emoji/autocomplete.js';
import {applySkinTone} from '../../../../../resources/js/richtext/core/plugins/emoji/data.js';

describe("Plugin:emoji", () => {
    it("keeps emojis as plain text", () => {
        initEditor('Hi 😀 👍🏽 👨‍👩‍👧 1️⃣');
        expect(toHtml()).to.equal('<p>Hi 😀 👍🏽 👨‍👩‍👧 1️⃣</p>');
        expect(serialize()).to.equal('Hi 😀 👍🏽 👨‍👩‍👧 1️⃣');
    });

    it("does not render shortcodes", () => {
        initEditor(':smile: and ;smile;');
        expect(toHtml()).to.equal('<p>:smile: and ;smile;</p>');
    });

    it("replaces a text shortcut while typing", () => {
        initEditor();
        expect(simulateInputRule(':)')).to.be.true;
        expect(serialize()).to.equal('🙂');
    });

    it("replaces a text shortcut ending with a letter only before a space", () => {
        initEditor();
        expect(simulateInputRule(':D')).to.not.be.ok;

        initEditor();
        expect(simulateInputRule(':D ')).to.be.true;
        expect(toHtml()).to.equal('<p>😄 </p>');
    });

    it("does not replace a shortcut within a word", () => {
        initEditor();
        expect(simulateInputRule('http:/')).to.not.be.ok;
    });

    it("does not replace a shortcut in code", () => {
        initEditor('`code ');
        setSelection(7, 7);
        expect(simulateInputRule(':)')).to.not.be.ok;
    });

    it("finds the autocomplete query before the cursor", () => {
        const editor = initEditor('Hello :smi');
        setSelection(11, 11);
        expect(findQuery(editor.view.state)).to.deep.equal({from: 7, to: 11, query: 'smi'});
    });

    it("finds no autocomplete query within a word, for one letter or in code", () => {
        let editor = initEditor('a:smi');
        setSelection(6, 6);
        expect(findQuery(editor.view.state)).to.be.null;

        editor = initEditor('Hello :s');
        setSelection(9, 9);
        expect(findQuery(editor.view.state)).to.be.null;

        editor = initEditor('`:smi`');
        setSelection(5, 5);
        expect(findQuery(editor.view.state)).to.be.null;
    });

    it("turns emoji images of pasted HTML into text", () => {
        const editor = getEditor(initEditor());
        const html = '<p>Hi <img class="emoji" draggable="false" alt="😀" src="/twemoji/1f600.png" data-name="grinning"> <img src="x.png" alt="photo"></p>';
        const result = editor.view.someProp('transformPastedHTML', (f) => f(html));
        expect(result).to.equal('<p>Hi 😀 <img src="x.png" alt="photo"></p>');
    });

    it("has a menu item to insert an emoji", () => {
        initEditor();
        expect($('.ProseMirror-menu-insertEmoji').length).to.equal(1);
    });

    it("ranks shortcodes starting with the query first", () => {
        const emojis = [
            {unicode: '🤞', annotation: 'crossed fingers', shortcodes: ['hand_with_index_finger_and_thumb_crossed']},
            {unicode: '⛈️', annotation: 'cloud with lightning and rain', shortcodes: ['thunder_cloud_and_rain']},
            {unicode: '👍', annotation: 'thumbs up', shortcodes: ['thumbs_up', '+1', 'thumbsup']},
        ];
        expect(rankEmojis(emojis, 'thu').map((item) => item.label)).to.deep.equal([
            ':thumbsup:', ':thunder_cloud_and_rain:', ':hand_with_index_finger_and_thumb_crossed:',
        ]);
    });

    it("applies the preferred skin tone", () => {
        const emoji = {unicode: '👍', skins: [{tone: 1, unicode: '👍🏻'}, {tone: 5, unicode: '👍🏿'}]};
        expect(applySkinTone(emoji, 5)).to.equal('👍🏿');
        expect(applySkinTone(emoji, 0)).to.equal('👍');
        expect(applySkinTone({unicode: '😀'}, 5)).to.equal('😀');
    });
});
