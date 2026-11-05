<template>
    <HumHubForm ref="form" model-name="Comment" :busy="busy || uploadBusy" @submit="onSubmit">
        <div ref="zone" class="comment_create content_create">
            <hr>
            <div class="richtext-create-input-group input-group" :class="{ scrollActive }">
                <RichTextField
                    ref="richtext"
                    class="mb-3"
                    attribute="message"
                    layout="inline"
                    :instance-key="formInstanceKey"
                    :placeholder="placeholder"
                    :mentioning-url="mentioningUrl"
                    :plugin-options="{ maxHeight: '300px' }"
                    upload
                    :handlers="fileHandlers"
                    @scroll-active="scrollActive = true"
                    @scroll-inactive="scrollActive = false"
                />
                <div ref="buttons" class="richtext-create-buttons">
                    <SubmitButton
                        :loader="false"
                        class="btn btn-accent btn-comment-submit btn-sm btn-icon-only"
                        :aria-label="sendLabel"
                        @click="onSubmit"
                    ><i class="ti ti-send" aria-hidden="true"></i></SubmitButton>
                </div>
            </div>
        </div>
        <UploadField
            ref="upload"
            attribute="fileList"
            v-model="files"
            :max="uploadMax"
            :handlers="fileHandlers"
            :title="attachLabel"
            :trigger-target="triggerTarget"
            @busy="uploadBusy = $event"
        />
    </HumHubForm>
</template>

<script>
/**
 * Hosts a comment create/reply/edit form, built on the `HumHubForm` suite (see
 * `protected/humhub/vue/HumHubForm.vue`'s own docblock, and
 * `docs/develop/ui-js-vuejs-forms.md`): the editor is a `RichTextField`, attachments an
 * `UploadField`; this component submits to the JSON API, and 422 field errors flow through
 * `HumHubForm`'s `setErrors()`/`clearErrors()`.
 *
 * Two modes, selected by whether `editCommentId` is set:
 *  - create (default): POSTs to the `comment` endpoint with `contentId`
 *    (+ `parentCommentId` for a reply — see commentApi.js's createComment()),
 *    clears the editor and the attachments on success and emits `created` with the
 *    new comment JSON.
 *  - edit: PATCHes `comment/<editCommentId>` instead and emits `updated` with the
 *    updated comment. The caller discards this component on success/cancel, but it
 *    still clear()s first: the editor's draft backup (sessionStorage) outlives the
 *    unmount (see `cancelEdit()` in CommentEntry for the discard twin).
 *
 * `initialMessage` (edit mode only) is applied via `RichTextField.setValue()` once this
 * component has mounted - children mount first, so the editor has booted by then; the
 * `$nextTick()` is a defensive margin should that boot ever become async.
 *
 * ## Error handling
 *
 * `onSubmit()` clears the errors before every request and, on a 422, hands the response
 * to `setErrors()`, which unwraps it (see `HumHubForm.vue`). A rejected `client.post()`
 * resolves a `client.Response` whose JSON body is merged onto itself, so a 422's field
 * errors are read from the top-level `response.errors` (see commentApi.js's
 * `extractFieldErrors()`); anything else - a 403/404 - is logged with a status message.
 * `RichTextField` (`attribute="message"`) renders its own messages.
 *
 * ## Submit button
 *
 * `SubmitButton` (`type="submit"`) sits in `.richtext-create-buttons`, bottom-right of the
 * input next to the upload trigger, as the legacy form had it. It keeps an explicit
 * `@click="onSubmit"`, whose `preventDefault()` cancels the native submission - so a click,
 * and the editor's Ctrl+S (its `save` plugin clicks the `[type="submit"]` of the editor's
 * form, i.e. this one), each submit once. `:loader="false"` keeps its content unchanged
 * while busy; only `disabled` changes. The `busy` check at the top of `onSubmit()` guards
 * against a second submission while one is in flight.
 *
 * It is icon-only (`send`), named by its `aria-label`.
 *
 * ## Editor
 *
 * Inline layout (the menu shows on focus), at most 300px high - the scroll shadow
 * (`scrollActive`) follows the editor's `scroll-active`/`scroll-inactive` -, mentions
 * looked up within the content (`user/mentioning/content`). The editor's upload puts a file
 * into the text (through `POST /api/v2/file`; the server attaches what the text references
 * on save) - a file pasted into the editor goes there too, not into the
 * attachments. Its upload menu also offers the file handlers (the core JS config's
 * `file.handlers`), whose files go into the text as well; the same handlers in the upload
 * field's dropdown attach their files.
 *
 * ## Attachments
 *
 * `UploadField` (see `docs/develop/ui-js-vuejs-forms.md`) owns the list of files attached
 * in the current editing session (`files`); their guids are submitted as `fileList` and the
 * list is cleared along with the editor. Its trigger is teleported into
 * `.richtext-create-buttons`, before the submit button, while progress and the file list
 * render below the editor. Drop and paste work on the whole comment box, as the legacy
 * widget's `dropZone`/`pasteZone` did — see `onZoneDrop()`.
 *
 * Two behaviours worth stating explicitly, both matching the pre-Vue form:
 *  - In EDIT mode the field starts EMPTY and only newly added files are submitted.
 *    `Comment::afterSave()` ATTACHES `fileList` and never detaches, so a comment's
 *    existing attachments survive an edit untouched (and cannot be removed here —
 *    the legacy edit form could not either).
 *  - Removing an entry before submitting means it is never attached; the uploaded
 *    file stays behind unattached and the file module's own cron job cleans it up.
 */
