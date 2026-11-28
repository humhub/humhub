import { afterEach, beforeAll, beforeEach, describe, expect, it, vi } from 'vitest';
import { nextTick } from 'vue';
import FileRow from '../../../../vue/FileRow.vue';
import UploadList from '../../../../vue/UploadList.vue';
import { UPLOAD_EVENTS } from '../../../../vue/upload/uploadBridge.js';
import { Widget } from '../../../../tests/js/support/legacyWidget.mjs';

// The progress bars (`UploadProgress`) a page renders, by id
const progressBars = new Map();
globalThis.humhub.modules['ui.widget'] = { Widget };
globalThis.humhub.modules['ui.progress'] = {
    Progress: { instance: ($node) => ($node.length ? progressBars.get($node.attr('id')) || null : null) },
};
globalThis.humhub.config.module('file').upload = { url: '/file/file/upload', deleteUrl: '/file/file/delete' };

await import('../../../../resources/js/humhub/humhub.util.js');
await import('../../resources/js/humhub.file.js');
await import('../../resources/js/humhub.ActiveFileUpload.js');
await import('../../../../resources/js/humhub/humhub.url.js');
await import('../../../../resources/js/humhub/humhub.vue.js');

const fileModule = globalThis.humhub.modules.file;
const vueModule = globalThis.humhub.modules.vue;
const client = globalThis.humhubStubs.client;

// The API's file shape (file\serializers\FileSerializer::file())
const shape = (guid, fileName) => ({
    id: 1,
    guid,
    mimeType: 'text/plain',
    size: 1024,
    fileName,
    mimeIcon: 'mime-text',
    icon: 'file-text',
    url: `/file/file/download?guid=${guid}`,
    downloadUrl: `/file/file/download?download=1&guid=${guid}`,
    previewUrl: null,
});

// A file of the upload response (FileHelper::getFileInfos())
const uploaded = (guid, name, extra = {}) => ({
    error: false,
    name,
    guid,
    size: 2048,
    mimeType: 'application/pdf',
    mimeIcon: 'mime-pdf',
    size_format: '2 KB',
    url: `/file/file/download?guid=${guid}`,
    relUrl: `/file/file/download?guid=${guid}`,
    openLink: '<a href="#">' + name + '</a>',
    thumbnailUrl: '',
    icon: 'file-type-pdf',
    downloadUrl: `/file/file/download?download=1&guid=${guid}`,
    ...extra,
});

const picked = (name, size = 2048) => ({ name, size });

/**
 * A form with an upload button and its preview, as `UploadButton` and `FilePreview` render them. `preview`: `true`
 * for a FilePreview, `'other'` for a module's own preview widget, `false` for none.
 */
const renderForm = ({ items = [], preview = true, max = 5, progressBar = false } = {}) => {
    const html = `
        <form id="form">
            <span class="fileinput-button" tabindex="0" role="button" data-action-click="file.upload" data-action-target="#upload">Attach</span>
            <input type="file" id="upload" multiple data-ui-widget="file.Upload" data-upload-submit-name="fileList[]"
                data-max-number-of-files="${max}" data-max-number-of-files-message="At most ${max} files">
            ${progressBar ? '<div id="upload_progress"></div>' : ''}
            ${preview === true ? `
                <div id="upload_preview" class="post-file-list" data-ui-widget="file.Preview" data-file-edit
                    style="${items.length ? '' : 'display:none'}">
                    <upload-list data-upload-bridge action="remove"></upload-list>
                </div>` : ''}
            ${preview === 'other' ? '<div id="upload_preview" data-ui-widget="test.FolderView"></div>' : ''}
        </form>`;
    document.body.replaceChildren(document.createRange().createContextualFragment(html));

    const tag = document.querySelector('upload-list');
    if (tag) {
        tag.setAttribute('props', JSON.stringify({ items: items.map((file) => ({ key: file.guid, file })) }));
    }

    return { tag, container: document.getElementById('upload_preview') };
};

