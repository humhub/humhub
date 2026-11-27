<template>
    <div class="list-group file-list" :hidden="!rows.length && !leaving">
        <TransitionGroup
            name="file-row"
            tag="div"
            class="file-list-rows"
            role="list"
            @enter="onEnter"
            @before-leave="onBeforeLeave"
            @after-leave="onLeft"
            @leave-cancelled="onLeft"
        >
            <UploadRow
                v-for="item in rows"
                ref="row"
                :key="item.key"
                :item="item"
                :action="action"
                :popover-placement="popoverPlacement"
                @remove="remove(item)"
            />
        </TransitionGroup>
    </div>
</template>

<script>
/**
 * The rows of the files of a form (docs/ui/file-upload.md): files being uploaded, refused files with their reason and
 * uploaded files, each a core `FileRow` with remove (or download) as its trailing action. A row renders through
 * `upload/UploadRow.vue`, so the progress of a file renders its row only.
 *
 * - `items`: `[{key, file, progress, error, busy}]`; `file` is the API's file shape
 *   (`file\serializers\FileSerializer::file()`) or `{fileName, size}` while it uploads, `progress` 0–100 while it
 *   uploads (else `null`), `error` the reason a file was refused, `busy` while its removal is under way. Change an
 *   item in place (a reactive item) for its progress: only its row renders then.
 * - `action`: `remove` (default) or `download`.
 * - `popoverPlacement`: where a row's image preview opens on hover (`right` by default); empty for none.
 * - Emits `remove(item)`; the owner of the items drops it.
 *
 * Rows grow in and collapse out (`file-row-*` in `_file.scss`, to the height measured here), so the rows below follow.
 * The list stays rendered and is `hidden` without rows, once the last one has collapsed. Uploaded and refused files
 * are announced once each; the files the list starts with, and files that arrive uploaded, are not. When the focus
 * is in a row that goes, it moves on to the next row, else to the previous one. When no row is left, the owner hands
 * it on, to its upload button (docs/ui/keyboard.md, "Moving the focus").
 *
 * Nested in a component (`UploadField`), the list shows its `items`. Mounted as an island on a tag with
 * `data-upload-bridge` (`file\widgets\FilePreview`), the host's DOM events fill it instead, with `items` as the start;
 * see `upload/uploadBridge.js` for the events.
 *
 * @since 1.20
 */
import { announce, i18n } from '@humhub/vue';
import { connectUploadBridge } from './upload/uploadBridge.js';
import UploadRow from './upload/UploadRow.vue';

// `announce()` keeps only the last message it gets within its delay: files that settle close together are said in
// one message
const ANNOUNCE_BATCH_MS = 150;

const stateOf = (item) => {
    if (item.error) {
        return 'failed';
    }

    return item.progress === null || item.progress === undefined ? 'done' : 'uploading';
};

// The height the row's max-height transition runs to or from (`_file.scss`). None in a hidden container: the row
// then shows at once instead of staying clipped
const setRowHeight = (el) => {
    if (el.offsetHeight) {
        el.style.setProperty('--hh-file-row-height', `${el.offsetHeight}px`);
    }
};