import { getConfig, i18n, log, url } from '@humhub/vue';
import { createComment, extractFieldErrors, updateComment } from './commentApi.js';

// HumHubForm/RichTextField/SubmitButton/UploadField are NOT
// imported here — they live at protected/humhub/vue/ (see docs/develop/ui-js-vuejs.md)
// and resolve through the global Vue component registry (every registered component is
// available in every island, see humhub.vue.js's register()). CoreVueAsset must register
// before this island's own script runs — enforced via CommentVueAsset::$depends, not by
// import order here.
export default {
    props: {
        contentId: { type: Number, required: true },
        parentCommentId: { type: Number, default: null },
        // When set, this form edits an existing comment instead of creating one.
        editCommentId: { type: Number, default: null },
        // Edit mode only: the raw markdown to prefill the editor with once booted.
        initialMessage: { type: String, default: null },
    },
    emits: ['created', 'updated'],
    data() {
        return {
            busy: false,
            // The button row the upload trigger is teleported into, once mounted
            // (`null` renders it in place until then).
            triggerTarget: null,
            // While the height-limited editor scrolls (see "Editor" above)
            scrollActive: false,
            // Files attached in THIS editing session (API file shapes, owned by the
            // UploadField below) - see the "Attachments" docblock section.
            files: [],
            // True while the upload field has a request in flight, so the submit button
            // stays disabled until the guids it would submit actually exist.
            uploadBusy: false,
        };
    },
    computed: {
        // Same key the legacy submit button's aria-label used (see the
        // "Submit button" docblock section above) - NOT a CommentModule.base
        // key. CommentSection preloads 'ContentModule.base' alongside its
        // own category for exactly this.
        sendLabel() {
            return i18n.t('ContentModule.base', 'Submit');
        },
        // The legacy form's upload button label - again a ContentModule.base key
        // CommentSection preloads.
        attachLabel() {
            return i18n.t('ContentModule.base', 'Attach Files');
        },
        // The page's, not the content's (the core JS config's `file` block)
        uploadMax() {
            return (getConfig('file').upload || {}).maxAttachedFiles || 0;
        },
        fileHandlers() {
            return getConfig('file').handlers || [];
        },
        // The editor's identity (see RichTextField's "Draft backup" docblock section): unique
        // among the comment forms mounted at once — the main form (`c<contentId>`), a reply
        // form per entry (`-r<parentCommentId>`), an edit form per comment
        // (`-e<editCommentId>`) — and stable across page loads, so a draft is restored into
        // the form it was typed in and no other.
        formInstanceKey() {
            if (this.editCommentId !== null) {
                return 'c' + this.contentId + '-e' + this.editCommentId;
            }
            if (this.parentCommentId !== null) {
                return 'c' + this.contentId + '-r' + this.parentCommentId;
            }
            return 'c' + this.contentId;
        },
        placeholder() {
            return i18n.t('CommentModule.base', 'Write a new comment...');
        },
        mentioningUrl() {
            return url('user/mentioning/content', { id: this.contentId });
        },
    },
    mounted() {
        this.triggerTarget = this.$refs.buttons;
        this.$refs.zone.addEventListener('drop', this.onZoneDrop);
        this.$refs.zone.addEventListener('dragover', this.onZoneDragOver);
        this.$refs.zone.addEventListener('paste', this.onZonePaste);
        if (this.initialMessage !== null) {
            this.$nextTick(() => {
                if (this.$refs.richtext) {
                    this.$refs.richtext.setValue(this.initialMessage);
                }
            });
        }
    },
    beforeUnmount() {
        this.$refs.zone.removeEventListener('drop', this.onZoneDrop);
        this.$refs.zone.removeEventListener('dragover', this.onZoneDragOver);
        this.$refs.zone.removeEventListener('paste', this.onZonePaste);
    },
    methods: {
        onSubmit(event) {
            // Called from the button's @click and from HumHubForm's `submit` (a native
            // submission of its form) - tolerate a bare call with no event too.
            if (event) {
                event.preventDefault();
            }

            // One request at a time (see the "Submit button" docblock section)
            if (this.busy) {
                return;
            }

            const isEdit = this.editCommentId !== null;
            const payload = {
                message: this.$refs.richtext.getValue(),
                fileList: this.files.map((file) => file.guid),
            };

            this.busy = true;
            this.$refs.form.clearErrors();

            const request = isEdit
                ? updateComment(this.editCommentId, payload)
                : createComment({
                    contentId: this.contentId,
                    parentCommentId: this.parentCommentId,
                    ...payload,
                });

            request.then((comment) => {
                this.busy = false;
                // Edit mode clears too, even though the caller discards this
                // component on `updated`: the editor's draft backup outlives the
                // unmount and would resurface the just-saved text as a draft later.
                this.clear();
                this.$emit(isEdit ? 'updated' : 'created', comment);
            }).catch((response) => {
                this.busy = false;
                // The API answers a validation failure with
                // `422 {"errors": {attribute: [messages]}}`, flattened onto the rejected
                // client.Response — see commentApi.js's extractFieldErrors().
                const fieldErrors = extractFieldErrors(response);
                if (response && response.status === 422 && fieldErrors) {
                    this.$refs.form.setErrors({ errors: fieldErrors });
                } else {
                    log.error(response, true);
                }
            });
        },
        /** Proxies to the richtext field so callers (reply toggle, section toggle) don't touch jQuery/legacy widgets. */
        focus() {
            if (this.$refs.richtext) {
                this.$refs.richtext.focus();
            }
        },
        /**
         * Empties the form: the editor (including its draft, see RichTextField's clear())
         * and the attachment list. Called both on a successful create/reply
         * submit (below) and by CommentEntry when a reply/edit form is discarded without
         * submitting.
         */
        clear() {
            if (this.$refs.richtext) {
                this.$refs.richtext.clear();
            }
            if (this.$refs.upload) {
                this.$refs.upload.clear();
            }
        },
        /**
         * Drop/paste anywhere in the comment box attaches files - the area the legacy upload
         * widget pointed its `dropZone`/`pasteZone` options at (`.comment_create`). The upload
         * field itself covers its own root; this
         * extends the zone to the whole box, and both funnel into the same `addFiles()`.
         */
        onZoneDrop(event) {
            const files = Array.from((event.dataTransfer && event.dataTransfer.files) || []);
            if (files.length && this.$refs.upload) {
                event.preventDefault();
                this.$refs.upload.addFiles(files);
            }
        },
        onZonePaste(event) {
            // Already uploaded into the text by the editor
            if (event.defaultPrevented) {
                return;
            }

            const files = Array.from((event.clipboardData && event.clipboardData.files) || []);
            if (files.length && this.$refs.upload) {
                this.$refs.upload.addFiles(files);
            }
        },
        onZoneDragOver(event) {
            // Without this the browser navigates to the dropped file instead of firing `drop`.
            event.preventDefault();
        },
    },
};
</script>
