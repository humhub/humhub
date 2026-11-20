import { describe, it, expect } from 'vitest';
import {initEditor, initView} from '../testEditor.js';

describe("Core:contexts", () => {
    const registerPlugin = window.humhub.richtext.plugin.registerPlugin;

    registerPlugin({id: 'ctx_comment_only'}, {preset: 'markdown', contexts: ['comment']});
    registerPlugin({id: 'ctx_post_comment'}, {preset: 'markdown', contexts: ['post', 'comment']});
    registerPlugin({id: 'ctx_everywhere'}, {preset: 'markdown'});

    const has = (instance, id) => !!instance.context.getPlugin(id);

    it("has no context id by default", () => {
        expect(initEditor({preset: 'markdown'}).context.getContextId()).to.equal(null);
        expect(initEditor({preset: 'markdown', contextId: ''}).context.getContextId()).to.equal(null);
    });

    it("provides the context id of the editor", () => {
        expect(initEditor({preset: 'markdown', contextId: 'post'}).context.getContextId()).to.equal('post');
    });

    it("activates a plugin with contexts only in these contexts", () => {
        const comment = initEditor({preset: 'markdown', contextId: 'comment'});
        expect(has(comment, 'ctx_comment_only')).to.equal(true);
        expect(has(comment, 'ctx_post_comment')).to.equal(true);
        expect(has(comment, 'ctx_everywhere')).to.equal(true);

        const post = initEditor({preset: 'markdown', contextId: 'post'});
        expect(has(post, 'ctx_comment_only')).to.equal(false);
        expect(has(post, 'ctx_post_comment')).to.equal(true);
        expect(has(post, 'ctx_everywhere')).to.equal(true);
    });

    it("does not activate a plugin with contexts without a context id", () => {
        const editor = initEditor({preset: 'markdown'});
        expect(has(editor, 'ctx_comment_only')).to.equal(false);
        expect(has(editor, 'ctx_post_comment')).to.equal(false);
        expect(has(editor, 'ctx_everywhere')).to.equal(true);
    });

    it("does not share the plugins of a preset between contexts", () => {
        // Cached by preset and context id
        initEditor({preset: 'markdown', contextId: 'comment'});
        const post = initEditor({preset: 'markdown', contextId: 'post'});
        expect(has(post, 'ctx_comment_only')).to.equal(false);
    });

    it("applies the contexts to the renderer of a view as well", () => {
        const comment = initView({preset: 'markdown', contextId: 'comment'});
        expect(has(comment, 'ctx_comment_only')).to.equal(true);

        const post = initView({preset: 'markdown', contextId: 'post'});
        expect(has(post, 'ctx_comment_only')).to.equal(false);
    });

    it("applies the contexts even if a plugin is included explicitly", () => {
        const post = initEditor({preset: 'markdown', contextId: 'post', include: ['ctx_comment_only']});
        expect(has(post, 'ctx_comment_only')).to.equal(false);
    });
});