// What the widget sends to the island: [type, detail] in order
const recordEvents = (tag) => {
    const sent = [];
    Object.values(UPLOAD_EVENTS)
        .filter((type) => type !== UPLOAD_EVENTS.READY && type !== UPLOAD_EVENTS.REMOVE)
        .forEach((type) => tag.addEventListener(type, (event) => sent.push([type, event.detail])));
    return sent;
};

// The island says it listens, as `connectUploadBridge()` does
const islandReady = (tag) => {
    tag.setAttribute('data-upload-ready', '');
    tag.dispatchEvent(new CustomEvent(UPLOAD_EVENTS.READY, { bubbles: true }));
};

const removeRow = (tag, key, file) => {
    tag.dispatchEvent(new CustomEvent(UPLOAD_EVENTS.REMOVE, { bubbles: true, detail: { key, file } }));
};

const lastItems = (sent) => sent.filter(([type]) => type === UPLOAD_EVENTS.ITEMS).pop()[1];

// The options of the upload widget, with the callbacks its transport calls
let fileupload;

/**
 * Files the user picked, as the transport hands them to the `add` callback with one request per file: one call per
 * file, all of the same selection. `submit()` sends the request, `abort()` cancels it.
 */
const pick = (files) => files.map((file) => {
    const data = {
        files: [file],
        originalFiles: files,
        process: () => jQuery.Deferred().resolve().promise(),
        submit: vi.fn(),
        abort: vi.fn(),
    };
    fileupload.add({}, data);
    return data;
});

beforeAll(() => {
    jQuery.fx.off = true;
    // A module's own preview widget, e.g. cfiles' folder view
    const FolderView = function (node, options) {
        Widget.call(this, node, options);
    };
    FolderView.prototype = Object.create(Widget.prototype);
    FolderView.prototype.add = () => {};
    globalThis.humhub.modules.test = { FolderView };
});

beforeEach(() => {
    fileupload = null;
    const initFileUpload = fileModule.Upload.prototype.initFileUpload;
    vi.spyOn(fileModule.Upload.prototype, 'initFileUpload').mockImplementation(function () {
        initFileUpload.call(this);
        fileupload = this.options;
    });
    fileModule.log.success = vi.fn();
    delete globalThis.humhub.prosemirrorFileHandler;
    progressBars.clear();
    vi.spyOn(client, 'post').mockResolvedValue({});
});

afterEach(() => {
    vi.restoreAllMocks();
    document.body.replaceChildren();
});

