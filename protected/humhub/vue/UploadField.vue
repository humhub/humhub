<template>
    <div class="vue-upload-field" v-additions @drop.prevent="onDrop" @dragover.prevent @paste="onPaste">
        <Teleport :to="triggerTarget" :disabled="!triggerTarget">
            <div class="btn-group btn-group-sm">
                <span
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
                    <i class="fa fa-cloud-upload" aria-hidden="true"></i>
                    <input
                        ref="input"
                        type="file"
                        class="d-none"
                        :multiple="multiple"
                        :accept="pickerAccept || accept || null"
                        :disabled="isDisabled"
                        @change="onInputChange"
                        @click.stop
                    >
                </span>
                <template v-if="handlersHtml">
                    <button
                        type="button"
                        class="btn btn-light btn-icon-only dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-haspopup="true"
                        aria-expanded="false"
                    ><span class="visually-hidden">{{ toggleLabel }}</span></button>
                    <ul class="dropdown-menu dropdown-menu-end" v-html="handlersHtml" @click="onHandlerClick"></ul>
                </template>
            </div>
        </Teleport>

        <div v-if="progress !== null" class="progress mt-2" style="height: 6px">
            <div
                class="progress-bar progress-bar-info"
                role="progressbar"
                :aria-valuenow="progress"
                aria-valuemin="0"
                aria-valuemax="100"
                :style="{ width: progress + '%' }"
            ></div>
        </div>

        <ul v-if="files.length" class="files">
            <li
                v-for="file in files"
                :key="file.guid"
                class="file-preview-item mime"
                :class="file.mimeIcon"
                :data-preview-guid="file.guid"
            >
                <span class="file-preview-content">{{ file.fileName }}&nbsp;&nbsp;<span
                    class="file_upload_remove_link"
                    role="button"
                    tabindex="0"
                    :aria-label="removeLabel"
                    @click="removeFile(file)"
                    @keydown.enter.prevent="removeFile(file)"
                ><i class="fa fa-trash-o" aria-hidden="true"></i>&nbsp;</span></span>
            </li>
        </ul>

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
 * uploads to `POST /api/v2/file` (see `upload/uploadClient.js` and the endpoint's own
 * docblock), keeps the returned file shapes in `modelValue`, and the surrounding form submits
 * their guids with its own request (`fileList`). Removing an entry is therefore a local list
 * operation: the file is simply never attached and the file module's cron job cleans it up —
 * the same net effect the legacy widget had when its hidden guid input was removed before the
 * form was saved.
 *
 * ```html
 * <UploadField attribute="fileList" v-model="files" :max="10" :handlers-html="handlersHtml" />
 * ```
 *
 * | Prop | | |
 * |---|---|---|
 * | `modelValue` | `Array`, `[]` | the attached file shapes (`v-model`) |
 * | `max` | `Number`, `0` | maximum number of files, `0` = unlimited |
 * | `accept` | `String`, `null` | `accept` attribute of the file input |
 * | `multiple` | `Boolean`, `true` | allow selecting several files at once |
 * | `title` | `String`, `null` | trigger tooltip, defaults to the platform's "Upload files" |
 * | `handlersHtml` | `String`, `''` | server-rendered `<li>` entries of additional file handlers (see below) |
 * | `triggerTarget` | `Object\|String`, `null` | teleport target for the trigger button group, so a caller can place it in its own button row while progress/preview stay here |
 *
 * Emits `update:modelValue` (the new file list) and `busy` (`true`/`false` around a request,
 * so a form can gate its submit button while an upload is in flight).
 *
 * Public methods: `addFiles(files)` (browser `File` objects — the entry point for a caller's
 * own drop/paste zone), `openPicker(accept)` and `clear()`.
 *
 * Note on the file input's `@click.stop`: the trigger it sits inside opens the picker by
 * clicking the input programmatically, so a bubbling synthetic click would re-enter that
 * same handler forever (an infinite loop, caught by the test suite running out of memory).
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
 * `BaseFileHandler`) are rendered by the server into `handlersHtml` and appear in this
 * field's dropdown, so their `data-action-click` attributes keep being served by
 * `humhub.action.js`'s document-level delegate. Core's own handlers are "open the picker
 * with this `accept`" entries (`file.uploadByType`); those are handled natively here,
 * because the legacy action walks a DOM structure (`.btn-group` → `.fileinput-button` →
 * `data-action-target`) this component does not reproduce.
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
 * - The preview reproduces the legacy EDIT-mode template (`ul.files > li.file-preview-item`
 *   with the mime-icon class, the file name and a `.file_upload_remove_link` trash icon), so
 *   existing theme CSS applies. The read-only variants (image popovers, `openLink`, size
 *   suffix) belong to `FilePreview`'s display mode, which this field never had.
 * - One progress bar for the batch (`.progress > .progress-bar`, the markup
 *   `ui.progress.Progress` renders), not one per file: the request IS the batch.
 * - No `objectModel`/`objectId` attach-on-upload, and no `hideInStream` — see the endpoint's
 *   docblock.
 *
 * @since 1.20
 */
