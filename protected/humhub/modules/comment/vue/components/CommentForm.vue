<template>
    <HumHubForm ref="form" model-name="Comment" :busy="busy" @submit="onSubmit">
        <RichTextField ref="richtext" attribute="message" :shell-html="shellHtml" :instance-key="formInstanceKey" />
        <Teleport :to="teleportTarget" :disabled="!teleportTarget">
            <SubmitButton
                :loader="false"
                class="btn btn-accent btn-comment-submit btn-sm"
                :class="{ 'btn-icon-only': submitIconHtml }"
                :aria-label="sendLabel"
                @click="onSubmit"
            ><span v-if="submitIconHtml" v-html="submitIconHtml"></span><template v-else>{{ sendLabel }}</template></SubmitButton>
        </Teleport>
    </HumHubForm>
</template>

<script>
/**
 * Hosts a comment create/reply/edit form: built on the `HumHubForm` suite (see
 * `protected/humhub/vue/HumHubForm.vue`'s own docblock, and
 * `docs/develop/ui-js-vuejs-forms.md` for the full suite reference) — a
 * `RichTextField` (the suite's "legacy citizen", see its own docblock) hosts the
 * server shell (richtext editor + upload widget), this component owns intercepting
 * its submit and talking to the JSON API, and 422 field errors flow through
 * `HumHubForm`'s `setErrors()`/`clearErrors()` instead of a hand-rolled local
 * `errors` object.
 *
 * Two modes, selected by whether `editCommentId` is set:
 *  - create (default): POSTs to the `comment` endpoint with `contentId`
 *    (+ `parentCommentId` for a reply — see commentApi.js's createComment()),
 *    clears the editor/upload widgets on success and emits `created` with the
 *    new comment JSON.
 *  - edit: PUTs to the `comment/<editCommentId>` endpoint instead and
 *    emits `updated` with the updated comment JSON. The caller discards this
 *    component on success/cancel, but it still clear()s first — the richtext
 *    draft backup (sessionStorage) and the acknowledgeForm baseline both
 *    outlive the unmount (see the success handler's own comment in
 *    onSubmit(), and cancelEdit() in CommentEntry for the discard twin).
 *
 * `initialMessage` (edit mode only) is applied via `RichTextField.setValue()`
 * once THIS component's own `mounted()` runs. Vue mounts children before
 * parents, so by the time a fresh CommentForm instance's `mounted()` fires,
 * its own RichTextField child - including that child's inner `LegacyFormWrapper`
 * and the `v-additions` directive that boots the richtext widget - has already
 * fully mounted. The extra `$nextTick()` is the same defensive margin CommentEntry's
 * own `toggleReply()` already uses before touching a freshly-mounted child ref
 * (see the reply-open branch there): cheap, and correct whether the widget
 * boot the directive triggers happens synchronously (today) or ever becomes
 * async.
 *
 * ## Built on `HumHubForm` — error handling
 *
 * `onSubmit()` calls `this.$refs.form.clearErrors()` before every request and, on
 * a 422, `this.$refs.form.setErrors(response)` — `setErrors()` itself unwraps
 * whichever of the three envelope shapes below `response` turns out to be (see
 * `HumHubForm.vue`'s own docblock), so this component only has to decide WHETHER
 * a response carries field errors at all, not how to unwrap them. `RichTextField`
 * (`attribute="message"`) renders that attribute's messages itself once
 * `HumHubForm`'s error map updates — see its own docblock's "API" section.
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
 * `<form>` the legacy widget rendered server-side; the Vue-owned button was
 * therefore made `type="button"` with an explicit `@click="onSubmit"`
 * instead of relying on native form submission - `type="submit"` looked
 * pointless while the button was a plain sibling of `LegacyFormWrapper`,
 * outside the `<form>` entirely.
 *
 * **Update (Ctrl+S bridge fix):** that premise stopped holding the moment
 * "Submit button placement" below started Teleporting the button INTO
 * `.richtext-create-buttons` - which is itself INSIDE the shell's `<form>`
 * (see `commentFormShell.php`). The button is now rendered via `SubmitButton`
 * (`type="submit"` built in — see its own docblock's "Ctrl+S compatibility"
 * section) with `@click="onSubmit"` still attached too (see the note at the
 * bottom of this section for why both stay), and `:loader="false"` (see
 * `SubmitButton.vue`'s own docblock) so its content stays byte-identical to
 * the pre-`HumHubForm` markup whether busy or not - only the `disabled`
 * attribute (driven by `HumHubForm`'s `:busy="busy"`, see the class docblock's
 * "Built on HumHubForm" section) changes while a request is in flight.
 *
 * ## Submit button placement (Teleport into the shell's button group)
 *
 * Browser-verified visual parity gap: legacy (`comment/widgets/views/form.php`,
 * now `commentFormShell.php`) always rendered its submit button INSIDE
 * `.richtext-create-buttons`, next to the upload dropdown, i.e. bottom-right
 * of the input. Simply rendering the button as a plain sibling of the richtext
 * field (as an earlier revision of this component did) lands it bottom-LEFT
 * instead - wrong position, and no shared button group with the upload dropdown.
 *
 * `mounted()` resolves `.richtext-create-buttons` inside the just-mounted
 * shell (`this.$refs.richtext.getShellElement().querySelector(...)` - see
 * `RichTextField.vue`'s own "API" docblock section) into `teleportTarget`. A
 * `<Teleport :to="teleportTarget" :disabled="!teleportTarget">` then moves
 * the button there when found - Vue's `disabled` prop renders the teleport's
 * children IN PLACE (right where the `<Teleport>` tag sits, i.e. the old
 * sibling-of-the-richtext-field position) without even evaluating `to`, so a
 * shell that doesn't carry the container (a minimal synthetic test shell, or
 * a hypothetical future caller) still gets a working, visible button instead
 * of a Teleport mount warning. The real, `commentFormShell.php`-derived shell
 * always carries the container (see that file's `<div
 * class="richtext-create-buttons">`, holding the upload dropdown - the
 * submit button stays exclusively Vue-owned, never rendered there
 * server-side), so this fallback only matters for tests that don't bother
 * wiring up the full shell markup.
 *
 * `submitIconHtml` (from `Comments::widget()` → `CommentSection` → down
 * through `CommentList`/`CommentEntry` — see their own docblocks) is the
 * server-rendered `Icon::get('send')->asString()` markup: exact icon parity
 * without hardcoding an icon-font class client-side, since the icon
 * provider (FontAwesome by default) is a pluggable `IconProvider` - this
 * component only ever `v-html`s whatever HTML the server decided to render
 * for that icon name. When present, `btn-icon-only` is restored (matching
 * legacy exactly) and the button's accessible name comes from `aria-label`
 * alone. When absent (tests that don't bother wiring the prop, or a
 * hypothetical future caller that legitimately has no icon to give),
 * `sendLabel` renders as VISIBLE text instead - a Submit button must never
 * end up with neither a label nor an icon.
 *
 * The native `submit` listener (see `mounted()`) stays wired too - it still
 * catches a programmatic/synthetic `submit()` call on the shell's OWN
 * `<form>` (e.g. an autofill or a browser extension), and it's the reason
 * the Ctrl+S chain above needed no separate wiring: the button's own
 * `@click="onSubmit"` calls `event.preventDefault()` on the CLICK first,
 * which cancels the button's pending form-submission activation before it
 * can dispatch a `submit` event - so a real user click OR the plugin's
 * `.trigger('click')` both still resolve to exactly one `onSubmit()` call,
 * never two. `@click` is kept explicit (rather than relying solely on this
 * listener) because jsdom-based tests drive the button via a plain synthetic
 * `dispatchEvent` that never runs a `type="submit"` button's native
 * activation behavior at all - see commentMutations.test.js's "keyboard
 * submit (Ctrl+S bridge)" describe block for the real-jQuery-`.trigger()`
 * regression test.
 *
 * Note this listener is attached to the SHELL's own inner `<form>` (rendered
 * by `LegacyFormWrapper` inside `RichTextField`), not to `HumHubForm`'s own
 * outer `<form>` — see `HumHubForm.vue`'s own docblock, "A note on
 * legacy-citizen fields and nested `<form>`", for why the two are different
 * elements here. That does NOT make `HumHubForm`'s own `submit` emission
 * (wired via `@submit="onSubmit"` above, same handler) dormant, though: a
 * native `submit` event dispatched on the inner form BUBBLES, like any DOM
 * event, through every ancestor up to and including `HumHubForm`'s own outer
 * `<form>` (the two are nested, not siblings — see above) — whose own
 * `@submit.prevent` listener re-emits `submit` and invokes this exact same
 * `onSubmit` a SECOND time. `onSubmit`'s own `if (this.busy) return;` guard
 * (see below) is the only thing that keeps that redundant second call from
 * double-posting; there is no dormancy on `HumHubForm`'s side to rely on.
 *
 * ## Unsaved-changes guard (P2-7 fix)
 *
 * Browser-verified: submitting a comment (or cancelling an edit/reply) could
 * leave a STALE "Unsaved changes will be lost" confirm armed for a LATER,
 * unrelated pjax navigation. Root cause, in `humhub.client.js`: the shell's
 * `<form>` carries `data-ui-addition="acknowledgeForm"` (from
 * `ActiveForm::begin(['acknowledge' => true])`, see
 * `commentFormShell.php`), which snapshots the form's serialized state once
 * at boot and arms GLOBAL `beforeunload`/`pjax:beforeSend` listeners
 * (bound to `window`/`document`, NOT scoped to this specific form) that
 * compare the CURRENT state against that snapshot forever. The only way
 * `onBeforeLoad()` ever clears that baseline (`resetChanges()`, itself a
 * closure-private function with no public API) is a native `submit` event
 * on the form OR a click on a `[type=submit]` element INSIDE it - neither
 * ever happens here: submission is JSON via `client.post()`, and this
 * component's own button is `type="button"` outside the `<form>` (see
 * above). Left unset, a form whose content still differs from its boot-time
 * snapshot for ANY reason (most reliably: a reply/edit form that gets
 * DISCARDED - closed/cancelled without submitting, so `clear()` never runs
 * at all - but its global listeners, closing over that now-detached `$form`
 * node, stay armed) trips the guard on the next pjax navigation regardless
 * of who's mounted at that point.
 *
 * Fix: `LegacyFormWrapper.clear()` (proxied via `RichTextField.clear()`, see
 * its own docblock) also resets that baseline (`resetAcknowledge()` - see
 * `LegacyFormWrapper`'s own docblock, `$form.data('state', null)` via the
 * PUBLIC jQuery `.data()` store `onBeforeLoad()`/`formStateChanged()` both
 * already read/write, not the private closure). `clear()` already ran on
 * every successful create/reply submit; this component's own `clear()`
 * passthrough additionally lets CommentEntry call it when a reply/edit form
 * is discarded (see its `cancelEdit()`/`toggleReply()`), covering the case
 * that never called `clear()` before.
 */