describe('file.Preview', () => {
    it('starts with the files of the island and counts them', () => {
        renderForm({ items: [shape('g1', 'a.txt'), shape('g2', 'b.txt')] });
        const preview = Widget.instance('#upload_preview');

        expect(preview.items.map((item) => item.key)).toEqual(['g1', 'g2']);
        expect(preview.items[0]).toEqual({ key: 'g1', file: shape('g1', 'a.txt'), progress: null, error: null });
        expect(preview.getFileCount()).toBe(2);
        expect(preview.hasFiles()).toBe(true);
    });

    it('sends its rows once the island is ready', () => {
        const { tag } = renderForm({ items: [shape('g1', 'a.txt')] });
        const sent = recordEvents(tag);
        const preview = Widget.instance('#upload_preview');

        preview.addPending('upload-a', picked('new.pdf'));
        expect(sent).toEqual([]);

        islandReady(tag);

        expect(sent).toEqual([[UPLOAD_EVENTS.ITEMS, [
            { key: 'g1', file: shape('g1', 'a.txt'), progress: null, error: null, busy: false },
            { key: 'upload-a', file: { fileName: 'new.pdf', size: 2048 }, progress: 0, error: null, busy: false },
        ]]]);
    });

    it('sends its rows at once to an island that was ready before it', () => {
        const { tag } = renderForm({ items: [shape('g1', 'a.txt')] });
        tag.setAttribute('data-upload-ready', '');
        const sent = recordEvents(tag);

        Widget.instance('#upload_preview');

        expect(sent).toEqual([[UPLOAD_EVENTS.ITEMS, [{ key: 'g1', file: shape('g1', 'a.txt'), progress: null, error: null, busy: false }]]]);
    });

    it('follows an upload: row, progress, then done or refused', () => {
        const { tag, container } = renderForm();
        const sent = recordEvents(tag);
        const preview = Widget.instance('#upload_preview');
        islandReady(tag);
        sent.length = 0;

        preview.addPending('upload-a', picked('a.pdf'));
        preview.addPending('upload-b', picked('b.exe'));
        expect(container.style.display).not.toBe('none');
        expect(preview.getFileCount()).toBe(2);

        preview.progress('upload-a', 42.5);
        // Whole percents, each once
        preview.progress('upload-a', 42.9);
        preview.done('upload-a', uploaded('g1', 'a.pdf'));
        preview.fail('upload-b', 'File type not allowed');
        // Not for a file that has finished
        preview.progress('upload-a', 80);

        expect(sent).toEqual([
            [UPLOAD_EVENTS.ADD, { key: 'upload-a', file: { fileName: 'a.pdf', size: 2048 }, progress: 0, error: null, busy: false }],
            [UPLOAD_EVENTS.ADD, { key: 'upload-b', file: { fileName: 'b.exe', size: 2048 }, progress: 0, error: null, busy: false }],
            [UPLOAD_EVENTS.PROGRESS, { key: 'upload-a', percent: 42 }],
            [UPLOAD_EVENTS.DONE, {
                key: 'upload-a',
                file: {
                    guid: 'g1',
                    fileName: 'a.pdf',
                    size: 2048,
                    mimeType: 'application/pdf',
                    mimeIcon: 'mime-pdf',
                    icon: 'file-type-pdf',
                    url: '/file/file/download?guid=g1',
                    downloadUrl: '/file/file/download?download=1&guid=g1',
                    previewUrl: null,
                    highlight: false,
                },
            }],
            [UPLOAD_EVENTS.FAIL, { key: 'upload-b', message: 'File type not allowed' }],
        ]);
        // A refused file is no file of the form
        expect(preview.getFileCount()).toBe(1);
    });

    it('adds an uploaded file a module hands it, keyed by its guid', () => {
        const { tag } = renderForm();
        const sent = recordEvents(tag);
        const preview = Widget.instance('#upload_preview');
        islandReady(tag);

        // A module's own response, without the icon and the download link of the upload response
        preview.add(uploaded('g7', 'photo.jpg', {
            mimeIcon: 'mime-image',
            thumbnailUrl: '/preview/g7',
            icon: undefined,
            downloadUrl: undefined,
        }));

        expect(lastItems(sent)).toEqual([{
            key: 'g7',
            file: {
                guid: 'g7',
                fileName: 'photo.jpg',
                size: 2048,
                mimeType: 'application/pdf',
                mimeIcon: 'mime-image',
                icon: null,
                url: '/file/file/download?guid=g7',
                downloadUrl: null,
                previewUrl: '/preview/g7',
                highlight: false,
            },
            progress: null,
            error: null,
            busy: false,
        }]);
        expect(preview.getFileCount()).toBe(1);
    });

    it('clears its rows on reset and cancels the uploads still running', () => {
        const { tag, container } = renderForm({ items: [shape('g1', 'a.txt')] });
        const sent = recordEvents(tag);
        const preview = Widget.instance('#upload_preview');
        preview.abortHandler = vi.fn();
        islandReady(tag);
        preview.addPending('upload-a', picked('a.pdf'));
        sent.length = 0;

        preview.reset();

        expect(preview.abortHandler).toHaveBeenCalledExactlyOnceWith('upload-a');
        expect(sent).toEqual([[UPLOAD_EVENTS.RESET, null]]);
        expect(preview.getFileCount()).toBe(0);
        expect(preview.hasFiles()).toBe(false);
        expect(container.style.display).toBe('none');
    });

    describe('when the user removes a row', () => {
        it('deletes an uploaded file, then drops its row', async () => {
            const { tag } = renderForm({ items: [shape('g1', 'a.txt'), shape('g2', 'b.txt')] });
            jQuery('#form').append('<input type="hidden" name="fileList[]" value="g1">');
            const sent = recordEvents(tag);
            const upload = Widget.instance('#upload');
            const preview = upload.preview;
            const removed = vi.fn();
            const deleted = vi.fn();
            preview.on('fileRemoved', (event, file) => removed(file));
            upload.on('fileDeleted', (event, file) => deleted(file));
            islandReady(tag);

            removeRow(tag, 'g1', shape('g1', 'a.txt'));
            // The row shows that the file is being deleted
            expect(lastItems(sent).map((item) => [item.key, item.busy])).toEqual([['g1', true], ['g2', false]]);
            // Asked again while the file is being deleted
            removeRow(tag, 'g1', shape('g1', 'a.txt'));
            await vi.waitFor(() => expect(removed).toHaveBeenCalled());

            expect(client.post).toHaveBeenCalledExactlyOnceWith({ url: '/file/file/delete', data: { guid: 'g1' } });
            expect(jQuery('#form input[value="g1"]').length).toBe(0);
            expect(deleted).toHaveBeenCalledExactlyOnceWith(shape('g1', 'a.txt'));
            expect(removed).toHaveBeenCalledExactlyOnceWith(shape('g1', 'a.txt'));
            expect(lastItems(sent).map((item) => item.key)).toEqual(['g2']);
            expect(preview.getFileCount()).toBe(1);
        });

        it('keeps the row when the file could not be deleted', async () => {
            const { tag } = renderForm({ items: [shape('g1', 'a.txt')] });
            const sent = recordEvents(tag);
            const preview = Widget.instance('#upload').preview;
            client.post.mockRejectedValue(new Error('Forbidden'));
            islandReady(tag);

            removeRow(tag, 'g1', shape('g1', 'a.txt'));
            expect(preview.removing).toEqual({ g1: true });
            expect(lastItems(sent)[0].busy).toBe(true);
            await vi.waitFor(() => expect(preview.removing).toEqual({}));

            expect(preview.getFileCount()).toBe(1);
            expect(lastItems(sent)).toEqual([{ key: 'g1', file: shape('g1', 'a.txt'), progress: null, error: null, busy: false }]);
        });

        it('cancels an upload and drops its row', () => {
            const { tag } = renderForm();
            const sent = recordEvents(tag);
            const upload = Widget.instance('#upload');
            islandReady(tag);
            const [data] = pick([picked('a.pdf')]);

            removeRow(tag, data.uploadKey, { fileName: 'a.pdf', size: 2048 });

            expect(data.abort).toHaveBeenCalledOnce();
            expect(client.post).not.toHaveBeenCalled();
            expect(lastItems(sent)).toEqual([]);
            expect(upload.getFileCount()).toBe(0);
        });

        it('drops a refused file without asking the server', () => {
            const { tag } = renderForm();
            const sent = recordEvents(tag);
            const preview = Widget.instance('#upload_preview');
            preview.abortHandler = vi.fn();
            islandReady(tag);
            preview.addPending('upload-a', picked('a.exe'));
            preview.fail('upload-a', 'File type not allowed');

            removeRow(tag, 'upload-a', { fileName: 'a.exe', size: 2048 });

            expect(preview.abortHandler).not.toHaveBeenCalled();
            expect(client.post).not.toHaveBeenCalled();
            expect(lastItems(sent)).toEqual([]);
        });

        it('hands the focus to the upload button when the last row goes', () => {
            const { tag } = renderForm();
            const preview = Widget.instance('#upload').preview;
            islandReady(tag);
            preview.addPending('upload-a', picked('a.exe'));
            preview.fail('upload-a', 'File type not allowed');
            const rowButton = document.createElement('button');
            tag.appendChild(rowButton);
            rowButton.focus();

            let focusWhenSent = null;
            tag.addEventListener(UPLOAD_EVENTS.ITEMS, () => {
                focusWhenSent = document.activeElement;
            });
            removeRow(tag, 'upload-a', { fileName: 'a.exe', size: 2048 });

            // Before the island drops the row that has it
            expect(focusWhenSent).toBe(document.querySelector('.fileinput-button'));
        });
    });
});

