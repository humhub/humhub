import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { createUploadQueue } from '../../vue/upload/uploadQueue.js';
import { uploadFile } from '../../vue/upload/uploadClient.js';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const apiFile = (id = 1) => ({ id, guid: `guid-${id}`, fileName: `f${id}.pdf`, size: 10 });
const browserFile = (name) => new File(['x'], name);
const tick = () => new Promise((resolve) => setTimeout(resolve, 0));

/** A fake `upload`: every call is recorded with controls to settle it. */
let calls;
const fakeUpload = (file, onProgress) => {
    let resolve;
    let reject;
    const promise = new Promise((res, rej) => {
        resolve = res;
        reject = rej;
    });
    promise.abort = vi.fn();
    calls.push({ file, onProgress, promise, resolve, reject });
    return promise;
};

const handlers = () => ({ onProgress: vi.fn(), onDone: vi.fn(), onFail: vi.fn() });

const { logCalls } = globalThis.humhubStubs;

const fileConfig = globalThis.humhub.config.module('file');

beforeEach(() => {
    calls = [];
    logCalls.error.length = 0;
    fileConfig.upload = {};
});

// The reason a failed request gives the file's row
const failWith = async (response) => {
    const queue = createUploadQueue({ upload: fakeUpload });
    const a = handlers();
    queue.add(browserFile('a'), a);

    calls[calls.length - 1].reject(response);
    await tick();

    expect(a.onFail).toHaveBeenCalledTimes(1);

    return a.onFail.mock.calls[0][0];
};

