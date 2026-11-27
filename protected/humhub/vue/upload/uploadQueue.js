/**
 * Uploads files one per request, at most `concurrency` at a time, with progress per file
 * and abort. The response contract is the one of `uploadClient.js`: per request
 * `{results: [<file>], errors: [{fileName, messages}]}`, or `422 {errors: {files: […]}}`.
 * A refused request fails its file with the server's message, else with one by the status:
 * 413 too large, 0 (no answer) the connection was interrupted, 5xx the server could not
 * process it, 429 too many uploads.
 *
 * @since 1.20
 */
import { i18n, log } from '@humhub/vue';
import { uploadFile } from './uploadClient.js';
import { tooLargeMessage } from './fileSize.js';

// The reason a refused request gives: a 422's field errors, or the message of Yii's JSON error body, which the
// client's Response carries as its own properties
const serverMessage = (response) => {
    const errors = response && response.errors && typeof response.errors === 'object' ? response.errors : {};
    const messages = Object.values(errors).flat().filter((message) => typeof message === 'string' && message);
    if (messages.length) {
        return messages.join(' ');
    }

    return response && typeof response.message === 'string' ? response.message : '';
};

/**
 * @typedef {{
 *   onProgress?: (percent: number) => void,
 *   onDone?: (file: import('./uploadClient.js').UploadedFile) => void,
 *   onFail?: (message: string) => void
 * }} UploadHandlers
 */

/**
 * @param {{concurrency?: number, upload?: (file: File, onProgress: Function) => Promise}} [options]
 *   `upload` returns a promise with an `abort()` method, like `uploadFile()`
 * @returns {{add: (file: File, handlers?: UploadHandlers) => number, abort: (key: number) => void}}
 */
export function createUploadQueue({ concurrency = 3, upload = uploadFile } = {}) {
    const waiting = [];
    const running = new Map();
    let nextKey = 1;

    // The reason of a refused request: the server's, else one by its status
    const failMessage = (response) => {
        const status = (response && response.status) || 0;

        if (status === 429) {
            // The client has already shown the Retry-After notice.
            return i18n.t('FileModule.base', 'Too many uploads. Please try again in a moment.');
        }

        if (status !== 422) {
            log.error(response);
        }

        const message = serverMessage(response);
        if (message) {
            return message;
        }

        if (status === 413) {
            // A limit in front of PHP (e.g. the web server's), lower than the one checked before uploading: no number
            return tooLargeMessage(0);
        }

        if (status === 0) {
            return i18n.t('FileModule.base', 'The connection was interrupted.');
        }

        if (status >= 500) {
            return i18n.t('FileModule.base', 'The server could not process the file.');
        }

        return i18n.t('FileModule.base', 'The upload failed.');
    };

    const settle = (item, handler, ...args) => {
        running.delete(item.key);
        if (!item.aborted && item.handlers[handler]) {
            item.handlers[handler](...args);
        }
        next();
    };

    const start = (item) => {
        running.set(item.key, item);

        const promise = upload(item.file, (percent) => {
            // Each percent once: a row renders per call
            if (!item.aborted && item.handlers.onProgress && percent !== item.percent) {
                item.percent = percent;
                item.handlers.onProgress(percent);
            }
        });
        item.promise = promise;

        promise.then((response) => {
            const file = response && response.results && response.results[0];
            if (file) {
                settle(item, 'onDone', file);
                return;
            }

            const messages = ((response && response.errors) || []).flatMap((error) => error.messages || []);
            settle(item, 'onFail', messages.length
                ? messages.join(' ')
                : i18n.t('FileModule.base', 'The upload failed.'));
        }, (response) => {
            if (item.aborted) {
                settle(item, 'onFail');
                return;
            }
            settle(item, 'onFail', failMessage(response));
        });
    };

    function next() {
        while (running.size < concurrency && waiting.length) {
            start(waiting.shift());
        }
    }

    return {
        /** Queues a file; returns the key for `abort()`. */
        add(file, handlers = {}) {
            const item = { key: nextKey++, file, handlers, aborted: false, promise: null, percent: null };
            waiting.push(item);
            next();

            return item.key;
        },
        /** Cancels a waiting or running upload; an abort fires no handler. */
        abort(key) {
            const index = waiting.findIndex((item) => item.key === key);
            if (index !== -1) {
                waiting.splice(index, 1);
                return;
            }

            const item = running.get(key);
            if (item) {
                item.aborted = true;
                if (item.promise && item.promise.abort) {
                    item.promise.abort();
                }
            }
        },
    };
}
