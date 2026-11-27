<template>
    <div class="vue-upload-field" v-additions @drop.prevent="onDrop" @dragover.prevent @paste="onPaste">
        <Teleport :to="triggerTarget" :disabled="!triggerTarget">
            <div class="btn-group btn-group-sm">
                <span
                    ref="trigger"
                    class="btn btn-light fileinput-button tt"
                    role="button"
                    tabindex="0"
                    :aria-disabled="isDisabled ? 'true' : 'false'"
                    data-bs-toggle="tooltip"
                    data-placement="bottom"
                    :title="triggerTitle"
                    :data-bs-title="triggerTitle"
                    @click="openPicker()"
                    @keydown.enter.prevent="openPicker()"
                    @keydown.space.prevent="openPicker()"
                >
                    <i class="ti ti-cloud-upload" aria-hidden="true"></i>
                    <input
                        ref="input"
                        type="file"
                        class="d-none"
                        :multiple="multiple"
                        :accept="pickerAccept || accept || null"
                        :aria-label="triggerTitle"
                        :disabled="isDisabled"
                        @change="onInputChange"
                        @click.stop
                    >
                </span>
                <template v-if="handlers.length">
                    <button
                        type="button"
                        class="btn btn-light btn-icon-only dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                    ><span class="visually-hidden">{{ toggleLabel }}</span></button>
                    <ul class="dropdown-menu dropdown-menu-end" @click="onHandlerClick">
                        <FileHandlerItems :handlers="handlers" />
                    </ul>
                </template>
            </div>
        </Teleport>

        <UploadList ref="list" :items="entries" action="remove" @remove="onRemove" />

        <div v-if="allMessages.length" :id="errorId" class="invalid-feedback d-block">
            <div v-for="(message, index) in allMessages" :key="index">{{ message }}</div>
        </div>
    </div>
</template>

