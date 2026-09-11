<template>
    <button type="submit" :disabled="isDisabled">
        <span v-if="showLoader" class="hh-loader text-center">
            <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
            <span role="status" class="visually-hidden">{{ loadingText }}</span>
        </span>
        <slot v-else />
    </button>
</template>

<script>
/**
 * A `type="submit"` button for the `HumHubForm` suite — see `HumHubForm.vue`'s own
 * docblock for the suite overview. Deliberately unopinionated about visual style:
 * its own template root carries no `class` at all, so a caller's own `class`
 * (`btn btn-primary`, `btn-sm`, an icon-only variant, ...) applies verbatim via
 * Vue's normal attribute fallthrough — see `docs/develop/ui-js-vuejs-forms.md` for
 * a worked example.
 *
 * ## Why `type="submit"` (Ctrl+S compatibility)
 *
 * The vendored richtext editor's save plugin (`humhub-prosemirror-richtext`'s
 * `src/editor/core/plugins/save/plugin.js`) reacts to Ctrl+S by doing exactly
 * `editor.$.closest('form').find('[type="submit"]').trigger('click')` — a
 * `type="button"` button is invisible to that lookup. Any `HumHubForm` field the
 * richtext editor could appear alongside therefore needs its submit trigger to
 * carry the real attribute, not just a `@click` handler — see `CommentForm.vue`'s
 * own "Ctrl+S bridge" docblock section for the reference case this fixed
 * originally.
 *
 * ## Busy/disabled + the loader idiom
 *
 * `disabled` (own prop) OR the injected form's `busy` (see `HumHubForm.vue`)
 * disables the button. While busy, the default slot is replaced by a spinner
 * reproducing `humhub.ui.loader.js`'s own `getTemplate()` markup byte-for-byte
 * (`.hh-loader` + `.spinner-border.spinner-border-sm` + a `role="status"`,
 * visually-hidden loading label) for visual consistency with every legacy
 * `data-ui-loader` button — WITHOUT reusing that module's click-triggered DOM
 * mechanism itself, which assumes it owns detecting "busy" via a click handler;
 * here the form/consumer already knows precisely when it is busy and reflects it
 * through `busy`/props reactively, so re-deriving it from a click is unnecessary.
 *
 * **`loader` prop (default `true`).** Set to `false` to disable the busy → spinner
 * swap entirely, keeping the slot content on screen unchanged and disabling ONLY
 * the `disabled` attribute while busy. `CommentForm.vue` does exactly this
 * (`:loader="false"`) — its button's content (send icon or label) must stay
 * byte-for-byte identical to the pre-`HumHubForm`-migration markup at all times,
 * busy or not (browser E2E and existing acceptance tests depend on it); the
 * busy → spinner swap this component ships for OTHER, non-legacy-parity consumers
 * would visibly change that button's content while a request is in flight.
 *
 * @since 1.20
 */
import { i18n } from '@humhub/vue';
import { FORM_CONTEXT_KEY } from './form/formContext.js';

export default {
    inject: {
        humhubForm: { from: FORM_CONTEXT_KEY, default: null },
    },
    props: {
        disabled: { type: Boolean, default: false },
        loader: { type: Boolean, default: true },
    },
    computed: {
        formBusy() {
            return this.humhubForm ? this.humhubForm.busy.value : false;
        },
        isDisabled() {
            return this.disabled || this.formBusy;
        },
        showLoader() {
            return this.loader && this.formBusy;
        },
        loadingText() {
            return i18n.t('base', 'Loading...');
        },
    },
};
</script>
