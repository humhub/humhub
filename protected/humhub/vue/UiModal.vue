<template>
    <Teleport to="body">
        <div
            v-if="show"
            ref="dialog"
            class="modal fade"
            :class="{ show: visible }"
            style="display: block"
            tabindex="-1"
            role="dialog"
            aria-modal="true"
            :aria-labelledby="titleId"
            @mousedown="onBackdropMousedown"
            @click.self="onBackdropClick"
        >
            <div class="modal-dialog" :class="sizeClass">
                <div class="modal-content">
                    <div class="modal-header">
                        <slot name="header" :title-id="titleId">
                            <h5 class="modal-title" :id="titleId">{{ title }}</h5>
                            <button type="button" class="btn-close" aria-label="Close" @click="requestClose"></button>
                        </slot>
                    </div>
                    <div class="modal-body">
                        <slot />
                    </div>
                    <div v-if="$slots.footer" class="modal-footer">
                        <slot name="footer" />
                    </div>
                </div>
            </div>
        </div>
        <div v-if="show" class="modal-backdrop fade" :class="{ show: visible }"></div>
    </Teleport>
</template>

<script>
// Per-instance id suffix for the title/aria-labelledby wiring below - a plain
// module-scope counter is enough (no need for crypto-grade uniqueness), and
// keeps every UiModal instance on the page distinguishable without a prop.
let uidSeq = 0;

/**
 * A native, core-provided modal (`<ui-modal>`) - the first "real" Vue citizen in the
 * modal space, reusing the exact same Bootstrap 5 markup/CSS classes the LEGACY
 * `humhub.ui.modal.js` bridge renders (`.modal.fade`, `.modal-dialog`, `.modal-content`,
 * `.modal-header`/`.modal-title`/`.btn-close`, `.modal-body`, `.modal-footer`,
 * `.modal-backdrop`) so it looks and themes identically, without depending on
 * `bootstrap.Modal` itself (open/close/backdrop/keyboard/focus/scroll-lock are all
 * owned here, in Vue).
 *
 * ## Visibility is fully controlled (`v-model:show`)
 *
 * There is no internal open/closed state machine beyond the `show` prop itself - the
 * component never closes itself. `backdropClose`/`keyboard` (and a body's own close
 * button) only ever `$emit('update:show', false)`; it is the `v-model:show` binding
 * that actually removes the dialog from the DOM. This mirrors every other `v-model`
 * form component in this codebase (see `CheckboxField.vue`) rather than the legacy
 * `Modal` class's own imperative `show()`/`close()` API.
 *
 * ## Slots
 *
 * - default: the modal body.
 * - `header`: replaces the ENTIRE header row (title text + close button) - the
 *   fallback renders the standard `<h5 class="modal-title">{{ title }}</h5>` plus a
 *   standard `.btn-close` button, exactly like `Modal.template.header` in the legacy
 *   bridge. A custom header is handed the same `titleId` the fallback would have used
 *   (`:title-id` slot prop) so it can wire its own heading to `aria-labelledby` (bound
 *   unconditionally to `titleId` on the dialog root) - a custom header that renders no
 *   element with that id just leaves `aria-labelledby` pointing at nothing, which every
 *   major screen reader tolerates as "no accessible name from this attribute" rather
 *   than an error.
 * - `footer`: omitted entirely (no `.modal-footer` element at all) when not provided -
 *   unlike the legacy bridge, which always renders an (empty) `.modal-footer` div.
 *
 * ## `size`
 *
 * `'small'` -> `modal-sm`, `'normal'` (default) -> no extra class (Bootstrap's own
 * default dialog width), `'large'` -> `modal-lg`. Deliberately NOT the legacy PHP
 * `humhub\widgets\modal\Modal::initOptions()` string aliasing (`'small'` there
 * quietly meant "default size", a backward-compat shim for pre-BS5 callers) - a new
 * component gets the plain, unsurprising mapping straight onto Bootstrap's own size
 * classes instead.
 *
 * ## Backdrop / keyboard / focus / scroll-lock
 *
 * - `backdropClose` (default `true`): clicking the dimmed area outside `.modal-dialog`
 *   requests a close. `@click.self` alone is not enough: a text-selection drag that
 *   starts inside `.modal-dialog` and is released outside it still fires a `click`
 *   whose target is the `.modal` root (the browser resolves click target from the
 *   `mouseup` point, not the `mousedown` point), which `.self` cannot tell apart from
 *   a genuine backdrop click. Mirrors Bootstrap's own guard for the exact same problem
 *   (`vendor/twbs/bootstrap/js/src/modal.js`'s `_addEventListeners()`): `@mousedown` on
 *   the root records whether THAT event's target was the root itself
 *   (`onBackdropMousedown`), and `onBackdropClick` only requests a close when the
 *   `click`'s target is ALSO the root AND that recorded flag is still set - i.e. both
 *   the press and the release targeted the backdrop, not just the release. The flag is
 *   consumed (reset) on every `mousedown`, so a stale `true` from an earlier backdrop
 *   click can never leak into a later, unrelated click.
 * - `keyboard` (default `true`): Escape requests a close. The listener is added to
 *   `document` only while `show` is true and removed the moment it becomes false (or
 *   the component unmounts) - never a lingering global listener.
 * - Focus: the dialog root (`tabindex="-1"`) is focused once it finishes opening;
 *   whatever had focus before opening is restored once it finishes closing.
 * - Scroll-lock: adds/removes Bootstrap's own `body.modal-open` class (the exact class
 *   `_modal.scss` already themes) while open. Stacking with the legacy `#globalModal`
 *   (or another `UiModal`) at the same time is out of scope - this component does not
 *   attempt the legacy bridge's multi-modal z-index reshuffling
 *   (`_setModalsAndBackdropsOrder()`), and two simultaneously-open modals fighting over
 *   `body.modal-open` add/remove is an accepted, undocumented-behavior edge case.
 *
 * ## Fade transition
 *
 * `visible` (distinct from the `show` prop) drives the `.show` class that triggers
 * Bootstrap's own CSS fade transition, and is flipped a tick after the dialog/backdrop
 * are actually inserted (open) or before they are removed (close) - the same
 * insert-then-animate ordering `bootstrap.Modal` itself relies on, reproduced here
 * with `nextTick()` instead of a manual reflow + class toggle.
 *
 * @since 1.19
 */