<script>
/**
 * A native `HumHubForm` field for file uploads — the Vue counterpart of the legacy
 * `file\widgets\Upload*` trio (`UploadButton` + `UploadProgress` + `FilePreview` driven by
 * `humhub.file.js`), and the field that lets a form shell drop its upload half (see
 * `docs/develop/ui-js-vuejs-forms.md`).
 *
 * The field owns the guid list; nothing is attached server-side while the user edits. It
 * uploads each file in its own request to `POST /api/v2/file`, at most 3 at a time (see
 * `upload/uploadQueue.js` and the endpoint's own docblock), keeps the returned file shapes in
 * `modelValue`, and the surrounding form submits their guids with its own request
 * (`fileList`). Removing an entry is therefore a local list operation: the file is simply
 * never attached and the file module's cron job cleans it up — the same net effect the legacy
 * widget had when its hidden guid input was removed before the form was saved.
 *
 * ```html
 * <UploadField attribute="fileList" v-model="files" :max="10" :handlers="handlers" />
 * ```
 *
 * | Prop | | |
 * |---|---|---|
 * | `modelValue` | `Array`, `[]` | the attached file shapes (`v-model`) |
 * | `max` | `Number`, `0` | maximum number of files, `0` = unlimited |
 * | `accept` | `String`, `null` | `accept` attribute of the file input |
 * | `multiple` | `Boolean`, `true` | allow selecting several files at once |
 * | `title` | `String`, `null` | trigger tooltip, defaults to the platform's "Upload files" |
 * | `handlers` | `Array`, `[]` | file handlers offered in a dropdown next to the trigger (see below) |
 * | `triggerTarget` | `Object\|String`, `null` | teleport target for the trigger button group, so a caller can place it in its own button row while the file rows stay here |
 *
 * Emits `update:modelValue` (the new file list) and `busy` (`true` while files upload,
 * `false` once none does, so a form can gate its submit button meanwhile).
 *
 * Public methods: `addFiles(files)` (browser `File` objects — the entry point for a caller's
 * own drop/paste zone), `openPicker(accept)` and `clear()` (cancels the uploads, too).
 *
 * Note on the file input's `@click.stop`: the trigger it sits inside opens the picker by
 * clicking the input programmatically, so a bubbling synthetic click would re-enter that
 * same handler forever (an infinite loop, caught by the test suite running out of memory).
 *
 * ## File rows
 *
 * The files show as rows (`UploadList`, docs/ui/file-upload.md), in the order they came: the
 * files of `modelValue`, then each added file as soon as it is added, with its progress. An
 * uploaded file keeps its row and joins `modelValue`, which lists the uploaded files in the
 * order of the rows. A refused file keeps its row with the reason and stays out of
 * `modelValue`. Removing a row cancels its upload, drops a refused file, or takes an uploaded
 * file out of `modelValue`. When the last row goes, the focus moves to the trigger.
 *
 * A file larger than the server takes (the core JS config's `file.upload.maxFileSize`) is
 * refused at once, in its row and without a request. `max` counts the uploaded files and
 * those still uploading; of files added over it, those that fit are taken and the field says
 * the maximum once. The form's `busy` state does not
 * disable the field while its own files upload: a form that turns the field's `busy` into its
 * own (the comment form) would otherwise lock the trigger and the rows' remove buttons until
 * all are done. Cancelling an upload or dropping a refused file is always possible, as it
 * changes nothing the form submits.
 *
 * The rows' labels come from `FileModule.base` and `base`: the island using the field
 * preloads them (without, they show in the source language).
 *
 * ## Drop, paste, and a caller's own zone
 *
 * The field's root handles `drop` and `paste` itself. A caller whose real drop zone is a
 * different element (the comment form: the whole comment box, matching the legacy
 * `dropZone`/`pasteZone` options) wires that element's events to `addFiles()`.
 *
 * ## Legacy file handlers
 *
 * `FileHandlerCollection::TYPE_CREATE` handlers (9 external modules implement
 * `BaseFileHandler`) come as data (`{label, icon, attributes}` - the core JS config's
 * `file.handlers`, see `file\serializers\FileHandlerSerializer`) and appear in this field's
 * dropdown as links carrying their attributes, so their `data-action-click` keeps being served by
 * `humhub.action.js`'s document-level delegate. Core's own handlers are "open the picker
 * with this `accept`" entries (`file.uploadByType`); those are handled natively here,
 * because the legacy action walks a DOM structure (`.btn-group` → `.fileinput-button` →
 * `data-action-target`) this component does not reproduce.
 *
 * A module's handler that creates a document (an office or diagram editor) announces the new
 * file on the global `humhub:file:created` event, as it always did: the field whose handler
 * entry was clicked last takes it over (the legacy file shape `FileHelper::getFileInfos()`
 * answers, `name` read as `fileName`).
 *
 * A handler that needs to hand an ALREADY-uploaded file to the field (a cloud-storage picker,
 * say) dispatches a DOM event on the field instead of reaching into a widget instance:
 *
 * ```js
 * element.dispatchEvent(new CustomEvent('humhub:file:attach', { detail: { files: [fileShape] } }));
 * ```
 *
 * ## Deliberate deviations from the legacy widget
 *
 * - File rows instead of the legacy `ul.files > li.file-preview-item` list, and a progress
 *   line around each row instead of one bar (`UploadProgress`) for all files.
 * - No `objectModel`/`objectId` attach-on-upload, and no `hideInStream` — see the endpoint's
 *   docblock.
 *
 * @since 1.20
 */
import { events, i18n, log } from '@humhub/vue';
import fieldMixin from './form/fieldMixin.js';
import { createUploadQueue } from './upload/uploadQueue.js';
import { maxFileSize, tooLargeMessage } from './upload/fileSize.js';
import FileHandlerItems from './upload/FileHandlerItems.vue';
import UploadList from './UploadList.vue';

