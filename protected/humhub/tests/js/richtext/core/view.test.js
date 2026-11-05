import { describe, it, expect, beforeAll } from 'vitest';
/* jshint -W024 */
/* jshint expr:true */

import {initView, viewToHtml, setViewText, serialize} from '../testEditor.js';

describe("MarkdownView:render", () => {

    beforeAll(() => {
        $('#result').html('');
    })

    it("test initializing the view by parameter", () => {
        let view = initView('**This is my view**');
        expect(viewToHtml()).to.equal('<p><strong>This is my view</strong></p>');
    });

    it("test initializing the view by text", () => {
        setViewText('**This is my view**');
        initView();
        expect(viewToHtml()).to.equal('<p><strong>This is my view</strong></p>');
    });

    it("test initializing the view by encoded text", () => {
        setViewText('This is my view with <b>Html</b>');
        initView();
        expect(viewToHtml()).to.equal('<p>This is my view with &lt;b&gt;Html&lt;/b&gt;</p>');
    });

    it("test initializing the view with new line", () => {
        setViewText('This is my view with <br>new line');
        initView();
        expect(viewToHtml()).to.equal('<p>This is my view with <br>new line</p>');
    });

    it("test initialize view with simple xss", () => {
        setViewText('This is my view with <script>alert()</script>');
        initView();
        expect(viewToHtml()).to.equal('<p>This is my view with &lt;script&gt;alert()&lt;/script&gt;</p>');
    });

    it("test transformToEditor and back", () => {
        let view = initView('**This is my view**');
        let editor = view.transformToEditor();
        expect(serialize(editor)).to.equal('**This is my view**');
        view = editor.transformToView();
        expect(viewToHtml(view)).to.equal('<p><strong>This is my view</strong></p>');
    });

});