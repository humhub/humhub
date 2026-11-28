import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { UPLOAD_EVENTS } from '../../../../vue/upload/uploadBridge.js';
import { Widget } from '../../../../tests/js/support/legacyWidget.mjs';

/**
 * `file.Upload` with its own transport: the requests go through jQuery's ajax to a stand-in for the browser's
 * XMLHttpRequest, so the queue, the callbacks, the drop and paste zones and the input run as in the browser.
 */

// The progress bars (`UploadProgress`) a page renders, by id
const progressBars = new Map();
globalThis.humhub.modules['ui.widget'] = { Widget };
globalThis.humhub.modules['ui.progress'] = {
    Progress: { instance: ($node) => ($node.length ? progressBars.get($node.attr('id')) || null : null) },
};
const fileConfig = globalThis.humhub.config.module('file');
fileConfig.upload = { url: '/file/file/upload', deleteUrl: '/file/file/delete' };

await import('../../../../resources/js/humhub/humhub.util.js');
await import('../../resources/js/humhub.file.js');

// As yii.js adds the CSRF token to every request of the page
jQuery.ajaxPrefilter((options, originalOptions, jqXHR) => {
    if (!options.crossDomain) {
        jqXHR.setRequestHeader('X-CSRF-Token', 'csrf-token');
    }
});

/**
 * The requests sent, held open until the test settles them: `progress(loaded, total)` as the browser's upload
 * progress, `respond(status, body)`, `interrupt()` for no answer. `aborted` once cancelled.
 */
let requests;

class FakeXMLHttpRequest {
    constructor() {
        this.upload = new EventTarget();
        this.headers = {};
        this.readyState = 0;
        this.status = 0;
        this.statusText = '';
        this.responseText = '';
        this.responseType = '';
        this.onload = this.onerror = this.onabort = this.ontimeout = null;
        this.aborted = false;
    }

    open(method, url) {
        this.method = method;
        this.url = url;
        this.readyState = 1;
    }

    setRequestHeader(name, value) {
        this.headers[name] = value;
    }

    getAllResponseHeaders() {
        return this.readyState === 4 ? 'content-type: application/json\r\n' : '';
    }

    send(body) {
        this.body = body;
        requests.push(this);
    }

    abort() {
        this.aborted = true;
    }

    // The names of the files sent, and the other fields
    get fileNames() {
        return this.body.getAll('files[]').map((file) => file.name);
    }

    get fields() {
        return [...this.body.entries()].map(([name, value]) => [name, typeof value === 'string' ? value : value.name]);
    }

    progress(loaded, total) {
        this.upload.dispatchEvent(new ProgressEvent('progress', { lengthComputable: true, loaded, total }));
    }

    respond(status, body) {
        this.status = status;
        this.statusText = status === 200 ? 'OK' : 'Error';
        this.responseText = typeof body === 'string' ? body : JSON.stringify(body);
        this.readyState = 4;
        this.onload();
    }

    succeed(result) {
        this.respond(200, result);
    }

    interrupt() {
        this.readyState = 4;
        this.onerror();
    }
}

const requestFor = (name) => requests.find((request) => request.fileNames.includes(name));

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

/**
 * A form with an upload button, as `UploadButton` and `FilePreview` render it. `preview: false` as the richtext
 * editor's own upload button, which has none; `attributes` go on the input.
 */