describe('createUploadQueue', () => {
    it('runs at most 3 uploads and starts the next one when one ends', async () => {
        const queue = createUploadQueue({ upload: fakeUpload });
        const hs = [1, 2, 3, 4, 5].map(() => handlers());
        hs.forEach((h, i) => queue.add(browserFile(`f${i + 1}`), h));

        expect(calls).toHaveLength(3);

        calls[1].resolve({ results: [apiFile(2)], errors: [] });
        await tick();

        expect(calls).toHaveLength(4);
        expect(calls[3].file.name).toBe('f4');
        expect(hs[1].onDone).toHaveBeenCalledWith(apiFile(2));

        calls[0].reject({ status: 500 });
        await tick();

        expect(calls).toHaveLength(5);
    });

    it('routes progress to the handlers of the file, each percent once', () => {
        const queue = createUploadQueue({ upload: fakeUpload });
        const a = handlers();
        const b = handlers();
        queue.add(browserFile('a'), a);
        queue.add(browserFile('b'), b);

        calls[1].onProgress(40);
        calls[1].onProgress(40);
        calls[1].onProgress(41);

        expect(b.onProgress.mock.calls).toEqual([[40], [41]]);
        expect(a.onProgress).not.toHaveBeenCalled();
    });

    it('never starts a waiting item that is aborted, and fires no handler', async () => {
        const queue = createUploadQueue({ concurrency: 1, upload: fakeUpload });
        const a = handlers();
        const b = handlers();
        queue.add(browserFile('a'), a);
        const keyB = queue.add(browserFile('b'), b);

        queue.abort(keyB);

        calls[0].resolve({ results: [apiFile()], errors: [] });
        await tick();

        expect(calls).toHaveLength(1);
        expect(b.onDone).not.toHaveBeenCalled();
        expect(b.onFail).not.toHaveBeenCalled();
    });

    it('aborts a running upload without calling a handler and starts the next', async () => {
        const queue = createUploadQueue({ concurrency: 1, upload: fakeUpload });
        const a = handlers();
        const b = handlers();
        const keyA = queue.add(browserFile('a'), a);
        queue.add(browserFile('b'), b);

        queue.abort(keyA);
        expect(calls[0].promise.abort).toHaveBeenCalledTimes(1);

        calls[0].reject({ status: 0, textStatus: 'abort' });
        await tick();

        expect(a.onDone).not.toHaveBeenCalled();
        expect(a.onFail).not.toHaveBeenCalled();
        expect(calls).toHaveLength(2);
    });

    it('ignores a result that arrives after the abort', async () => {
        const queue = createUploadQueue({ upload: fakeUpload });
        const a = handlers();
        const key = queue.add(browserFile('a'), a);

        queue.abort(key);
        calls[0].resolve({ results: [apiFile()], errors: [] });
        await tick();

        expect(a.onDone).not.toHaveBeenCalled();
    });

    it('fails with the per-file messages of the response', async () => {
        const queue = createUploadQueue({ upload: fakeUpload });
        const a = handlers();
        queue.add(browserFile('a'), a);

        calls[0].resolve({
            results: [],
            errors: [{ fileName: 'a', messages: ['Type not allowed.', 'Too big.'] }],
        });
        await tick();

        expect(a.onDone).not.toHaveBeenCalled();
        expect(a.onFail).toHaveBeenCalledWith('Type not allowed. Too big.');
    });

    it('fails with the messages of a 422', async () => {
        const queue = createUploadQueue({ upload: fakeUpload });
        const a = handlers();
        queue.add(browserFile('a'), a);

        calls[0].reject({ status: 422, errors: { files: ['No file.'] } });
        await tick();

        expect(a.onFail).toHaveBeenCalledWith('No file.');
    });

    it('fails with a short message on a 429 without logging it as an error', async () => {
        const queue = createUploadQueue({ upload: fakeUpload });
        const a = handlers();
        queue.add(browserFile('a'), a);

        calls[0].reject({ status: 429 });
        await tick();

        expect(a.onFail).toHaveBeenCalledTimes(1);
        expect(a.onFail.mock.calls[0][0]).toEqual(expect.any(String));
        expect(a.onFail.mock.calls[0][0]).not.toBe('');
        expect(logCalls.error).toHaveLength(0);
    });

    it('logs other failures and fails with a generic message', async () => {
        const queue = createUploadQueue({ upload: fakeUpload });
        const a = handlers();
        const response = { status: 500 };
        queue.add(browserFile('a'), a);

        calls[0].reject(response);
        await tick();

        expect(logCalls.error).toEqual([[response]]);
        expect(a.onFail).toHaveBeenCalledTimes(1);
        expect(a.onFail.mock.calls[0][0]).toBe('The server could not process the file.');
    });

    it('fails with the message of the server', async () => {
        // Yii's JSON error body, merged into the client's Response
        expect(await failWith({ status: 403, message: 'You are not allowed to upload files here.' }))
            .toBe('You are not allowed to upload files here.');
        expect(await failWith({ status: 500, message: 'The storage is full.' })).toBe('The storage is full.');
    });

    it('says a file is too large on a 413, without a number: the limit in front of PHP is a lower one', async () => {
        fileConfig.upload = { maxFileSize: 64 * 1024 * 1024 };
        expect(await failWith({ status: 413 })).toBe('The file is too large.');
    });

    it('says the connection was interrupted when no answer came', async () => {
        expect(await failWith({ status: 0, textStatus: 'error' })).toBe('The connection was interrupted.');
    });

    it('says the server could not process the file on a server error without a message', async () => {
        expect(await failWith({ status: 502 })).toBe('The server could not process the file.');
        expect(await failWith({ status: 500, response: '<html>Fatal error</html>' })).toBe('The server could not process the file.');
    });

    it('fails a response without result and errors', async () => {
        const queue = createUploadQueue({ upload: fakeUpload });
        const a = handlers();
        queue.add(browserFile('a'), a);

        calls[0].resolve({});
        await tick();

        expect(a.onFail).toHaveBeenCalledWith('The upload failed.');
    });

    it('fails other refusals without a message with the generic one', async () => {
        expect(await failWith({ status: 404 })).toBe('The upload failed.');
    });
});

describe('uploadFile', () => {
    const originalXhr = globalThis.jQuery.ajaxSettings.xhr;

    afterEach(() => {
        globalThis.jQuery.ajaxSettings.xhr = originalXhr;
    });

    it('posts one file and reports the percent of the body upload', () => {
        let cfg;
        const promise = Promise.resolve({});
        globalThis.humhubStubs.client.post = (url, config) => {
            cfg = config;
            return promise;
        };
        const fakeXhr = { upload: { addEventListener: vi.fn() } };
        globalThis.jQuery.ajaxSettings.xhr = () => fakeXhr;
        const onProgress = vi.fn();
        const file = browserFile('a.pdf');

        const result = uploadFile(file, onProgress);

        expect(result).toBe(promise);
        expect(cfg.data.getAll('files[]')).toEqual([file]);

        cfg.xhr();
        const listener = fakeXhr.upload.addEventListener.mock.calls[0][1];
        listener({ lengthComputable: true, loaded: 1, total: 4 });

        expect(onProgress).toHaveBeenCalledWith(25);
    });
});
