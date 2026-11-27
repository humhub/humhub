/**
 * The DOM-event bridge between an `<upload-list data-upload-bridge>` island and the legacy code that owns its files
 * (the jQuery `file.Preview` of `humhub.file.js`). `UploadList` connects it when it is mounted as an island on a tag
 * with `data-upload-bridge`.
 *
 * The host is the mount tag itself: the island renders into it, and both directions of events go through it.
 *
 * - **To the island**, dispatched natively on the tag (`tag.dispatchEvent(new CustomEvent(type, {detail}))`; jQuery's
 *   `trigger()` does not reach these listeners):
 *   - `upload:items` - detail: the full list `[{key, file, progress, error, busy}]`; replaces the rows. A missing
 *     `progress` means the file is uploaded; `busy` that the host is deleting the file.
 *   - `upload:add` - `{key, file, progress, error}`: a file that starts uploading (`progress` defaults to 0; pass
 *     `null` for an uploaded file). Replaces a row with the same key.
 *   - `upload:progress` - `{key, percent}`, for a file that is uploading; the row takes whole percents.
 *   - `upload:done` - `{key, file}`: uploaded, `file` the API's file shape.
 *   - `upload:fail` - `{key, message}`: refused; the row stays with the reason until the host drops it.
 *   - `upload:reset` - no detail: all rows go.
 *
 *   `file` is the API's file shape (`file\serializers\FileSerializer::file()`), or `{fileName, size}` while it
 *   uploads. Keys are compared with `===`; events for an unknown key are ignored.
 * - **From the island**, bubbling, so the host may listen on its container (jQuery: `event.originalEvent.detail`):
 *   - `upload:ready` - the island listens now. The host then sends its list (`upload:items`), which covers what
 *     happened before. The tag carries `data-upload-ready` while the island listens, for a host that connects later.
 *   - `upload:remove` - `{key, file}`: the user removed a row. The island keeps the row; the host decides, cancels
 *     the upload or deletes the file, and then sends the list without it. It may come twice for the same key.
 *
 * @since 1.20
 */
import { shallowReactive } from 'vue';
import { i18n } from '@humhub/vue';

export const UPLOAD_EVENTS = Object.freeze({
    ITEMS: 'upload:items',
    ADD: 'upload:add',
    PROGRESS: 'upload:progress',
    DONE: 'upload:done',
    FAIL: 'upload:fail',
    RESET: 'upload:reset',
    READY: 'upload:ready',
    REMOVE: 'upload:remove',
});

// A row as `UploadList` takes it; `progress` is what a missing one stands for
const toItem = (item, progress) => ({
    key: item.key,
    file: item.file || {},
    progress: item.progress === undefined ? progress : item.progress,
    error: item.error || null,
    busy: Boolean(item.busy),
});

// A row is reactive itself and changes in place, so a progress renders that row only. Shallow: `file` stays the
// host's object
const toRow = (item, progress) => shallowReactive(toItem(item, progress));

/**
 * Listens on `host` for the events above and keeps the rows they describe.
 *
 * @param {HTMLElement} host the mount tag
 * @param {Array} items the rows to start with (the island's `items` prop)
 * @returns {{items: Array, remove: (item: object) => void, disconnect: () => void}} `items` is reactive, and so is
 *   each row: progress, done and fail change a row in place
 */
export function connectUploadBridge(host, items = []) {
    const rows = shallowReactive(items.map((item) => toRow(item, null)));

    const find = (key) => rows.find((row) => row.key === key);
    const update = (key, changes, applies = () => true) => {
        const row = find(key);
        if (row && applies(row)) {
            Object.assign(row, changes);
        }
    };

    const handlers = {
        [UPLOAD_EVENTS.ITEMS]: (detail) => {
            rows.splice(0, rows.length, ...(Array.isArray(detail) ? detail : []).map((item) => toRow(item, null)));
        },
        [UPLOAD_EVENTS.ADD]: (detail) => {
            const row = find(detail.key);
            if (row) {
                Object.assign(row, toItem(detail, 0));
            } else {
                rows.push(toRow(detail, 0));
            }
        },
        // Whole percents, each once. Only while it uploads: a late progress must not start a finished file again
        [UPLOAD_EVENTS.PROGRESS]: (detail) => {
            const percent = Math.floor(Number(detail.percent) || 0);
            update(detail.key, { progress: percent }, (row) => row.progress !== null && !row.error && row.progress !== percent);
        },
        [UPLOAD_EVENTS.DONE]: (detail) => update(detail.key, {
            ...(detail.file ? { file: detail.file } : {}),
            progress: null,
            error: null,
        }),
        [UPLOAD_EVENTS.FAIL]: (detail) => update(detail.key, {
            progress: null,
            error: detail.message || i18n.t('FileModule.base', 'The upload failed.'),
        }),
        [UPLOAD_EVENTS.RESET]: () => rows.splice(0),
    };

    const listeners = Object.entries(handlers).map(([type, handler]) => {
        const listener = (event) => handler(event.detail || {});
        host.addEventListener(type, listener);

        return [type, listener];
    });

    host.setAttribute('data-upload-ready', '');
    host.dispatchEvent(new CustomEvent(UPLOAD_EVENTS.READY, { bubbles: true }));

    return {
        items: rows,
        remove(item) {
            host.dispatchEvent(new CustomEvent(UPLOAD_EVENTS.REMOVE, {
                bubbles: true,
                detail: { key: item.key, file: item.file },
            }));
        },
        disconnect() {
            listeners.forEach(([type, listener]) => host.removeEventListener(type, listener));
            host.removeAttribute('data-upload-ready');
        },
    };
}