import { i18n, log } from '@humhub/vue';
import fieldMixin from './form/fieldMixin.js';
import { uploadFiles } from './upload/uploadClient.js';

/** Mirrors `humhub.file.js`'s `uploadByType` action, which core's create-handlers use. */
const UPLOAD_BY_TYPE_ACTION = 'file.uploadByType';

export default {
    mixins: [fieldMixin],
    props: {
        modelValue: { type: Array, default: () => [] },
        max: { type: Number, default: 0 },
        accept: { type: String, default: null },
        multiple: { type: Boolean, default: true },
        title: { type: String, default: null },
        handlersHtml: { type: String, default: '' },
        triggerTarget: { type: [Object, String], default: null },
    },
    emits: ['update:modelValue', 'busy'],
    data() {
        return {
            // Progress of the request in flight, `null` while none is.
            progress: null,
            // Per-file outcomes of the last request: [{fileName, messages}].
            fileErrors: [],
            // Messages about the request as a whole (a 422, or a client-side refusal).
            requestMessages: [],
            // `accept` of the file input for ONE picker opening (an upload-by-type handler),
            // reset as soon as the picker was opened.
            pickerAccept: null,
        };
    },
    computed: {
        files() {
            return this.modelValue || [];
        },
        triggerTitle() {
            return this.title || i18n.t('FileModule.base', 'Upload files');
        },
        toggleLabel() {
            return i18n.t('base', 'Toggle Dropdown');
        },
        removeLabel() {
            return i18n.t('base', 'Delete');
        },
        allMessages() {
            return [
                ...this.requestMessages,
                ...this.fileErrors.flatMap((error) => (error.messages || []).map(
                    (message) => `${error.fileName}: ${message}`,
                )),
                // Errors the surrounding form assigned to this attribute (a 422 of the form's
                // own request, e.g. a guid the server rejected).
                ...this.errorMessages,
            ];
        },
    },
    mounted() {
        // A legacy file handler hands over already-uploaded files this way - see the class
        // docblock. Registered on the field's own root, so a handler needs no global channel.
        this.$el.addEventListener('humhub:file:attach', this.onAttachEvent);
    },
    beforeUnmount() {
        this.$el.removeEventListener('humhub:file:attach', this.onAttachEvent);
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

            if (!entry || entry.getAttribute('data-action-click') !== UPLOAD_BY_TYPE_ACTION) {
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
        onAttachEvent(event) {
            const files = (event.detail && event.detail.files) || [];
            if (files.length) {
                this.emitFiles([...this.files, ...files]);
            }
        },
        /**
         * Uploads browser `File` objects and appends what the server stored.
         *
         * @param {File[]|FileList} files
         * @returns {Promise} resolves once the request finished (rejections are handled here)
         */
        addFiles(files) {
            const list = Array.from(files || []);
            this.clearMessages();

            if (!list.length || this.isDisabled) {
                return Promise.resolve();
            }

            if (!this.acceptsCount(list.length)) {
                return Promise.resolve();
            }

            this.progress = 0;
            this.$emit('busy', true);

            return uploadFiles(list, (percent) => {
                this.progress = percent;
            }).then((response) => {
                const results = (response && response.results) || [];
                this.fileErrors = (response && response.errors) || [];

                if (results.length) {
                    this.emitFiles([...this.files, ...results]);
                }
            }).catch((response) => {
                // Same split the rest of the islands use: a validation failure is rendered at
                // the field, anything else goes to the platform's error surface (the status
                // bar) rather than being paraphrased here.
                if (response && response.status === 422 && response.errors) {
                    this.requestMessages = Object.values(response.errors).flat();
                } else {
                    log.error(response, true);
                }
            }).finally(() => {
                this.progress = null;
                this.$emit('busy', false);
            });
        },
        removeFile(file) {
            if (this.isDisabled) {
                return;
            }

            this.clearMessages();
            this.emitFiles(this.files.filter((candidate) => candidate.guid !== file.guid));
        },
        /** Drops every attached file (e.g. after the surrounding form was submitted). */
        clear() {
            this.clearMessages();
            this.emitFiles([]);
        },
        emitFiles(files) {
            this.clearOwnError();
            this.$emit('update:modelValue', files);
        },
        /**
         * Client-side guard against a selection the server would reject wholesale: this
         * field's own maximum, and PHP's `max_file_uploads` per request. Both messages reuse
         * the keys the legacy upload widget passes to the browser, so translations exist.
         */
        acceptsCount(count) {
            if (this.max > 0 && this.files.length + count > this.max) {
                this.requestMessages = [i18n.t(
                    'FileModule.base',
                    'This upload field only allows a maximum of {n,plural,=1{# file} other{# files}}.',
                    { n: this.max },
                )];

                return false;
            }

            return true;
        },
        clearMessages() {
            this.fileErrors = [];
            this.requestMessages = [];
        },
        /** `HumHubForm.focusFirstError()` entry point. */
        focus() {
            const trigger = this.$el.querySelector('.fileinput-button');
            if (trigger) {
                trigger.focus();
            }
        },
    },
};
</script>
