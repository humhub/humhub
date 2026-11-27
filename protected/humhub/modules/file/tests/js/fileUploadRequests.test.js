import { existsSync } from 'node:fs';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { UPLOAD_EVENTS } from '../../../../vue/upload/uploadBridge.js';
import { Widget } from '../../../../tests/js/support/legacyWidget.mjs';

/**
 * `file.Upload` on the real `jquery.fileupload.js` (and the jQuery UI widget factory the page loads with it): the
 * requests themselves are stand-ins (`$.ajax`), so the plugin's own queue, its callbacks and its input replacement
 * run as in the browser.
 *
 * Both come with composer (`protected/vendor/npm-asset`): without it, e.g. in the JS job of CI, the tests are skipped.
 */
const vendorFile = (path) => resolve(dirname(fileURLToPath(import.meta.url)), '../../../../../vendor/npm-asset', path);
const plugins = [vendorFile('jquery-ui/ui/widget.js'), vendorFile('blueimp-file-upload/js/jquery.fileupload.js')];
const hasPlugins = plugins.every((path) => existsSync(path));

globalThis.humhub.modules['ui.widget'] = { Widget };
globalThis.humhub.modules['ui.progress'] = { Progress: { instance: () => null } };
const fileConfig = globalThis.humhub.config.module('file');
fileConfig.upload = { url: '/file/file/upload', deleteUrl: '/file/file/delete' };

if (hasPlugins) {
    for (const path of plugins) {
        await import(/* @vite-ignore */ path);
    }
}
await import('../../../../resources/js/humhub/humhub.util.js');
await import('../../resources/js/humhub.file.js');

const originalAjax = jQuery.ajax;

/**
 * The requests the plugin sent, held open until the test settles them: `progress(loaded, total)` as the browser's
 * upload progress, `succeed(result)`, `refuse(status, json)`. `aborted` once cancelled.
 */
let requests;

const requestFor = (name) => requests.find((request) => request.fileName === name);

const fakeAjax = (options) => {
    const deferred = jQuery.Deferred();
    const jqXHR = deferred.promise();
    const request = {
        options,
        fileName: options.files[0].name,
        fileCount: options.files.length,
        aborted: false,
        progress(loaded, total) {
            options.xhr().upload.dispatchEvent(new ProgressEvent('progress', { lengthComputable: true, loaded, total }));
        },
        succeed(result) {
            deferred.resolveWith(options.context, [result, 'success', jqXHR]);
        },
        refuse(status, json, errorThrown = 'Payload Too Large') {
            jqXHR.status = status;
            jqXHR.responseJSON = json;
            deferred.rejectWith(options.context, [jqXHR, 'error', errorThrown]);
        },
    };
    // As jQuery's own after the request was sent
    jqXHR.abort = (statusText) => {
        request.aborted = true;
        deferred.rejectWith(options.context, [jqXHR, statusText || 'abort', statusText || 'abort']);
        return jqXHR;
    };
    requests.push(request);

    return jqXHR;
};

// A file of the upload response (FileHelper::getFileInfos())
const uploaded = (guid, name) => ({
    error: false,
    name,
    guid,
    size: 7,
    mimeType: 'application/pdf',
    mimeIcon: 'mime-pdf',
    url: `/file/file/download?guid=${guid}`,
    thumbnailUrl: '',
    icon: 'file-type-pdf',
    downloadUrl: `/file/file/download?download=1&guid=${guid}`,
});

const renderForm = () => {
    document.body.replaceChildren(document.createRange().createContextualFragment(`
        <form id="form">
            <span class="fileinput-button" tabindex="0" role="button" data-action-click="file.upload" data-action-target="#upload">Attach</span>
            <input type="file" id="upload" multiple data-ui-widget="file.Upload" data-upload-submit-name="fileList[]"
                data-max-number-of-files="10" data-max-number-of-files-message="At most 10 files">
            <div id="upload_preview" class="post-file-list" data-ui-widget="file.Preview" data-file-edit style="display:none">
                <upload-list data-upload-bridge action="remove" props='{"items":[]}'></upload-list>
            </div>
            <button type="submit" id="submit">Save</button>
        </form>`));

    const tag = document.querySelector('upload-list');
    const upload = Widget.instance('#upload');
    const sent = [];
    Object.values(UPLOAD_EVENTS)
        .filter((type) => type !== UPLOAD_EVENTS.READY && type !== UPLOAD_EVENTS.REMOVE)
        .forEach((type) => tag.addEventListener(type, (event) => sent.push([type, event.detail])));
    // The island says it listens, as `connectUploadBridge()` does
    tag.setAttribute('data-upload-ready', '');
    tag.dispatchEvent(new CustomEvent(UPLOAD_EVENTS.READY, { bubbles: true }));
    sent.length = 0;

    const started = vi.fn();
    const finished = vi.fn();
    upload.on('uploadStart', started).on('uploadFinish', finished);

    return { tag, upload, sent, started, finished, button: document.getElementById('submit') };
};