describe('file.Upload', () => {
    it('uploads one file per request, three at a time, only into file rows', () => {
        renderForm();
        Widget.instance('#upload');
        expect(fileupload.singleFileUploads).toBe(true);
        expect(fileupload.limitConcurrentUploads).toBe(3);

        // A module's own preview keeps one request for all files
        renderForm({ preview: 'other' });
        Widget.instance('#upload');
        expect(fileupload.singleFileUploads).toBe(false);
        expect(fileupload.limitConcurrentUploads).toBeUndefined();

        // As the richtext editor's own upload button, without a preview
        renderForm({ preview: false });
        Widget.instance('#upload');
        expect(fileupload.singleFileUploads).toBe(false);
        expect(fileupload.limitConcurrentUploads).toBeUndefined();
    });

    it('shows a row for each picked file at once and counts it against the maximum', () => {
        const { tag } = renderForm({ items: [shape('g1', 'a.txt')], max: 3 });
        const sent = recordEvents(tag);
        const upload = Widget.instance('#upload');
        const warn = vi.spyOn(fileModule.log, 'warn');
        islandReady(tag);
        sent.length = 0;

        const datas = pick([picked('b.pdf'), picked('c.pdf'), picked('d.pdf'), picked('e.pdf')]);

        expect(datas.map((data) => data.submit.mock.calls.length)).toEqual([1, 1, 0, 0]);
        expect(sent.map(([type, detail]) => [type, detail.file.fileName])).toEqual([
            [UPLOAD_EVENTS.ADD, 'b.pdf'],
            [UPLOAD_EVENTS.ADD, 'c.pdf'],
        ]);
        expect(upload.getFileCount()).toBe(3);
        // Once for the selection
        expect(warn).toHaveBeenCalledExactlyOnceWith('At most 3 files', true);
    });

    it('allows uploads again when a file that filled the maximum is refused or cancelled', () => {
        const { tag } = renderForm({ max: 2 });
        Widget.instance('#upload');
        islandReady(tag);
        const input = () => document.getElementById('upload');

        const [a, b] = pick([picked('a.pdf'), picked('b.exe'), picked('c.pdf')]);
        expect(input().disabled).toBe(true);

        fileupload.done({}, { ...b, result: { files: [{ error: true, errors: ['File type not allowed'], name: 'b.exe' }] } });
        expect(input().disabled).toBe(false);

        pick([picked('d.pdf'), picked('e.pdf')]);
        expect(input().disabled).toBe(true);
        removeRow(tag, a.uploadKey, { fileName: 'a.pdf', size: 2048 });
        expect(a.abort).toHaveBeenCalledOnce();
        expect(input().disabled).toBe(false);
    });

    it('routes progress, responses and failures to the rows by key', () => {
        const { tag } = renderForm();
        const sent = recordEvents(tag);
        const upload = Widget.instance('#upload');
        const ended = vi.fn();
        upload.on('uploadEnd', (event, response) => ended(response));
        islandReady(tag);
        const [a, b, c, d] = pick([picked('a.pdf'), picked('b.exe'), picked('c.pdf'), picked('d.pdf')]);
        sent.length = 0;

        fileupload.start({}, {});
        fileupload.progress({}, { ...a, loaded: 512, total: 2048 });
        fileupload.done({}, { ...a, result: { files: [uploaded('g1', 'a.pdf')] } });
        fileupload.done({}, { ...b, result: { files: [{ error: true, errors: ['File type not allowed'], name: 'b.exe', size: 1 }] } });
        fileupload.fail({}, {
            ...c,
            errorThrown: 'Payload Too Large',
            textStatus: 'error',
            jqXHR: { responseJSON: { error: true, message: 'Too large' } },
        });
        // Cancelled: no row to tell
        fileupload.fail({}, { ...d, errorThrown: 'abort', textStatus: 'abort' });

        expect(sent.map(([type, detail]) => [type, detail.key, detail.percent ?? detail.message ?? detail.file.guid])).toEqual([
            [UPLOAD_EVENTS.PROGRESS, a.uploadKey, 25],
            [UPLOAD_EVENTS.DONE, a.uploadKey, 'g1'],
            [UPLOAD_EVENTS.FAIL, b.uploadKey, 'File type not allowed'],
            [UPLOAD_EVENTS.FAIL, c.uploadKey, 'Too large'],
        ]);
        // The uploaded file goes with the form
        expect(jQuery('#form input[name="fileList[]"]').map((i, input) => input.value).get()).toEqual(['g1']);
        // Once per file, with the response of the file
        expect(ended).toHaveBeenCalledTimes(2);
        expect(ended.mock.calls[0][0].result.files[0].guid).toBe('g1');
        // No collected error message: the rows say it
        expect(upload.errors).toEqual([]);
    });

    it('shows the messages the server encoded for HTML as text in the rows', () => {
        const { tag } = renderForm();
        const sent = recordEvents(tag);
        Widget.instance('#upload');
        islandReady(tag);
        const [a, b, c] = pick([picked('R&D <draft>.exe'), picked('b.pdf'), picked('c.pdf')]);
        sent.length = 0;

        fileupload.done({}, {
            ...a,
            result: { files: [{ error: true, errors: 'File R&amp;D &lt;draft&gt;.exe could not be uploaded!', name: 'R&amp;D &lt;draft&gt;.exe' }] },
        });
        fileupload.fail({}, {
            ...b,
            errorThrown: 'Forbidden',
            textStatus: 'error',
            jqXHR: { responseJSON: { message: 'You aren&#039;t allowed &#x2014; &amp;amp; stays &amp;' } },
        });
        // A message that is not encoded keeps what looks like markup
        fileupload.done({}, { ...c, result: { files: [{ error: true, errors: ['Not <b>allowed</b> & refused'], name: 'c.pdf' }] } });

        expect(sent.map(([type, detail]) => [type, detail.message])).toEqual([
            [UPLOAD_EVENTS.FAIL, 'File R&D <draft>.exe could not be uploaded!'],
            [UPLOAD_EVENTS.FAIL, 'You aren\'t allowed — &amp; stays &'],
            [UPLOAD_EVENTS.FAIL, 'Not <b>allowed</b> & refused'],
        ]);
    });

    it('adds the files of a response without a row, as from a module calling done()', () => {
        const { tag } = renderForm();
        const sent = recordEvents(tag);
        const upload = Widget.instance('#upload');
        islandReady(tag);

        upload.done({}, { result: { files: [uploaded('g1', 'a.pdf'), uploaded('g2', 'b.pdf')] } });

        expect(lastItems(sent).map((item) => [item.key, item.file.fileName, item.progress])).toEqual([
            ['g1', 'a.pdf', null],
            ['g2', 'b.pdf', null],
        ]);
        expect(upload.getFileCount()).toBe(2);
    });

    it('shows no rows for files that go into the richtext', () => {
        const { tag } = renderForm();
        const sent = recordEvents(tag);
        Widget.instance('#upload');
        islandReady(tag);
        sent.length = 0;
        globalThis.humhub.prosemirrorFileHandler = true;

        const [data] = pick([picked('a.png')]);
        fileupload.done({}, { ...data, result: { files: [uploaded('g1', 'a.png')] } });

        expect(data.uploadKey).toBeUndefined();
        expect(data.submit).toHaveBeenCalledOnce();
        expect(sent).toEqual([]);
        expect(jQuery('#form input[name="fileList[]"]').val()).toBe('g1');
    });

    it('collects the errors without rows', () => {
        renderForm({ preview: false });
        const upload = Widget.instance('#upload');
        const statusError = vi.spyOn(upload, 'statusError');
        const data = {
            files: [picked('a.pdf'), picked('b.exe')],
            originalFiles: [],
            process: () => jQuery.Deferred().resolve().promise(),
            submit: vi.fn(),
        };

        fileupload.add({}, data);
        fileupload.done({}, { ...data, result: { files: [uploaded('g1', 'a.pdf'), { error: true, errors: 'Not allowed', name: 'b.exe' }] } });
        fileupload.stop({});

        expect(data.uploadKey).toBeUndefined();
        expect(data.submit).toHaveBeenCalledOnce();
        expect(statusError).toHaveBeenCalledOnce();
    });

    it('keeps updating a progress bar a module renders, and its own progressall callback', () => {
        const bar = { update: vi.fn() };
        const progressall = vi.fn();
        progressBars.set('upload_progress', bar);
        renderForm({ progressBar: true });
        new fileModule.Upload(jQuery('#upload'), { progressall });

        fileupload.progressall({}, { loaded: 10, total: 40 });

        expect(bar.update).toHaveBeenCalledExactlyOnceWith(10, 40);
        expect(progressall).toHaveBeenCalledExactlyOnceWith({}, { loaded: 10, total: 40 });
    });
});