import { i18n, log } from '@humhub/vue';
import { createComment, extractFieldErrors, updateComment } from './commentApi.js';

// HumHubForm/RichTextField/SubmitButton (like LegacyFormWrapper before them) are NOT
// imported here — they live at protected/humhub/vue/ (see docs/develop/ui-js-vuejs.md)
// and resolve through the global Vue component registry (every registered component is
// available in every island, see humhub.vue.js's register()). CoreVueAsset must register
// before this island's own script runs — enforced via CommentVueAsset::$depends, not by
// import order here.
export default {
    props: {
        shellHtml: { type: String, required: true },
        contentId: { type: Number, required: true },
        parentCommentId: { type: Number, default: null },
        // When set, this form edits an existing comment instead of creating one.
        editCommentId: { type: Number, default: null },
        // Edit mode only: the raw markdown to prefill the editor with once booted.
        initialMessage: { type: String, default: null },
        // Server-rendered submit-icon HTML (see "Submit button" docblock section above).
        submitIconHtml: { type: String, default: null },
    },
    emits: ['created', 'updated'],
    data() {
        return {
            busy: false,
            // Resolved once in mounted() - see the "Submit button placement" docblock
            // section above. `null` until then/if the shell has no button-group
            // container, which Teleport's `disabled` prop treats as "render in place".
            teleportTarget: null,
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
        // Deterministic identity for the shell's DOM ids, threaded down to
        // LegacyFormWrapper (see ITS "Unique-id contract" docblock section for
        // the full contract this scheme satisfies): unique among every comment
        // form that can be mounted at once — the main create form (`c<contentId>`),
        // one reply form per commented-on entry (`-r<parentCommentId>`), one edit
        // form per comment (`-e<editCommentId>`) — AND stable across page loads
        // for the same logical form. Stability is what keys the richtext
        // editor's sessionStorage draft backup correctly: the wrapper's own
        // per-page-load counter fallback produced `vueform-1` on EVERY page,
        // merging drafts of unrelated contents across navigations
        // (browser-verified) and arming phantom unsaved-changes confirms.
        formInstanceKey() {
            if (this.editCommentId !== null) {
                return 'c' + this.contentId + '-e' + this.editCommentId;
            }
            if (this.parentCommentId !== null) {
                return 'c' + this.contentId + '-r' + this.parentCommentId;
            }
            return 'c' + this.contentId;
        },
    },
    mounted() {
        const shellEl = this.$refs.richtext.getShellElement();
        this.formEl = shellEl.querySelector('form');
        if (this.formEl) {
            this.formEl.addEventListener('submit', this.onSubmit);
        }
        // See the "Submit button placement" docblock section above - absent on a
        // shell without the container, in which case the button just renders where
        // the <Teleport> tag sits (Teleport's own `disabled` fallback).
        this.teleportTarget = shellEl.querySelector('.richtext-create-buttons');
        if (this.initialMessage !== null) {
            this.$nextTick(() => {
                if (this.$refs.richtext) {
                    this.$refs.richtext.setValue(this.initialMessage);
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
            // Called as a native 'submit' listener on the shell's own inner <form>
            // (an Event is always given), directly from the rendered button's
            // @click (Vue passes the click MouseEvent there too), and via
            // HumHubForm's own dormant `submit` emission (see the class docblock's
            // "Submit button placement" section) - tolerate a bare call with none
            // either way (e.g. a future programmatic caller).
            if (event) {
                event.preventDefault();
            }

            // Load-bearing, not just a courtesy re-entrancy guard: a native submit on the
            // shell's INNER (legacy) <form> — e.g. the Ctrl+S bridge, see this class's own
            // "Submit button placement" docblock section — bubbles up to HumHubForm's outer
            // <form>, whose own `@submit.prevent="onSubmit"` (see the template above) fires
            // this SAME handler a second time. Nothing upstream de-duplicates that second
            // call; this synchronous check is the only thing standing between one Ctrl+S and
            // two POSTs.
            if (this.busy) {
                return;
            }

            const isEdit = this.editCommentId !== null;
            const payload = {
                message: this.$refs.richtext.getValue(),
                fileList: this.$refs.richtext.getFileGuids(),
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
                // component on `updated`: the discard only removes DOM — the
                // richtext editor's sessionStorage draft backup and the
                // acknowledgeForm baseline both OUTLIVE the unmount, and left
                // unset they resurface the just-saved text as a phantom draft/
                // "unsaved changes" confirm later (browser-verified; the exact
                // reason cancelEdit() already clear()s on discard — see the
                // class docblock's "Unsaved-changes guard" section).
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
         * Proxies to RichTextField's clear() - blanks the editor/uploads AND resets the
         * unsaved-changes guard baseline (see this component's own "Unsaved-changes guard"
         * docblock section). Called both on a successful create/reply submit (below) and by
         * CommentEntry when a reply/edit form is discarded without submitting.
         */
        clear() {
            if (this.$refs.richtext) {
                this.$refs.richtext.clear();
            }
        },
    },
};
</script>
