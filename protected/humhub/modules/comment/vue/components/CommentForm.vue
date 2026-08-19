<template>
    <LegacyFormWrapper ref="wrapper" :shell-html="shellHtml" />
    <button type="button" class="btn btn-accent btn-comment-submit btn-sm" :disabled="busy" @click="onSubmit">{{ sendLabel }}</button>
    <div v-if="hasErrors" class="invalid-feedback d-block">
        <div v-for="(message, index) in errorMessages" :key="index">{{ message }}</div>
    </div>
</template>

<script>
/**
 * Hosts a comment create/reply/edit form: LegacyFormWrapper renders the
 * server shell (richtext editor + upload widget), this component owns
 * intercepting its native submit and talking to the JSON API.
 *
 * Two modes, selected by whether `editCommentId` is set:
 *  - create (default): POSTs to `/comment/comment/create` with `contentId`
 *    (+ `parentCommentId` for a reply), clears the editor/upload widgets on
 *    success and emits `created` with the new comment JSON.
 *  - edit: POSTs to `/comment/comment/update?id=<editCommentId>` instead,
 *    does NOT clear the editor (there is nothing to clear back to - the
 *    caller discards this component on success/cancel) and emits `updated`
 *    with the updated comment JSON.
 *
 * `initialMessage` (edit mode only) is applied via the wrapper's `setValue()`
 * once THIS component's own `mounted()` runs. Vue mounts children before
 * parents, so by the time a fresh CommentForm instance's `mounted()` fires,
 * its own LegacyFormWrapper child - including that child's `v-additions`
 * directive, which boots the richtext widget - has already fully mounted.
 * The extra `$nextTick()` is the same defensive margin CommentEntry's own
 * `toggleReply()` already uses before touching a freshly-mounted child ref
 * (see the reply-open branch there): cheap, and correct whether the widget
 * boot the directive triggers happens synchronously (today) or ever becomes
 * async.
 *
 * Error shapes (see docs/superpowers/plans/2026-08-19-vuejs-comments.md, "API
 * contract notes"): a rejected `client.post()` call resolves a
 * `client.Response`-shaped object. Its constructor unconditionally merges a
 * JSON response body onto itself (`$.extend(this, this.response)`) BEFORE
 * `.setError()` runs its own (separately buggy, non-string-safe)
 * `JSON.parse(this.response)` - so the reliable, already-flattened place to
 * read a 422's field errors is the TOP-LEVEL `response.errors`, not
 * `response.error.errors`. A 403/404 rejects the same way with the Yii
 * framework error shape (`{name, message, status, ...}`) flattened the same
 * way - since that shape has no `errors` key, it falls through to the
 * `log.error(response, true)` branch, which is the "show via log status"
 * parity the plan calls for.
 *
 * `response.error.errors` is ALSO accepted defensively: the top-level flatten
 * only happens for a response `Content-Type` jQuery sniffs as JSON (see
 * above); a deployment where something ahead of `asJson()` degrades the 422
 * response to e.g. `text/html` (a misconfigured error-handler/proxy) would
 * skip the constructor's `$.extend()` merge and leave the body reachable
 * only via `.setError()`'s OWN (string-input-only) `JSON.parse()` instead -
 * i.e. under `.error`, not flattened to the top level.
 *
 * ## Submit button (P2-7 fix)
 *
 * The `__VUEFORM__` shell (comment/widgets/views/form.php, now removed - see
 * git history at the P2-6 removal commit) deliberately never had a native
 * SUBMIT trigger a Vue re-render would need to intercept declaratively: its
 * own button was `Button::accent()->icon('send')->cssClass('btn-comment-submit')
 * ->sm()->action('submit', $submitUrl)->submit()` - `type="submit"`, classes
 * `btn btn-accent btn-comment-submit btn-icon-only btn-sm` (icon-only per
 * `Button::run()`, since no `label` was set), with an `aria-label` of
 * `Yii::t('ContentModule.base', 'Submit')` — NOT a `CommentModule.base` key;
 * that category has no 'Send'/'Submit' string at all (verified against
 * protected/humhub/modules/{content,comment}/messages/de/base.php).
 *
 * A native `<button type="submit">` only works because it lives INSIDE the
 * `<form>` the legacy widget rendered server-side; the Vue-owned button
 * below is rendered as a SIBLING of LegacyFormWrapper's root (outside that
 * `<form>` element, since it isn't part of the `__VUEFORM__` shell string),
 * so it MUST be `type="button"` with an explicit `@click="onSubmit"` instead
 * of relying on native form submission - a `type="submit"` here would just
 * be an inert button with no form to submit.
 *
 * Rendered as a visible LABELED button (`{{ sendLabel }}`, reusing the exact
 * `ContentModule.base` / 'Submit' key found above) rather than reproducing
 * the legacy icon-only rendering: the 'send' icon glyph is rendered through
 * `Icon::get()` → a pluggable `IconProvider` (FontAwesome today, but
 * swappable) with no client-side equivalent available to this component -
 * hardcoding an icon font class here would silently break if the provider
 * ever changes. `btn-icon-only` is deliberately NOT included for the same
 * reason: it assumes no visible text, which this button has. The other
 * legacy classes (`btn`, `btn-accent`, `btn-comment-submit`, `btn-sm`) ARE
 * reproduced so existing theme CSS targeting `.btn-comment-submit` still
 * applies.
 *
 * The native `submit` listener (see `mounted()`) stays wired too — harmless,
 * and still catches a programmatic/synthetic `submit()` call on the form
 * (e.g. an autofill or a browser extension) even though nothing in this
 * shell can trigger one through user interaction anymore.
 */