const renderForm = ({ preview = true, multiple = true, attributes = '', progressBar = false } = {}) => {
    document.body.replaceChildren(document.createRange().createContextualFragment(`
        <form id="form">
            <div id="zone">
                <span class="fileinput-button" tabindex="0" role="button" data-action-click="file.upload" data-action-target="#upload">Attach</span>
                <input type="file" id="upload" name="files[]" ${multiple ? 'multiple' : ''} data-ui-widget="file.Upload" data-upload-submit-name="fileList[]"
                    data-max-number-of-files="10" data-max-number-of-files-message="At most 10 files" ${attributes}>
            </div>
            ${progressBar ? '<div id="upload_progress"></div>' : ''}
            ${preview ? `
                <div id="upload_preview" class="post-file-list" data-ui-widget="file.Preview" data-file-edit style="display:none">
                    <upload-list data-upload-bridge action="remove" props='{"items":[]}'></upload-list>
                </div>` : ''}
            <button type="submit" id="submit">Save</button>
        </form>`));

    const tag = document.querySelector('upload-list');
    const sent = [];
    if (tag) {
        Object.values(UPLOAD_EVENTS)
            .filter((type) => type !== UPLOAD_EVENTS.READY && type !== UPLOAD_EVENTS.REMOVE)
            .forEach((type) => tag.addEventListener(type, (event) => sent.push([type, event.detail])));
        // The island says it listens, as `connectUploadBridge()` does
        tag.setAttribute('data-upload-ready', '');
    }

    const upload = Widget.instance('#upload');
    // The rows the preview starts with
    sent.length = 0;
    const events = [];
    ['uploadStart', 'uploadEnd', 'humhub:file:uploadEnd', 'uploadFinish'].forEach((type) => {
        upload.on(type, (event, response) => events.push([type, response]));
    });

    return { tag, upload, sent, events, button: document.getElementById('submit'), input: document.getElementById('upload') };
};

const file = (name, content = 'content') => (name instanceof File ? name : new File([content], name));

// The user picks files (names, or `File`s): a `change` of the input
const pick = (files) => {
    const input = document.getElementById('upload');
    Object.defineProperty(input, 'files', { value: files.map((name) => file(name)), configurable: true });
    input.dispatchEvent(new Event('change', { bubbles: true }));
};

// jsdom has no DragEvent: an event with a stand-in for its `dataTransfer`
const dragEvent = (type, dataTransfer) => {
    const event = new Event(type, { bubbles: true, cancelable: true });
    Object.defineProperty(event, 'dataTransfer', { value: dataTransfer });
    return event;
};

const dropFiles = (element, files) => {
    const event = dragEvent('drop', { types: ['Files'], files: files.map((name) => file(name)) });
    element.dispatchEvent(event);
    return event;
};

const pasteFiles = (element, files) => {
    const event = new Event('paste', { bubbles: true, cancelable: true });
    const items = [{ getAsFile: () => null }, ...files.map((name) => ({ getAsFile: () => file(name) }))];
    Object.defineProperty(event, 'clipboardData', { value: { items } });
    element.dispatchEvent(event);
    return event;
};

const keyOf = (sent, name) => sent.find(([type, detail]) => type === UPLOAD_EVENTS.ADD && detail.file.fileName === name)[1].key;

const removeRow = (tag, key) => {
    tag.dispatchEvent(new CustomEvent(UPLOAD_EVENTS.REMOVE, { bubbles: true, detail: { key, file: {} } }));
};

const { logCalls } = globalThis.humhubStubs;
const OriginalXMLHttpRequest = globalThis.XMLHttpRequest;

beforeEach(() => {
    requests = [];
    globalThis.XMLHttpRequest = FakeXMLHttpRequest;
    jQuery.fx.off = true;
    logCalls.error.length = 0;
    logCalls.warn.length = 0;
});

afterEach(() => {
    globalThis.XMLHttpRequest = OriginalXMLHttpRequest;
    document.body.replaceChildren();
    progressBars.clear();
    delete fileConfig.upload.maxFileSize;
    delete globalThis.humhub.prosemirrorFileHandler;
});