describe('ActiveFileUpload.Preview', () => {
    // As ActiveFileUpload renders it: its preview with the current file as the init data, and the upload input
    const renderField = (files) => {
        document.body.replaceChildren(document.createRange().createContextualFragment(`
            <form id="form">
                <div id="cover-preview" class="img-uploader-preview" data-ui-widget="ActiveFileUpload.Preview"
                    style="${files.length ? '' : 'display:none'}"></div>
                <input type="file" id="cover" data-ui-widget="file.Upload" data-upload-preview="#cover-preview" data-upload-single="1">
            </form>`));
        if (files.length) {
            document.getElementById('cover-preview').setAttribute('data-ui-init', JSON.stringify(files));
        }
    };

    it('shows the image of the field', () => {
        renderField([uploaded('g1', 'cover.jpg', { thumbnailUrl: '/preview/g1' })]);

        const upload = Widget.instance('#cover');
        const preview = upload.preview;

        const item = document.querySelector('#cover-preview ul.files > li.file-preview-item');
        expect(item.getAttribute('data-guid')).toBe('g1');
        expect(item.querySelector('img').getAttribute('src')).toBe('/preview/g1');
        expect(item.querySelector('img').getAttribute('alt')).toBe('cover.jpg');
        expect(document.getElementById('cover-preview').style.display).not.toBe('none');
        expect(preview.hasFiles()).toBe(true);
        expect(upload.getFileCount()).toBe(1);
        // One request for its single file, the image comes with the response
        expect(upload.hasUploadRows()).toBe(false);

        upload.done({}, { result: { files: [uploaded('g2', 'new.jpg', { thumbnailUrl: '/preview/g2' })] } });
        expect(Array.from(document.querySelectorAll('#cover-preview img')).map((img) => img.getAttribute('src'))).toEqual(['/preview/g2']);

        preview.reset();
        expect(preview.hasFiles()).toBe(false);
        expect(document.getElementById('cover-preview').style.display).toBe('none');
    });

    it('stays hidden without an image', () => {
        renderField([]);

        const preview = Widget.instance('#cover-preview');

        expect(preview.hasFiles()).toBe(false);
        expect(document.getElementById('cover-preview').style.display).toBe('none');
    });
});

describe('file.Preview with the island', () => {
    beforeAll(() => {
        vueModule.register('FileRow', FileRow);
        vueModule.register('UploadList', UploadList);
    });

    it('shows what happened before the island was mounted, and cancels an upload removed in it', async () => {
        const { tag } = renderForm({ items: [shape('g1', 'a.txt')] });
        const upload = Widget.instance('#upload');
        const [data] = pick([picked('new.pdf')]);

        await vueModule.mountElement(tag);
        await nextTick();

        const rows = () => Array.from(tag.querySelectorAll('.file-row:not(.file-row-leave-active)'));
        expect(rows().map((row) => row.querySelector('.file-row-name').textContent)).toEqual(['a.txt', 'new.pdf']);
        expect(rows()[1].classList.contains('is-uploading')).toBe(true);

        rows()[1].querySelector('button.file-row-action').click();
        await nextTick();

        expect(data.abort).toHaveBeenCalledOnce();
        expect(rows()).toHaveLength(1);
        expect(upload.getFileCount()).toBe(1);
    });
});