// The user picks files (names, or `File`s): a `change` of the input, which the plugin then replaces by a clone
const pick = (files) => {
    const input = document.getElementById('upload');
    const value = files.map((file) => (file instanceof File ? file : new File(['content'], file)));
    Object.defineProperty(input, 'files', { value, configurable: true });
    jQuery(input).trigger('change');
};

const keyOf = (sent, name) => sent.find(([type, detail]) => type === UPLOAD_EVENTS.ADD && detail.file.fileName === name)[1].key;

const removeRow = (tag, key) => {
    tag.dispatchEvent(new CustomEvent(UPLOAD_EVENTS.REMOVE, { bubbles: true, detail: { key, file: {} } }));
};

const { logCalls } = globalThis.humhubStubs;

beforeEach(() => {
    requests = [];
    jQuery.ajax = fakeAjax;
    logCalls.error.length = 0;
});

afterEach(() => {
    jQuery.ajax = originalAjax;
    document.body.replaceChildren();
    delete fileConfig.upload.maxFileSize;
    delete globalThis.humhub.prosemirrorFileHandler;
});

describe.skipIf(!hasPlugins)('file.Upload with jquery.fileupload.js', () => {
    it('sends one request per file, at most 3 at a time, and routes progress and results by row', async () => {
        const { tag, sent, started, finished } = renderForm();

        pick(['a.pdf', 'b.exe', 'c.pdf', 'd.pdf']);

        // The input was replaced; its clone carries the plugin
        expect(document.getElementById('upload')).not.toBeNull();
        expect(jQuery('#upload').fileupload('instance')).toBeDefined();
        expect(sent.filter(([type]) => type === UPLOAD_EVENTS.ADD).map(([, detail]) => detail.file.fileName))
            .toEqual(['a.pdf', 'b.exe', 'c.pdf', 'd.pdf']);
        expect(requests.map((request) => [request.fileName, request.fileCount])).toEqual([['a.pdf', 1], ['b.exe', 1], ['c.pdf', 1]]);
        expect(started).toHaveBeenCalledOnce();

        const [a, b, c, d] = ['a.pdf', 'b.exe', 'c.pdf', 'd.pdf'].map((name) => keyOf(sent, name));
        sent.length = 0;

        requestFor('a.pdf').progress(3, 7);
        requestFor('a.pdf').succeed({ files: [uploaded('g1', 'a.pdf')] });
        // A slot is free: the fourth file goes
        await vi.waitFor(() => expect(requests).toHaveLength(4));
        requestFor('b.exe').refuse(403, { message: 'File type not allowed' });
        requestFor('c.pdf').succeed({ files: [uploaded('g3', 'c.pdf')] });
        expect(finished).not.toHaveBeenCalled();
        requestFor('d.pdf').succeed({ files: [uploaded('g4', 'd.pdf')] });

        expect(sent.map(([type, detail]) => [type, detail.key, detail.percent ?? detail.message ?? detail.file.guid])).toEqual([
            [UPLOAD_EVENTS.PROGRESS, a, 42],
            [UPLOAD_EVENTS.PROGRESS, a, 100],
            [UPLOAD_EVENTS.DONE, a, 'g1'],
            [UPLOAD_EVENTS.FAIL, b, 'File type not allowed'],
            [UPLOAD_EVENTS.PROGRESS, c, 100],
            [UPLOAD_EVENTS.DONE, c, 'g3'],
            [UPLOAD_EVENTS.PROGRESS, d, 100],
            [UPLOAD_EVENTS.DONE, d, 'g4'],
        ]);
        expect(started).toHaveBeenCalledOnce();
        expect(finished).toHaveBeenCalledOnce();
        expect(jQuery('#upload').fileupload('active')).toBe(0);
        expect(jQuery('#form input[name="fileList[]"]').map((i, input) => input.value).get()).toEqual(['g1', 'g3', 'g4']);
        expect(tag.isConnected).toBe(true);
    });

    it('cancels a waiting and a running upload without a refused row, and frees their slots', async () => {
        const { tag, upload, sent, finished, button } = renderForm();
        upload.lockWhileUploading('#submit');

        pick(['a.pdf', 'b.pdf', 'c.pdf', 'd.pdf', 'e.pdf']);
        expect(requests).toHaveLength(3);
        expect(button.disabled).toBe(true);

        // d.pdf and e.pdf wait for a slot
        removeRow(tag, keyOf(sent, 'd.pdf'));
        removeRow(tag, keyOf(sent, 'a.pdf'));

        expect(requestFor('a.pdf').aborted).toBe(true);
        // The slot of a.pdf goes to e.pdf; the cancelled d.pdf is never sent
        await vi.waitFor(() => expect(requests.map((request) => request.fileName)).toEqual(['a.pdf', 'b.pdf', 'c.pdf', 'e.pdf']));
        expect(jQuery('#upload').fileupload('active')).toBe(3);
        expect(button.disabled).toBe(true);

        ['b.pdf', 'c.pdf', 'e.pdf'].forEach((name, i) => requestFor(name).succeed({ files: [uploaded(`g${i}`, name)] }));

        await new Promise((resolve) => setTimeout(resolve, 10));
        expect(requests).toHaveLength(4);
        expect(sent.filter(([type]) => type === UPLOAD_EVENTS.FAIL)).toEqual([]);
        expect(finished).toHaveBeenCalledOnce();
        expect(button.disabled).toBe(false);
        expect(upload.getFileCount()).toBe(3);
    });

    it('names the reason of a refused request: the server\'s, else one by its status', async () => {
        fileConfig.upload.maxFileSize = 64 * 1024 * 1024;
        const { sent } = renderForm();

        pick(['a.pdf', 'b.pdf', 'c.pdf']);
        requestFor('a.pdf').refuse(403, { error: true, message: 'You &amp; your files are not allowed here.' }, 'Forbidden');
        requestFor('b.pdf').refuse(413, undefined);
        requestFor('c.pdf').refuse(0, undefined, '');
        await vi.waitFor(() => expect(requests).toHaveLength(3));
        pick(['d.pdf', 'e.pdf', 'f.pdf']);
        requestFor('d.pdf').refuse(502, undefined, 'Bad Gateway');
        requestFor('e.pdf').refuse(422, { errors: { files: ['No file was uploaded.'] } });
        requestFor('f.pdf').refuse(404, undefined, 'Not Found');

        const failures = sent.filter(([type]) => type === UPLOAD_EVENTS.FAIL).map(([, detail]) => detail.message);
        expect(failures).toEqual([
            // As text: the server encodes its messages for HTML
            'You & your files are not allowed here.',
            'The file is too large.',
            'The connection was interrupted.',
            'The server could not process the file.',
            'No file was uploaded.',
            'The upload failed.',
        ]);
    });

    it('refuses a file over the size limit at once, in its row and without a request', () => {
        fileConfig.upload.maxFileSize = 5;
        const { sent, started, upload } = renderForm();

        // 7 bytes, over the limit, beside files of 1 byte
        pick([new File(['x'], 'a.pdf'), new File(['content'], 'huge.zip'), new File(['x'], 'b.pdf')]);

        expect(requests.map((request) => request.fileName)).toEqual(['a.pdf', 'b.pdf']);
        expect(started).toHaveBeenCalledOnce();
        const huge = keyOf(sent, 'huge.zip');
        expect(sent.filter(([, detail]) => detail.key === huge)).toEqual([
            [UPLOAD_EVENTS.ADD, expect.objectContaining({ key: huge, file: { fileName: 'huge.zip', size: 7 } })],
            [UPLOAD_EVENTS.FAIL, { key: huge, message: 'The file is too large. Maximum: 5 B' }],
        ]);
        // A refused file takes no place of the maximum
        expect(upload.getFileCount()).toBe(2);
    });

    it('says why a file over the size limit is refused where files get no row', () => {
        fileConfig.upload.maxFileSize = 5;
        // The files go into the richtext, not into rows
        globalThis.humhub.prosemirrorFileHandler = true;
        const { sent, started } = renderForm();

        pick([new File(['content'], 'huge.png')]);

        expect(requests).toEqual([]);
        expect(started).not.toHaveBeenCalled();
        expect(sent).toEqual([]);
        expect(logCalls.error).toEqual([['huge.png: The file is too large. Maximum: 5 B', true]]);
    });

    it('keeps the submit button locked when uploadFinish comes while files still upload', () => {
        const { upload, button } = renderForm();
        upload.lockWhileUploading('#submit');

        pick(['a.pdf', 'b.pdf']);
        expect(button.disabled).toBe(true);

        // As the handler of `humhub:file:created` and modules call it
        upload.finish();
        expect(button.disabled).toBe(true);

        requestFor('a.pdf').succeed({ files: [uploaded('g1', 'a.pdf')] });
        expect(button.disabled).toBe(true);
        requestFor('b.pdf').succeed({ files: [uploaded('g2', 'b.pdf')] });
        expect(button.disabled).toBe(false);

        // A finish() without any upload running unlocks
        upload.start({}, {});
        expect(button.disabled).toBe(true);
        upload.finish();
        expect(button.disabled).toBe(false);
    });
});