export default {
    name: 'UiModal',
    props: {
        show: { type: Boolean, default: false },
        title: { type: String, default: null },
        size: {
            type: String,
            default: 'normal',
            validator: (value) => ['small', 'normal', 'large'].includes(value),
        },
        backdropClose: { type: Boolean, default: true },
        keyboard: { type: Boolean, default: true },
    },
    emits: ['update:show', 'opened', 'closed'],
    data() {
        return {
            visible: false,
            titleId: `ui-modal-title-${++uidSeq}`,
            previouslyFocused: null,
            // Set by `onBackdropMousedown` on every mousedown targeting the `.modal` root,
            // cleared on any mousedown that doesn't - see the "Backdrop" docblock section
            // above and Bootstrap's own `_addEventListeners()` for the mechanism this mirrors.
            mousedownOnBackdrop: false,
        };
    },
    computed: {
        sizeClass() {
            return {
                'modal-sm': this.size === 'small',
                'modal-lg': this.size === 'large',
            };
        },
    },
    watch: {
        show(isOpen) {
            if (isOpen) {
                this.handleOpen();
            } else {
                this.handleClose();
            }
        },
    },
    mounted() {
        // Covers a modal mounted already-open (`:show="true"` from the start) - the
        // `show` watcher above only fires on a subsequent CHANGE, not on the initial
        // value, so the open-side effects (focus, scroll-lock, ESC listener) need this
        // explicit catch-up call.
        if (this.show) {
            this.handleOpen();
        }
    },
    beforeUnmount() {
        // Safety net: a modal destroyed while still open (e.g. its host island itself
        // unmounts) must not leak the scroll-lock class or the document-level ESC
        // listener.
        document.removeEventListener('keydown', this.onKeydown);
        if (this.show) {
            document.body.classList.remove('modal-open');
        }
    },
    methods: {
        handleOpen() {
            this.previouslyFocused = document.activeElement;
            document.body.classList.add('modal-open');
            document.addEventListener('keydown', this.onKeydown);

            this.$nextTick(() => {
                this.visible = true;
                this.$nextTick(() => {
                    if (this.$refs.dialog) {
                        this.$refs.dialog.focus();
                    }
                    this.$emit('opened');
                });
            });
        },
        handleClose() {
            this.visible = false;
            document.body.classList.remove('modal-open');
            document.removeEventListener('keydown', this.onKeydown);

            if (this.previouslyFocused && typeof this.previouslyFocused.focus === 'function') {
                this.previouslyFocused.focus();
            }
            this.previouslyFocused = null;

            this.$emit('closed');
        },
        onKeydown(event) {
            if (event.key === 'Escape' && this.keyboard) {
                this.requestClose();
            }
        },
        onBackdropMousedown(event) {
            // event.currentTarget is always the `.modal` root itself (that's where this
            // listener is bound); event.target is whichever descendant (or the root
            // itself) the press actually landed on.
            this.mousedownOnBackdrop = event.target === event.currentTarget;
        },
        onBackdropClick() {
            // `@click.self` already guarantees the CLICK's own target is the root; this
            // additionally requires the preceding MOUSEDOWN to have targeted the root too
            // - see the "Backdrop" docblock section above for why both are needed (a
            // text-selection drag started inside `.modal-dialog` and released outside it
            // otherwise fires a self-targeted click here without ever being a backdrop
            // click). Consume the flag either way so a later, unrelated click can never
            // reuse a stale `true`.
            const mousedownWasOnBackdrop = this.mousedownOnBackdrop;
            this.mousedownOnBackdrop = false;

            if (this.backdropClose && mousedownWasOnBackdrop) {
                this.requestClose();
            }
        },
        requestClose() {
            this.$emit('update:show', false);
        },
    },
};
</script>