import { client, i18n, log, url } from '@humhub/vue';
import LegacyFormWrapper from './LegacyFormWrapper.vue';

export default {
    components: { LegacyFormWrapper },
    props: {
        shellHtml: { type: String, required: true },
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
            errors: {},
        };
    },
    computed: {
        hasErrors() {
            return Object.keys(this.errors).length > 0;
        },
        errorMessages() {
            return Object.values(this.errors).flat();
        },
        // Same key the legacy submit button's aria-label used (see the
        // "Submit button" docblock section above) - NOT a CommentModule.base
        // key. CommentSection preloads 'ContentModule.base' alongside its
        // own category for exactly this.
        sendLabel() {
            return i18n.t('ContentModule.base', 'Submit');
        },
    },
    mounted() {
        this.formEl = this.$refs.wrapper.$el.querySelector('form');
        if (this.formEl) {
            this.formEl.addEventListener('submit', this.onSubmit);
        }
        if (this.initialMessage !== null) {
            this.$nextTick(() => {
                if (this.$refs.wrapper) {
                    this.$refs.wrapper.setValue(this.initialMessage);
                }
            });
        }
    },
    beforeUnmount() {
        if (this.formEl) {
            this.formEl.removeEventListener('submit', this.onSubmit);
        }
    },
    methods: {
        onSubmit(event) {
            // Called both as a native 'submit' listener (an Event is always
            // given) and directly from the rendered button's @click (Vue
            // passes the click MouseEvent there too, but tolerate a bare
            // call with none - e.g. a future programmatic caller).
            if (event) {
                event.preventDefault();
            }

            if (this.busy) {
                return;
            }

            const isEdit = this.editCommentId !== null;
            const endpoint = isEdit ? '/comment/comment/update' : '/comment/comment/create';
            const params = isEdit ? { id: this.editCommentId } : { contentId: this.contentId };
            if (!isEdit && this.parentCommentId !== null) {
                params.parentCommentId = this.parentCommentId;
            }

            this.busy = true;
            this.errors = {};

            client.post(url(endpoint, params), {
                data: {
                    message: this.$refs.wrapper.getValue(),
                    fileList: this.$refs.wrapper.getFileGuids(),
                },
            }).then((comment) => {
                this.busy = false;
                if (!isEdit) {
                    this.$refs.wrapper.clear();
                }
                this.$emit(isEdit ? 'updated' : 'created', comment);
            }).catch((response) => {
                this.busy = false;
                const errors = response && (response.errors || (response.error && response.error.errors));
                if (response && response.status === 422 && errors) {
                    this.errors = errors;
                } else {
                    log.error(response, true);
                }
            });
        },
        /** Proxies to the wrapper so callers (reply toggle, section toggle) don't touch jQuery/legacy widgets. */
        focus() {
            if (this.$refs.wrapper) {
                this.$refs.wrapper.focus();
            }
        },
    },
};
</script>
