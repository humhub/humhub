import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { Widget } from '../../../../tests/js/support/legacyWidget.mjs';

/**
 * A post is not sent while its files are still uploading: the submit button of the create form and of the inline
 * edit form waits for the uploads (`file.Upload.lockWhileUploading()`).
 */

// The entry's base (`content.Content`) as far as `StreamEntry` uses it here
const Content = function (node) {
    this.$ = jQuery(node);
};
Content.prototype.parent = () => undefined;
Content.extend = function (init) {
    const Sub = function (...args) {
        init.apply(this, args);
    };
    Sub.prototype = Object.create(Content.prototype);
    Sub.prototype.constructor = Sub;
    return Sub;
};

globalThis.humhub.modules['ui.widget'] = { Widget };
globalThis.humhub.modules['ui.progress'] = { Progress: { instance: () => null } };
globalThis.humhub.modules.content = { Content };
globalThis.humhub.config.module('file').upload = { url: '/file/file/upload', deleteUrl: '/file/file/delete' };

await import('../../../../resources/js/humhub/humhub.util.js');
await import('../../../file/resources/js/humhub.file.js');
await import('../../resources/js/humhub.content.form.js');
await import('../../resources/js/humhub.stream.StreamEntry.js');

// `module.export = StreamEntry` replaces the export function of the module stand-in
const StreamEntry = globalThis.humhub.modules['stream.StreamEntry'].export;
const { Upload } = globalThis.humhub.modules.file;

// Requests each upload widget has running or waiting, by the id of its input
let active;

const render = (html) => {
    document.body.replaceChildren(document.createRange().createContextualFragment(html));
};

// The callbacks of the widget's transport: the first upload of a selection starts, the last one stops
const start = (id) => {
    active.set(id, 1);
    Widget.instance('#' + id).options.start({});
};
const stop = (id) => {
    active.set(id, 0);
    Widget.instance('#' + id).options.stop({});
};

beforeEach(() => {
    jQuery.fx.off = true;
    active = new Map();
    vi.spyOn(Upload.prototype, 'isActive').mockImplementation(function () {
        return (active.get(this.$.attr('id')) || 0) > 0;
    });
});

afterEach(() => {
    vi.restoreAllMocks();
    document.body.replaceChildren();
});

describe('the create form', () => {
    // The richtext's own upload widget (`ProsemirrorRichTextEditor::prepend()`) and the attachments
    const renderCreateForm = () => render(`
        <div id="contentFormBody" data-ui-widget="content.form.CreateForm">
            <form>
                <input type="file" id="post_input-file-upload" data-ui-widget="file.Upload">
                <input type="file" id="contentFormFiles" multiple data-ui-widget="file.Upload"
                    data-upload-preview="#contentFormFiles_preview">
                <button type="submit" id="post_submit_button">Submit</button>
            </form>
        </div>`);

    it('disables the submit button while its files upload, until all of its upload widgets are done', () => {
        renderCreateForm();
        Widget.instance('#contentFormBody');
        const button = document.getElementById('post_submit_button');

        start('contentFormFiles');
        expect(button.disabled).toBe(true);

        // An image dropped into the text meanwhile
        start('post_input-file-upload');
        stop('contentFormFiles');
        expect(button.disabled).toBe(true);

        stop('post_input-file-upload');
        expect(button.disabled).toBe(false);
    });

    it('keeps it disabled when uploadFinish comes while requests still run', () => {
        renderCreateForm();
        Widget.instance('#contentFormBody');
        const button = document.getElementById('post_submit_button');
        const upload = Widget.instance('#contentFormFiles');

        start('contentFormFiles');
        // As the handler of `humhub:file:created` and modules call it
        upload.finish();
        expect(button.disabled).toBe(true);

        stop('contentFormFiles');
        expect(button.disabled).toBe(false);
    });

    it('enables the submit button again when the form is reset', () => {
        renderCreateForm();
        const form = Widget.instance('#contentFormBody');
        const button = document.getElementById('post_submit_button');

        start('contentFormFiles');
        expect(button.disabled).toBe(true);
        form.resetForm();

        expect(button.disabled).toBe(false);
    });
});

describe('the inline edit form of an entry', () => {
    // As post/views/post/edit.php renders it: the attachments and the editor's own upload widget
    const editForm = (error = '') => `
        <div class="content content_edit" id="post_edit_1">
            <form>
                ${error}
                <input type="file" id="post_input_1-file-upload" data-ui-widget="file.Upload">
                <input type="file" id="post_upload_1" multiple data-ui-widget="file.Upload"
                    data-upload-preview="#post_upload_preview_1">
                <button type="submit" data-action-click="editSubmit" data-action-url="/post/edit">Save</button>
            </form>
        </div>`;

    const renderEntry = () => {
        render('<div id="entry" data-stream-entry><div class="content">The post</div></div>');
        return new StreamEntry('#entry');
    };

    const submitButton = () => document.querySelector('[data-action-click="editSubmit"]');

    it('disables the submit button while files upload, until all of its upload widgets are done', () => {
        const entry = renderEntry();
        entry.setEditContent(editForm());

        start('post_upload_1');
        expect(submitButton().disabled).toBe(true);

        // An image dropped into the text meanwhile
        start('post_input_1-file-upload');
        stop('post_upload_1');
        expect(submitButton().disabled).toBe(true);

        stop('post_input_1-file-upload');
        expect(submitButton().disabled).toBe(false);
    });

    it('locks the form again after it comes back with errors', () => {
        const entry = renderEntry();
        entry.setEditContent(editForm());
        globalThis.humhubStubs.client.submit = () => ({
            status(handlers) {
                handlers[400]({ html: editForm('<div class="invalid-feedback">Too long</div>') });
                return Promise.resolve();
            },
        });

        entry.editSubmit({ url: '/post/edit' });
        expect(document.querySelector('.invalid-feedback')).not.toBeNull();

        start('post_upload_1');
        expect(submitButton().disabled).toBe(true);
        stop('post_upload_1');
        expect(submitButton().disabled).toBe(false);
    });
});