export default {
    name: 'UploadList',
    components: { UploadRow },
    i18nCategories: ['FileModule.base', 'base'],
    props: {
        items: { type: Array, default: () => [] },
        action: { type: String, default: 'remove', validator: (value) => ['remove', 'download'].includes(value) },
        popoverPlacement: { type: String, default: 'right' },
    },
    emits: ['remove'],
    data() {
        return {
            // The bridge's rows, in place of `items` (island on a `data-upload-bridge` tag)
            bridgeItems: null,
            // Rows still collapsing
            leaving: 0,
        };
    },
    computed: {
        rows() {
            return this.bridgeItems || this.items;
        },
        // The keys and states of the rows, not their progress: what the watcher below follows
        rowStates() {
            return this.rows.map((item) => `${item.key}:${stateOf(item)}`).join('\n');
        },
    },
    watch: {
        // Before the rows are rendered: the row that goes still holds the focus
        rowStates() {
            this.handOverFocus(this.rows);
            this.announceChanges(this.rows);
            this.keys = this.rows.map((item) => item.key);
        },
    },
    created() {
        this.keys = this.items.map((item) => item.key);
        this.states = new Map(this.items.map((item) => [item.key, stateOf(item)]));
        this.announcements = [];
        this.announceTimer = null;
        this.bridge = null;
    },
    mounted() {
        const host = this.$el.parentElement;
        if (!this.$parent && host && host.hasAttribute('data-upload-bridge')) {
            this.bridge = connectUploadBridge(host, this.items);
            this.bridgeItems = this.bridge.items;
        }
    },
    beforeUnmount() {
        if (this.bridge) {
            this.bridge.disconnect();
        }
        this.flushAnnouncements();
    },
    methods: {
        // The element of a rendered row
        rowElement(key) {
            const row = (this.$refs.row || []).find((component) => component.item.key === key);

            return row ? row.$el : null;
        },
        remove(item) {
            if (this.bridge) {
                this.bridge.remove(item);
            }
            this.$emit('remove', item);
        },
        handOverFocus(rows) {
            const active = document.activeElement;
            const remaining = new Set(rows.map((item) => item.key));
            const index = this.keys.findIndex((key) => !remaining.has(key) && this.rowElement(key)?.contains(active));
            if (index === -1) {
                return;
            }

            const removed = this.rowElement(this.keys[index]);
            const neighbour = this.keys.slice(index + 1).find((key) => remaining.has(key))
                ?? this.keys.slice(0, index).reverse().find((key) => remaining.has(key));
            if (neighbour === undefined) {
                return;
            }

            this.$nextTick(() => {
                // Unless the user has moved on meanwhile
                const current = document.activeElement;
                if (current && current !== document.body && current.isConnected && !removed.contains(current)) {
                    return;
                }

                const row = this.rowElement(neighbour);
                const target = row && (row.querySelector('.file-row-action') || row);
                if (!target) {
                    return;
                }
                if (target === row) {
                    row.setAttribute('tabindex', '-1');
                }
                target.focus({ preventScroll: true });
            });
        },
        announceChanges(rows) {
            const states = new Map();
            rows.forEach((item) => {
                const state = stateOf(item);
                const previous = this.states.get(item.key);
                const fileName = item.file && item.file.fileName;

                if (state === 'done' && previous === 'uploading') {
                    this.queueAnnouncement(i18n.t('FileModule.base', 'Uploaded {fileName}', { fileName }));
                } else if (state === 'failed' && previous !== 'failed') {
                    this.queueAnnouncement(i18n.t('FileModule.base', 'Could not upload {fileName}: {error}', {
                        fileName,
                        error: item.error,
                    }));
                }
                states.set(item.key, state);
            });
            this.states = states;
        },
        queueAnnouncement(message) {
            this.announcements.push(message);
            if (!this.announceTimer) {
                this.announceTimer = setTimeout(() => this.flushAnnouncements(), ANNOUNCE_BATCH_MS);
            }
        },
        flushAnnouncements() {
            clearTimeout(this.announceTimer);
            this.announceTimer = null;
            if (this.announcements.length) {
                announce(this.announcements.join(' '));
                this.announcements = [];
            }
        },
        // Measured without the start state and without a transition, which would otherwise start right here
        onEnter(el) {
            el.style.transition = 'none';
            el.classList.remove('file-row-enter-from');
            setRowHeight(el);
            el.classList.add('file-row-enter-from');
            void el.offsetHeight;
            el.style.transition = '';
        },
        onBeforeLeave(el) {
            setRowHeight(el);
            this.leaving++;
        },
        onLeft() {
            this.leaving--;
        },
    },
};
</script>