/** Mirrors `humhub.file.js`'s `uploadByType` action, which core's create-handlers use. */
const UPLOAD_BY_TYPE_ACTION = 'file.uploadByType';

// A row of the field (see "File rows" above): `progress` 0–100 while the file uploads, `error` once it was refused
const isUploading = (entry) => entry.progress !== null && !entry.error;
const isUploaded = (entry) => entry.progress === null && !entry.error;
// An uploaded file the field got from outside (`modelValue`, a file handler), keyed by its guid; a file uploaded
// here keeps the key of its row
const fileEntry = (file) => ({ key: file.guid, file, progress: null, error: null });

/** The field whose module handler entry was clicked last: it takes the created files. */
let handlerField = null;

// Any handler entry clicked - this field's, or one in an editor's menu - resets it first
// (capture phase, before a field's own click handler claims it again).
document.addEventListener('click', (event) => {
    if (event.target instanceof Element && event.target.closest('a[data-action-process="file-handler"]')) {
        handlerField = null;
    }
}, true);

export default {
    components: { FileHandlerItems, UploadList },
    mixins: [fieldMixin],
    props: {
        modelValue: { type: Array, default: () => [] },
        max: { type: Number, default: 0 },
        accept: { type: String, default: null },
        multiple: { type: Boolean, default: true },
        title: { type: String, default: null },
        handlers: { type: Array, default: () => [] },
        triggerTarget: { type: [Object, String], default: null },
    },
    emits: ['update:modelValue', 'busy'],
    data() {
        return {
            // The rows: [{key, file, progress, error}], see "File rows" above
            entries: (this.modelValue || []).map(fileEntry),
            // A client-side refusal of the files added (the maximum)
            requestMessages: [],
            // `accept` of the file input for ONE picker opening (an upload-by-type handler),
            // reset as soon as the picker was opened.
            pickerAccept: null,
        };
    },
    computed: {
        uploading() {
            return this.entries.some(isUploading);
        },
        // Not while the form is busy with this field's own uploads, see "File rows" above
        isDisabled() {
            return this.disabled || (this.formBusy && !this.uploading);
        },
        triggerTitle() {
            return this.title || i18n.t('FileModule.base', 'Upload files');
        },
        toggleLabel() {
            return i18n.t('base', 'More actions');
        },
        allMessages() {
            return [
                ...this.requestMessages,
                // Errors the surrounding form assigned to this attribute (a 422 of the form's
                // own request, e.g. a guid the server rejected).
                ...this.errorMessages,
            ];
        },
    },
    watch: {
        uploading(uploading) {
            this.$emit('busy', uploading);
        },
        // From the parent: the files it dropped leave the rows, the files it added join them
        modelValue(files) {
            const list = files || [];
            const guids = new Set(list.map((file) => file.guid));
            const known = new Set(this.entries.map((entry) => entry.file.guid));
            const kept = this.entries.filter((entry) => !isUploaded(entry) || guids.has(entry.file.guid));
            const added = list.filter((file) => !known.has(file.guid)).map(fileEntry);

            if (kept.length !== this.entries.length || added.length) {
                this.entries = [...kept, ...added];
            }
        },
    },
    created() {
        this.queue = createUploadQueue();
        // Row key -> queue key, while the file waits or uploads
        this.queueKeys = new Map();
        this.lastKey = 0;
    },
    mounted() {
        // A legacy file handler hands over already-uploaded files this way - see the class
        // docblock. Registered on the field's own root, so a handler needs no global channel.
        this.$el.addEventListener('humhub:file:attach', this.onAttachEvent);
        events.on('humhub:file:created', this.onFileCreated);
    },
    beforeUnmount() {
        this.$el.removeEventListener('humhub:file:attach', this.onAttachEvent);
        events.off('humhub:file:created', this.onFileCreated);
        if (handlerField === this) {
            handlerField = null;
        }
        this.cancelUploads();
    },
    methods: {
        openPicker(accept = null) {
            if (this.isDisabled) {
                return;
            }

            // The attribute has to be on the input BEFORE the click opens the dialog, hence
            // the tick; it stays until the next opening (each one sets it explicitly).
            this.pickerAccept = accept;
            this.$nextTick(() => {
                // The field can be gone by now (a dropdown entry clicked as the surrounding
                // form unmounts), and a click on nothing must not become an unhandled error.
                if (this.$refs.input) {
                    this.$refs.input.click();
                }
            });
        },
        onInputChange(event) {
            const files = Array.from(event.target.files || []);
            // Reset the input so picking the same file twice in a row fires `change` again.
            event.target.value = '';
            this.pickerAccept = null;
            this.addFiles(files);
        },
        /**
         * Handles a click inside the handler dropdown. Only core's own "upload with this
         * accept type" entries are taken over (see the class docblock); everything else falls
         * through to `humhub.action.js`'s document-level `data-action-click` delegate.
         */
        onHandlerClick(event) {
            const entry = event.target.closest('[data-action-click]');

            if (!entry) {
                return;
            }

            if (entry.getAttribute('data-action-click') !== UPLOAD_BY_TYPE_ACTION) {
                // A module's handler: its action runs through humhub.action.js, and what it
                // creates comes back on `humhub:file:created` (see onFileCreated()).
                handlerField = this;
                return;
            }

            event.preventDefault();
            // Load-bearing: `humhub.action.js` binds `[data-action-click]` on `document`, so
            // without stopping propagation the legacy `file.uploadByType` ALSO runs for this
            // entry - and it opens a picker its own way (it clicks `.fileinput-button`, which
            // is this field's trigger, i.e. openPicker() WITHOUT an accept type). Two picker
            // openings in one user gesture: the browser honours the first one and drops the
            // rest, so the entry either opened an unfiltered dialog or none at all.
            event.stopPropagation();

            let accept = null;
            try {
                accept = JSON.parse(entry.getAttribute('data-action-params') || '{}').type || null;
            } catch (error) {
                log.warn('UploadField: could not read data-action-params of a file handler entry', error);
            }

            this.openPicker(accept);
        },
        onDrop(event) {
            this.addFiles(Array.from((event.dataTransfer && event.dataTransfer.files) || []));
        },
        onPaste(event) {
            const files = Array.from((event.clipboardData && event.clipboardData.files) || []);
            if (files.length) {
                this.addFiles(files);
            }
        },
        /** A file a module's handler created (see the class docblock, "Legacy file handlers"). */
        onFileCreated(event, ...created) {
            if (handlerField !== this) {
                return;
            }
            handlerField = null;

            // `event.trigger('humhub:file:created', [file])` hands the files over as arguments
            const files = created.flat()
                .filter(Boolean)
                .map((file) => ({ ...file, fileName: file.fileName || file.name }));
            this.attachFiles(files);
        },
        onAttachEvent(event) {
            this.attachFiles((event.detail && event.detail.files) || []);
        },
        /** Adds files that are uploaded already. */
        attachFiles(files) {
            const known = new Set(this.entries.map((entry) => entry.file.guid));
            const added = files.filter((file) => !known.has(file.guid));

            if (added.length) {
                this.entries.push(...added.map(fileEntry));
                this.emitFiles();
            }
        },
        /**
         * Adds browser `File` objects: each gets its row at once and is uploaded in its own
         * request (see "File rows" above). A file over the size limit of uploads (the core JS
         * config's `file.upload.maxFileSize`) is refused in its row without a request.
         *
         * @param {File[]|FileList} files
         */
        addFiles(files) {
            const list = Array.from(files || []);
            this.clearMessages();

            if (!list.length || this.isDisabled) {
                return;
            }

            // Refused files take no place of the maximum
            const limit = maxFileSize();
            const tooLarge = (file) => limit > 0 && file.size > limit;
            const taken = new Set(this.withinMax(list.filter((file) => !tooLarge(file))));

            list.forEach((file) => {
                if (tooLarge(file)) {
                    this.refuse(file, tooLargeMessage(limit));
                } else if (taken.has(file)) {
                    this.upload(file);
                }
            });
        },
        /** A file refused before its upload: its row with the reason. */
        refuse(file, error) {
            this.entries.push({ key: ++this.lastKey, file: { fileName: file.name, size: file.size }, progress: null, error });
        },
        upload(file) {
            const key = ++this.lastKey;
            this.entries.push({ key, file: { fileName: file.name, size: file.size }, progress: 0, error: null });

            this.queueKeys.set(key, this.queue.add(file, {
                onProgress: (progress) => this.updateEntry(key, { progress }),
                onDone: (uploaded) => {
                    this.queueKeys.delete(key);
                    this.updateEntry(key, { file: uploaded, progress: null });
                    this.emitFiles();
                },
                onFail: (error) => {
                    this.queueKeys.delete(key);
                    this.updateEntry(key, { progress: null, error });
                },
            }));
        },
        updateEntry(key, values) {
            // The entry of the reactive list, so the change renders
            const entry = this.entries.find((candidate) => candidate.key === key);
            if (entry) {
                Object.assign(entry, values);
            }
        },
        /** A row's remove button (see "File rows" above). */
        onRemove(item) {
            const entry = this.entries.find((candidate) => candidate.key === item.key);
            if (!entry || (isUploaded(entry) && this.isDisabled)) {
                return;
            }

            const list = this.$refs.list && this.$refs.list.$el;
            const hadFocus = Boolean(list && list.contains(document.activeElement));

            if (this.queueKeys.has(entry.key)) {
                this.queue.abort(this.queueKeys.get(entry.key));
                this.queueKeys.delete(entry.key);
            }
            this.entries = this.entries.filter((candidate) => candidate.key !== entry.key);

            if (isUploaded(entry)) {
                this.clearMessages();
                this.emitFiles();
            }

            // With rows left, the list hands the focus on to the next or previous one
            if (hadFocus && !this.entries.length) {
                this.$nextTick(() => {
                    // Unless the user has moved on meanwhile
                    const current = document.activeElement;
                    if (!current || current === document.body || !current.isConnected || list.contains(current)) {
                        this.focus();
                    }
                });
            }
        },
        /** Drops every file and cancels the uploads (e.g. after the surrounding form was submitted). */
        clear() {
            this.cancelUploads();
            this.entries = [];
            this.clearMessages();
            this.emitFiles();
        },
        cancelUploads() {
            this.queueKeys.forEach((queueKey) => this.queue.abort(queueKey));
            this.queueKeys.clear();
        },
        /** Emits the uploaded files, in the order of their rows. */
        emitFiles() {
            this.clearOwnError();
            this.$emit('update:modelValue', this.entries.filter(isUploaded).map((entry) => entry.file));
        },
        /**
         * The files that fit into this field's maximum, counting the uploaded files and those
         * still uploading; the files over it are left out with one message, as in the legacy
         * upload widget. The message reuses the key that widget passes to the browser, so
         * translations exist.
         */
        withinMax(files) {
            if (this.max <= 0) {
                return files;
            }

            const room = Math.max(0, this.max - this.entries.filter((entry) => !entry.error).length);
            if (files.length > room) {
                this.requestMessages = [i18n.t(
                    'FileModule.base',
                    'This upload field only allows a maximum of {n,plural,=1{# file} other{# files}}.',
                    { n: this.max },
                )];
            }

            return files.slice(0, room);
        },
        clearMessages() {
            this.requestMessages = [];
        },
        /** `HumHubForm.focusFirstError()` entry point. */
        focus() {
            // A ref, as the trigger may be teleported out of the field
            if (this.$refs.trigger) {
                this.$refs.trigger.focus();
            }
        },
    },
};
</script>
