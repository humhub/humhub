<template>
    <UiModal v-model:show="internalShow">
        <template #header="{ titleId }">
            <h5 class="modal-title" :id="titleId" v-html="headerHtml"></h5>
            <button type="button" class="btn-close" :aria-label="cancelLabel" @click="close"></button>
        </template>

        <!--
            The HumHubForm wrapper is here for its NAMING context, not for submitting
            (the footer's Confirm button drives the flow): it namespaces the fields to
            `AdminDeleteCommentForm[message]`/`[notify]` and the matching
            `admindeletecommentform-*` element ids — byte-identical to what the removed
            server-rendered modal emitted, so any theme CSS targeting them keeps
            working. Standalone fields would fall back to the bare global ids
            `message`/`notify` (see `form/fieldMixin.js`), which would collide with
            any same-named field elsewhere in the document and mis-bind their labels —
            this modal renders teleported into `document.body`.
        -->
        <HumHubForm v-if="adminMode" model-name="AdminDeleteCommentForm">
            <TextareaField
                attribute="message"
                :label="reasonLabel"
                :rows="3"
                :disabled="!notify"
                v-model="message"
            />
            <CheckboxField attribute="notify" :label="notifyLabel" v-model="notify" />
        </HumHubForm>
        <p v-else>{{ bodyLabel }}</p>

        <template #footer>
            <button type="button" class="btn btn-light" @click="close">{{ cancelLabel }}</button>
            <button
                type="button"
                class="btn btn-danger"
                :disabled="confirmDisabled"
                @click="confirm"
            >{{ confirmLabel }}</button>
        </template>
    </UiModal>
</template>

<script>
/**
 * The comment delete confirmation — a native `UiModal`, replacing BOTH legacy
 * flows: the plain `modal.confirm()` bridge dialog (own comment) and the
 * server-rendered admin-delete modal (`comment/comment/get-admin-delete-modal`
 * + `AdminDeleteModal` widget, removed with this component). The comment island
 * has no dependency on the legacy `#globalModal`/`#globalModalConfirm` bridge
 * anymore.
 *
 * Two modes via `adminMode` (the caller passes the adapted comment's
 * `canAdminDelete` — deleting someone ELSE's comment as a moderator):
 *  - plain: the classic "Do you really want to delete this comment?" confirm.
 *  - admin: reason textarea + "notify the author" checkbox — the fields the
 *    legacy `AdminDeleteCommentForm` carried, feeding the API's delete
 *    endpoint's `notify`/`message` moderation parameters instead of a
 *    server-side form model.
 *
 * Parity notes vs. the legacy admin modal (`adminDeleteModal.php`, removed):
 *  - `notify` defaults to CHECKED and the reason textarea is disabled while it
 *    is unchecked — the exact behavior the view's inline `<script>` (with CSP
 *    nonce) implemented; here it is a plain reactive binding.
 *  - The legacy flow silently SKIPPED the notification when the reason was
 *    empty (`AdminDeleteCommentForm` validation failed → comment still
 *    deleted, author never notified). This modal disables Confirm instead
 *    while notify is checked and the reason is empty — same server-side
 *    leniency, but the user can no longer think they notified when they
 *    didn't.
 *  - All strings reuse the existing `CommentModule.base` translations of the
 *    legacy dialogs; the `<strong>…</strong>` headers are translator-authored
 *    markup (same v-html trust boundary as `LikeButton.vue`'s modal title).
 *
 * State resets every time the modal (re)opens, so a canceled admin delete
 * never leaks its half-typed reason into the next one.
 *
 * Emits `confirm` with the API's moderation fields — `{notify: 1, message}`
 * when an admin delete should notify, `null` otherwise (plain delete, or an
 * admin delete with notify unchecked) — exactly what `commentApi.js`'s
 * `deleteComment(id, fields)` expects. Closing is the caller's job on
 * success/failure alike (`v-model:show`); this component closes itself only
 * on cancel.
 *
 * `UiModal`/`HumHubForm`/`TextareaField`/`CheckboxField` resolve through the
 * global Vue component registry (CoreVueAsset — a `CommentVueAsset`
 * dependency), like every other core component this island nests.
 *
 * @since 1.19
 */
import { i18n } from '@humhub/vue';

export default {
    props: {
        show: { type: Boolean, default: false },
        adminMode: { type: Boolean, default: false },
    },
    emits: ['update:show', 'confirm'],
    data() {
        return {
            notify: true,
            message: '',
        };
    },
    computed: {
        internalShow: {
            get() {
                return this.show;
            },
            set(value) {
                this.$emit('update:show', value);
            },
        },
        headerHtml() {
            // Both keys are the exact headers the legacy dialogs used (the admin one
            // came from the removed actionGetAdminDeleteModal response).
            return this.adminMode
                ? i18n.t('CommentModule.base', '<strong>Delete</strong> comment?')
                : i18n.t('CommentModule.base', '<strong>Confirm</strong> comment deleting');
        },
        bodyLabel() {
            return i18n.t('CommentModule.base', 'Do you really want to delete this comment?');
        },
        reasonLabel() {
            return i18n.t('CommentModule.base', 'Reason');
        },
        notifyLabel() {
            return i18n.t('CommentModule.base', 'Send a notification to author');
        },
        confirmLabel() {
            return this.adminMode
                ? i18n.t('CommentModule.base', 'Confirm')
                : i18n.t('CommentModule.base', 'Delete');
        },
        cancelLabel() {
            return i18n.t('CommentModule.base', 'Cancel');
        },
        confirmDisabled() {
            return this.adminMode && this.notify && this.message.trim() === '';
        },
    },
    watch: {
        show(value) {
            if (value) {
                this.notify = true;
                this.message = '';
            }
        },
    },
    methods: {
        close() {
            this.$emit('update:show', false);
        },
        confirm() {
            const fields = this.adminMode && this.notify
                ? { notify: 1, message: this.message.trim() }
                : null;
            this.$emit('confirm', fields);
        },
    },
};
</script>