describe('file.Upload', () => {
    it('posts each file as the plugin did: to the upload URL, in the field of the input, with the form data and the CSRF header', () => {
        renderForm({ attributes: 'data-upload-model="humhub\\modules\\post\\models\\Post" data-upload-model-id="3" data-upload-hide-in-stream="1"' });

        pick(['a.pdf']);

        const [request] = requests;
        expect([request.method, request.url]).toEqual(['POST', '/file/file/upload']);
        expect(request.fields).toEqual([
            ['objectModel', 'humhub\\modules\\post\\models\\Post'],
            ['objectId', '3'],
            ['hideInStream', '1'],
            ['files[]', 'a.pdf'],
        ]);
        expect(request.headers).toMatchObject({ 'X-CSRF-Token': 'csrf-token', 'X-Requested-With': 'XMLHttpRequest' });
        expect(request.headers.Accept).toContain('application/json');
    });

    it('leaves out the form data it has no value for, and names the field `files[]` for an input without a name', () => {
        renderForm({ attributes: 'data-url="/custom/upload"' });
        document.getElementById('upload').removeAttribute('name');

        pick(['a.pdf']);

        expect(requests[0].url).toBe('/custom/upload');
        expect(requests[0].fields).toEqual([['files[]', 'a.pdf']]);
    });

    it('sends one request per file, at most 3 at a time, and routes progress and results by row', () => {
        const { tag, upload, sent, events } = renderForm();

        pick(['a.pdf', 'b.exe', 'c.pdf', 'd.pdf']);

        expect(sent.filter(([type]) => type === UPLOAD_EVENTS.ADD).map(([, detail]) => detail.file.fileName))
            .toEqual(['a.pdf', 'b.exe', 'c.pdf', 'd.pdf']);
        expect(requests.map((request) => request.fileNames)).toEqual([['a.pdf'], ['b.exe'], ['c.pdf']]);
        expect(upload.isActive()).toBe(true);

        const [a, b, c, d] = ['a.pdf', 'b.exe', 'c.pdf', 'd.pdf'].map((name) => keyOf(sent, name));
        sent.length = 0;

        requestFor('a.pdf').progress(3, 7);
        requestFor('a.pdf').succeed({ files: [uploaded('g1', 'a.pdf')] });
        // A place is free: the fourth file goes
        expect(requests).toHaveLength(4);
        requestFor('b.exe').respond(403, { message: 'File type not allowed' });
        requestFor('c.pdf').succeed({ files: [uploaded('g3', 'c.pdf')] });
        expect(events.filter(([type]) => type === 'uploadFinish')).toEqual([]);
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
        // Once at the start and at the end, the end of each file between, with the response of its request
        expect(events.map(([type, response]) => [type, response && response.result ? response.result.files[0].guid : response])).toEqual([
            ['uploadStart', undefined],
            ['humhub:file:uploadEnd', 'g1'],
            ['uploadEnd', 'g1'],
            ['humhub:file:uploadEnd', 'g3'],
            ['uploadEnd', 'g3'],
            ['humhub:file:uploadEnd', 'g4'],
            ['uploadEnd', 'g4'],
            ['uploadFinish', upload],
        ]);
        const response = events.find(([type]) => type === 'uploadEnd')[1];
        expect(response._response.result.files[0].guid).toBe('g1');
        expect(response.files.map((picked) => picked.name)).toEqual(['a.pdf']);
        expect(upload.isActive()).toBe(false);
        expect(jQuery('#form input[name="fileList[]"]').map((i, input) => input.value).get()).toEqual(['g1', 'g3', 'g4']);
        expect(tag.isConnected).toBe(true);
    });

    it('sends all files of a selection in one request without rows, as the richtext editor\'s own upload, with the progress of all', () => {
        const bar = { update: vi.fn(), fadeIn: vi.fn(), fadeOut: () => Promise.resolve({ reset() {} }) };
        progressBars.set('upload_progress', bar);
        const { upload, events } = renderForm({ preview: false, progressBar: true });

        pick([file('a.pdf', '1234567890'), file('b.pdf', '12345')]);

        expect(requests.map((request) => request.fileNames)).toEqual([['a.pdf', 'b.pdf']]);
        requests[0].progress(10, 20);
        requests[0].succeed({ files: [uploaded('g1', 'a.pdf'), uploaded('g2', 'b.pdf')] });

        // The progress in bytes of the files, then all of them once sent
        expect(bar.update.mock.calls).toEqual([[7, 15], [15, 15]]);
        expect(events.map(([type, response]) => [type, response && response.result ? response.result.files.map((f) => f.guid) : response])).toEqual([
            ['uploadStart', undefined],
            ['humhub:file:uploadEnd', ['g1', 'g2']],
            ['uploadEnd', ['g1', 'g2']],
            ['uploadFinish', upload],
        ]);
        expect(jQuery('#form input[name="fileList[]"]').map((i, input) => input.value).get()).toEqual(['g1', 'g2']);
    });

    it('uploads the files given to addFiles(), e.g. dropped into a richtext, as one batch', () => {
        const { upload, events } = renderForm({ preview: false });
        const files = [file('a.png'), file('b.png')];
        // A FileList, as the richtext gets it from the drop or paste event
        const fileList = { 0: files[0], 1: files[1], length: 2, [Symbol.iterator]: files[Symbol.iterator].bind(files) };

        upload.addFiles(fileList);

        expect(requests.map((request) => request.fileNames)).toEqual([['a.png', 'b.png']]);
        requests[0].succeed({ files: [uploaded('g1', 'a.png'), uploaded('g2', 'b.png')] });
        expect(events.filter(([type]) => type === 'uploadEnd')).toHaveLength(1);
    });

    it('uploads each file on its own from an input without `multiple`, and the parts of the progress of all', () => {
        const bar = { update: vi.fn(), fadeIn: vi.fn(), fadeOut: () => Promise.resolve({ reset() {} }) };
        progressBars.set('upload_progress', bar);
        renderForm({ preview: false, multiple: false, progressBar: true });

        pick([file('a.pdf', '1234567890'), file('b.pdf', '12345'), file('c.pdf', '1')]);

        // Without rows, nothing waits
        expect(requests.map((request) => request.fileNames)).toEqual([['a.pdf'], ['b.pdf'], ['c.pdf']]);
        requests[0].progress(5, 10);
        // A failed file leaves the progress of all
        requests[1].respond(500, '');
        requests[0].succeed({ files: [uploaded('g1', 'a.pdf')] });
        requests[2].succeed({ files: [uploaded('g3', 'c.pdf')] });

        expect(bar.update.mock.calls).toEqual([[5, 16], [10, 11], [11, 11]]);
    });

    it('cancels a waiting and a running upload without a refused row, and frees their places', () => {
        const { tag, upload, sent, events, button } = renderForm();
        upload.lockWhileUploading('#submit');

        pick(['a.pdf', 'b.pdf', 'c.pdf', 'd.pdf', 'e.pdf']);
        expect(requests).toHaveLength(3);
        expect(button.disabled).toBe(true);

        // d.pdf and e.pdf wait for a place
        removeRow(tag, keyOf(sent, 'd.pdf'));
        removeRow(tag, keyOf(sent, 'a.pdf'));

        expect(requestFor('a.pdf').aborted).toBe(true);
        // The place of a.pdf goes to e.pdf; the cancelled d.pdf is never sent
        expect(requests.map((request) => request.fileNames[0])).toEqual(['a.pdf', 'b.pdf', 'c.pdf', 'e.pdf']);
        expect(upload.transport.active()).toBe(3);
        expect(button.disabled).toBe(true);

        ['b.pdf', 'c.pdf', 'e.pdf'].forEach((name, i) => requestFor(name).succeed({ files: [uploaded(`g${i}`, name)] }));

        expect(requests).toHaveLength(4);
        expect(sent.filter(([type]) => type === UPLOAD_EVENTS.FAIL)).toEqual([]);
        expect(events.filter(([type]) => type === 'uploadFinish')).toHaveLength(1);
        expect(button.disabled).toBe(false);
        expect(upload.getFileCount()).toBe(3);
        // A cancelled request is no error
        expect(logCalls.error).toEqual([]);
    });

    it('never sends a waiting upload that was cancelled, and still starts and stops once', () => {
        const { tag, sent, events } = renderForm();

        pick(['a.pdf', 'b.pdf', 'c.pdf', 'd.pdf']);
        ['a.pdf', 'b.pdf', 'c.pdf'].forEach((name, i) => requestFor(name).succeed({ files: [uploaded(`g${i}`, name)] }));
        expect(requests).toHaveLength(4);

        // d.pdf is running now; a new selection waits behind two others
        pick(['e.pdf', 'f.pdf', 'g.pdf', 'h.pdf']);
        expect(requests).toHaveLength(6);
        removeRow(tag, keyOf(sent, 'h.pdf'));
        ['d.pdf', 'e.pdf', 'f.pdf', 'g.pdf'].forEach((name, i) => requestFor(name).succeed({ files: [uploaded(`h${i}`, name)] }));

        expect(requests.map((request) => request.fileNames[0])).toEqual(['a.pdf', 'b.pdf', 'c.pdf', 'd.pdf', 'e.pdf', 'f.pdf', 'g.pdf']);
        expect(events.filter(([type]) => type === 'uploadStart')).toHaveLength(1);
        expect(events.filter(([type]) => type === 'uploadFinish')).toHaveLength(1);
    });

    it('starts and stops once when every upload fails or is cancelled', () => {
        const { tag, sent, events, upload } = renderForm();

        pick(['a.pdf', 'b.pdf', 'c.pdf', 'd.pdf']);
        removeRow(tag, keyOf(sent, 'd.pdf'));
        requestFor('a.pdf').respond(500, '');
        requestFor('b.pdf').interrupt();
        removeRow(tag, keyOf(sent, 'c.pdf'));

        expect(events.filter(([type]) => type === 'uploadStart')).toHaveLength(1);
        expect(events.filter(([type]) => type === 'uploadFinish')).toHaveLength(1);
        expect(upload.isActive()).toBe(false);
    });

    it('sends the files even when an uploadStart handler throws', () => {
        const { upload } = renderForm();
        upload.on('uploadStart', () => {
            throw new Error('A module failed');
        });

        // The handler's error reaches the caller, the file goes out anyway
        expect(() => upload.addFiles([file('a.pdf')])).toThrow('A module failed');

        expect(requests.map((request) => request.fileNames)).toEqual([['a.pdf']]);
        requests[0].succeed({ files: [uploaded('g1', 'a.pdf')] });
        expect(upload.isActive()).toBe(false);
    });

    it('names the reason of a refused request: the server\'s, else one by its status', () => {
        fileConfig.upload.maxFileSize = 64 * 1024 * 1024;
        const { sent } = renderForm();

        pick(['a.pdf', 'b.pdf', 'c.pdf']);
        requestFor('a.pdf').respond(403, { error: true, message: 'You &amp; your files are not allowed here.' });
        // A limit in front of PHP answers with its own page
        requestFor('b.pdf').respond(413, '<html><body>413 Request Entity Too Large</body></html>');
        requestFor('c.pdf').interrupt();
        pick(['d.pdf', 'e.pdf', 'f.pdf']);
        requestFor('d.pdf').respond(502, '<html>Bad Gateway</html>');
        requestFor('e.pdf').respond(422, { errors: { files: ['No file was uploaded.'] } });
        requestFor('f.pdf').respond(404, '');
        pick(['g.pdf', 'h.pdf']);
        requestFor('g.pdf').respond(429, { message: '' });
        // Not JSON
        requestFor('h.pdf').respond(200, '<html>Login</html>');

        const failures = sent.filter(([type]) => type === UPLOAD_EVENTS.FAIL).map(([, detail]) => detail.message);
        expect(failures).toEqual([
            // As text: the server encodes its messages for HTML
            'You & your files are not allowed here.',
            'The file is too large.',
            'The connection was interrupted.',
            'The server could not process the file.',
            'No file was uploaded.',
            'The upload failed.',
            'Too many uploads. Please try again in a moment.',
            'The upload failed.',
        ]);
        // The rows tell the user
        expect(logCalls.error.filter(([, status]) => status === true)).toEqual([]);
    });

    it('says why a request failed where files get no row', () => {
        renderForm({ preview: false });

        pick(['a.pdf']);
        requests[0].respond(500, '');

        expect(logCalls.error).toHaveLength(1);
        expect(logCalls.error[0][0].status).toBe(500);
        expect(logCalls.error[0][1]).toBe(true);
    });

    it('refuses a file over the size limit at once, in its row and without a request', () => {
        fileConfig.upload.maxFileSize = 5;
        const { sent, events, upload } = renderForm();

        // 7 bytes, over the limit, beside files of 1 byte
        pick([file('a.pdf', 'x'), file('huge.zip'), file('b.pdf', 'x')]);

        expect(requests.map((request) => request.fileNames[0])).toEqual(['a.pdf', 'b.pdf']);
        expect(events.filter(([type]) => type === 'uploadStart')).toHaveLength(1);
        const huge = keyOf(sent, 'huge.zip');
        expect(sent.filter(([, detail]) => detail.key === huge)).toEqual([
            [UPLOAD_EVENTS.ADD, expect.objectContaining({ key: huge, file: { fileName: 'huge.zip', size: 7 } })],
            [UPLOAD_EVENTS.FAIL, { key: huge, message: 'The file is too large. Maximum: 5 B' }],
        ]);
        // A refused file takes no place of the maximum
        expect(upload.getFileCount()).toBe(2);
    });

    it('says why a file over the size limit is refused where files get no row, and sends the others', () => {
        fileConfig.upload.maxFileSize = 5;
        // The files go into the richtext, not into rows
        globalThis.humhub.prosemirrorFileHandler = true;
        const { sent, events } = renderForm();

        pick([file('huge.png')]);

        expect(requests).toEqual([]);
        expect(events).toEqual([]);
        expect(sent).toEqual([]);
        expect(logCalls.error).toEqual([['huge.png: The file is too large. Maximum: 5 B', true]]);

        // One request for the files that are left
        renderForm({ preview: false });
        pick([file('huge.png'), file('a.png', 'x')]);
        expect(requests.map((request) => request.fileNames)).toEqual([['a.png']]);
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

    it('empties the input after each selection, so the same file can be picked again', () => {
        const { input } = renderForm();
        let value = 'C:\\fakepath\\a.pdf';
        Object.defineProperty(input, 'value', { get: () => value, set: (next) => { value = next; }, configurable: true });

        pick(['a.pdf']);
        expect(value).toBe('');
        pick(['a.pdf']);

        expect(requests.map((request) => request.fileNames[0])).toEqual(['a.pdf', 'a.pdf']);
    });

    it('creating the widget again for its input does not upload twice', () => {
        renderForm();
        new globalThis.humhub.modules.file.Upload(jQuery('#upload'));

        pick(['a.pdf']);
        dropFiles(document.querySelector('.fileinput-button'), ['b.pdf']);

        expect(requests.map((request) => request.fileNames[0])).toEqual(['a.pdf', 'b.pdf']);
    });
});

describe('file.Upload drop and paste', () => {
    it('takes files dropped on the upload button, and only files, without a drop zone of its own', () => {
        renderForm();
        const trigger = document.querySelector('.fileinput-button');

        const over = dragEvent('dragover', { types: ['Files'], dropEffect: 'none' });
        trigger.dispatchEvent(over);
        expect(over.defaultPrevented).toBe(true);
        expect(over.dataTransfer.dropEffect).toBe('copy');
        ['dragenter', 'dragleave'].forEach((type) => {
            const event = dragEvent(type, { types: ['Files'] });
            trigger.dispatchEvent(event);
            expect(event.defaultPrevented).toBe(true);
        });

        // Dragged text is left to the browser
        const text = dragEvent('dragover', { types: ['text/plain'] });
        trigger.dispatchEvent(text);
        expect(text.defaultPrevented).toBe(false);
        const noFiles = dragEvent('drop', { types: ['text/plain'], files: [] });
        trigger.dispatchEvent(noFiles);
        expect(noFiles.defaultPrevented).toBe(false);

        // Not the rest of the page
        const elsewhere = dropFiles(document.getElementById('form'), ['b.pdf']);
        expect(elsewhere.defaultPrevented).toBe(false);
        expect(requests).toEqual([]);

        const drop = dropFiles(trigger, ['a.pdf']);
        expect(drop.defaultPrevented).toBe(true);
        expect(requests.map((request) => request.fileNames)).toEqual([['a.pdf']]);
    });

    it('takes files dropped on its drop zone instead of the upload button', () => {
        renderForm({ attributes: 'data-upload-drop-zone="#zone"' });

        dropFiles(document.getElementById('zone'), ['a.pdf', 'b.pdf']);

        expect(requests.map((request) => request.fileNames)).toEqual([['a.pdf'], ['b.pdf']]);
    });

    it('takes drops and pastes when the drop zone is the paste zone too', () => {
        renderForm({ attributes: 'data-upload-drop-zone="#zone" data-upload-paste-zone="#zone"' });
        const zone = document.getElementById('zone');

        dropFiles(zone, ['a.pdf']);
        pasteFiles(zone, ['b.png']);

        expect(requests.map((request) => request.fileNames)).toEqual([['a.pdf'], ['b.png']]);
    });

    it('leaves a drop that something inside the zone handled, as the richtext editor', () => {
        renderForm({ attributes: 'data-upload-drop-zone="#form"' });
        const inner = document.getElementById('zone');
        inner.addEventListener('drop', (event) => event.stopPropagation());

        dropFiles(inner, ['a.pdf']);

        expect(requests).toEqual([]);
    });

    it('takes the files in a dropped folder and its folders', async () => {
        renderForm();
        const fileEntry = (name) => ({ isFile: true, isDirectory: false, file: (resolve) => resolve(file(name)) });
        const folderEntry = (parts) => ({
            isFile: false,
            isDirectory: true,
            createReader: () => {
                const left = [...parts, []];
                return { readEntries: (resolve) => resolve(left.shift()) };
            },
        });
        const folder = folderEntry([[fileEntry('a.pdf'), folderEntry([[fileEntry('b.pdf')]])], [fileEntry('c.pdf')]]);
        const loose = file('d.pdf');
        const event = dragEvent('drop', {
            types: ['Files'],
            files: [file('folder'), loose],
            items: [{ webkitGetAsEntry: () => folder, getAsFile: () => null }, { webkitGetAsEntry: () => fileEntry('d.pdf'), getAsFile: () => loose }],
        });

        document.querySelector('.fileinput-button').dispatchEvent(event);

        expect(event.defaultPrevented).toBe(true);
        await vi.waitFor(() => expect(requests).toHaveLength(3));
        expect(requests.map((request) => request.fileNames[0])).toEqual(['a.pdf', 'b.pdf', 'c.pdf']);
        requests[0].succeed({ files: [uploaded('g1', 'a.pdf')] });
        expect(requests[3].fileNames).toEqual(['d.pdf']);
    });

    it('takes pasted files only in its paste zone, and leaves the paste to the browser', () => {
        renderForm();
        pasteFiles(document.body, ['a.png']);
        expect(requests).toEqual([]);

        renderForm({ attributes: 'data-upload-paste-zone="#form"' });
        const paste = pasteFiles(document.getElementById('zone'), ['a.png', 'b.png']);

        expect(paste.defaultPrevented).toBe(false);
        expect(requests.map((request) => request.fileNames)).toEqual([['a.png'], ['b.png']]);
    });

    it('stops listening on a zone that outlives its input, as the body after a page change', () => {
        renderForm({ attributes: 'data-upload-paste-zone="body"' });

        // The page content goes, the body stays
        document.getElementById('form').remove();
        pasteFiles(document.body, ['a.png']);

        expect(requests).toEqual([]);
        expect((jQuery._data(document.body, 'events') || {}).paste).toBeUndefined();
    });
});
