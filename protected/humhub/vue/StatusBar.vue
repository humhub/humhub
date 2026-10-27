<template>
    <div v-if="entry" class="toast-container position-fixed bottom-0 start-50 translate-middle-x p-3">
        <div
            ref="toast"
            class="toast fade show"
            :class="[toneClass, { showing: !visible }]"
            :role="isError ? 'alert' : 'status'"
            :aria-live="isError ? 'assertive' : 'polite'"
            aria-atomic="true"
            @mouseenter="interact('hovered', true)"
            @mouseleave="interact('hovered', false)"
            @focusin="interact('focused', true)"
            @focusout="onFocusOut"
        >
            <div class="toast-body">
                <i class="toast-icon" :class="iconClass" aria-hidden="true"></i>
                <span class="toast-message" :class="{ 'is-toggle': hasDetails }" @click="toggleDetails">{{ entry.message }}</span>
                <button
                    v-if="hasDetails"
                    type="button"
                    class="btn btn-ghost btn-icon-only btn-sm toast-details-toggle"
                    :aria-expanded="detailsOpen ? 'true' : 'false'"
                    :aria-label="detailsLabel"
                    :title="detailsLabel"
                    @click="toggleDetails"
                ><i class="ti" :class="detailsOpen ? 'ti-chevron-down' : 'ti-chevron-up'" aria-hidden="true"></i></button>
                <button type="button" class="btn-close toast-close" :aria-label="closeLabel" @click="close"></button>
            </div>
            <div v-if="detailsOpen" class="toast-details">
                <pre>{{ detailsText }}</pre>
            </div>
        </div>
    </div>
</template>

<script>
/**
 * The platform's user-feedback bar ("status bar") — the toast that rises at the
 * bottom of the window for flash messages, AJAX errors and the like.
 *
 * It is an infrastructure island: it gets nothing but its button labels as
 * props, and no caller ever imports it. It is mounted once per page by `humhub\widgets\StatusBar`
 * (a `LayoutAddons` widget, so it exists on every full page) and driven purely
 * through the bridge — `humhub.vue.js`'s `status()`/`setStatusHandler()` pair,
 * see their docblock there for why messages need a queue in front of this
 * component.
 *
 * ## Who sends messages
 *
 * Nothing in Vue-land, so far. The callers are legacy and stay legacy:
 *
 *  - `humhub.ui.status`'s exported `success/info/warn/error` — used by 18
 *    external modules — plus its `humhub:modules:log:setStatus` listener, which
 *    is how every `humhub.log.*(msg, details, true)` call surfaces. That module
 *    is now a thin façade in front of the bridge.
 *  - the inline `humhub.modules.ui.status.<type>(…)` snippet
 *    `humhub\components\View::endBody()` registers for a session flash message
 *    (`$this->view->success(...)`).
 *
 * That is also why the messages need no i18n here: every message it shows
 * was produced by its caller.
 *
 * ## Behaviour of the jQuery bar it replaces, look of a toast
 *
 * The behaviour is that of `humhub.ui.status.js`'s `StatusBar` class (owner
 * decision — a toast stack can be added inside this component later without
 * touching a single caller):
 *
 *  - one message at a time; a new one takes the current one out first, then
 *    appears (the legacy `hide(() => setContent().show())` chain).
 *  - the same auto-close timings (`AUTOCLOSE_*`), including the legacy quirk
 *    that a falsy `closeAfter` falls back to the level default rather than
 *    meaning "stay" — only `error` stays by default.
 *  - a level icon, the message, a close button and — for a message with
 *    details — a chevron toggle plus a `.toast-details > pre` block. Clicking
 *    the message text toggles the details too, as it did before.
 *
 * It is Bootstrap's toast (`_toast.scss`, Bootstrap's `$toast-*` variables): a
 * `.toast-container` fixed at the bottom centre holds one `.toast` with the
 * tone class `toast-<tone>` (`info`, `success`, `warning`, `danger`), icon,
 * message and controls in its `.toast-body` and the details below it. Errors
 * are announced assertively (`role="alert"`), the rest politely. It fades in
 * and out the way Bootstrap's Toast JS does it: `.fade.show` plus `.showing`
 * while hidden, the element kept around for the duration of the exit. Like
 * Bootstrap's `autohide`, the timer pauses while the pointer is on the toast or
 * the focus in it, and starts over once both have left.
 *
 * The labels of the close and details buttons come from
 * `humhub\widgets\StatusBar` (props `closeLabel`, `detailsLabel`).
 *
 * One intentional difference: the message renders as TEXT (`{{ }}`), not HTML.
 * The server path was HTML-encoded anyway (and no longer needs
 * `View::endBody()`'s `&quot;`-stripping hack), and no core caller passes
 * markup — see `docs/develop/module-migrate-1.20.md`.
 *
 * ## Details normalisation
 *
 * `details` reaches this component already flattened to a string by
 * `humhub.ui.status` for the cases only legacy code can recognise (a
 * `client.Response`, a jQuery error envelope). Anything else that still arrives
 * as an object is handled here so a Vue-side caller cannot end up with
 * `[object Object]` in the trace block.
 */

// Bootstrap's `.fade` transition ($transition-fade, 0.15s). Kept in JS as well
// because the component owns the swap/removal timing (an element must outlive
// its exit) - the two values have to stay in sync.
const TRANSITION_MS = 150;

// Legacy AUTOCLOSE_* values; 0 means "never auto-close" (error only).
const AUTOCLOSE = {
    info: 6000,
    success: 2000,
    warn: 10000,
    error: 0,
};

// Tabler names, the same four `humhub.ui.status.js` rendered before it forwarded to this island.
const ICONS = {
    info: 'ti ti-info-circle',
    success: 'ti ti-circle-check',
    warn: 'ti ti-alert-triangle',
    error: 'ti ti-alert-circle',
};

// The toast's tone class per level - what colours its border, outline and icon (see _toast.scss).
const TONES = {
    info: 'toast-info',
    success: 'toast-success',
    warn: 'toast-warning',
    error: 'toast-danger',
};

/**
 * Mirrors `getErrorMessage()` in humhub.ui.status.js for the value shapes that
 * can still reach a Vue component (the legacy façade resolves the rest). No
 * HTML escaping here - unlike the jQuery bar, this component renders the result
 * through text interpolation.
 */
const normalizeDetails = (details) => {
    if (details === undefined || details === null || details === '') {
        return null;
    }

    if (typeof details === 'string') {
        return details;
    }

    if (details instanceof Error) {
        // `stack` already opens with the error's own toString() in every engine
        // HumHub supports - only prepend it where one does not.
        const text = details.toString();
        if (!details.stack) {
            return text;
        }

        return details.stack.indexOf(text) === 0 ? details.stack : text + '\n' + details.stack;
    }

    try {
        return JSON.stringify(details, null, 4);
    } catch (e) {
        return String(details);
    }
};

import { setStatusHandler } from '@humhub/vue';

export default {
    props: {
        closeLabel: { type: String, default: 'Close' },
        detailsLabel: { type: String, default: 'Show more' },
    },
    data() {
        return {
            entry: null,
            visible: false,
            detailsOpen: false,
            hovered: false,
            focused: false,
        };
    },
    computed: {
        iconClass() {
            return ICONS[this.entry.level] || ICONS.info;
        },
        toneClass() {
            return TONES[this.entry.level] || TONES.info;
        },
        isError() {
            return this.entry.level === 'error';
        },
        detailsText() {
            return this.entry ? this.entry.details : null;
        },
        hasDetails() {
            return !!this.detailsText;
        },
    },
    mounted() {
        // Registering also drains whatever was queued before this island mounted
        // (a flash message from the page that is loading right now, typically).
        setStatusHandler(this.handle);
    },
    unmounted() {
        setStatusHandler(null);
        this.clearTimers();
    },
    methods: {
        /** Bridge handler - see humhub.vue.js `status()`. */
        handle(message) {
            const entry = {
                level: AUTOCLOSE[message.level] !== undefined ? message.level : 'info',
                message: message.message,
                details: normalizeDetails(message.details),
                closeAfter: message.closeAfter,
            };

            if (this.entry) {
                // Fade the current message out first, then show the new one -
                // the legacy hide-then-show chain.
                this.startHide(() => this.present(entry));
            } else {
                this.present(entry);
            }
        },
        present(entry) {
            this.clearTimers();
            this.entry = entry;
            this.detailsOpen = false;
            this.visible = false;
            this.hovered = false;
            this.focused = false;

            // The fade-in needs the hidden state to be laid out BEFORE the class that
            // transitions out of it is applied - otherwise both land in the same frame and the
            // browser jumps straight to the end state (no animation at all). `$nextTick` alone
            // only guarantees the element exists; reading a layout property forces the browser
            // to compute its style, which is exactly what Vue's own <Transition> does
            // (`forceReflow()`) for the same reason.
            this.$nextTick(() => {
                if (this.$el && typeof this.$el.getBoundingClientRect === 'function') {
                    void this.$el.getBoundingClientRect().height;
                }

                this.visible = true;
            });

            // Legacy started the timer once the slide-in had finished; here: the fade-in.
            this.scheduleClose(TRANSITION_MS);
        },
        scheduleClose(offset = 0) {
            this.clearCloseTimer();
            const closeAfter = this.autoCloseDelay(this.entry);
            if (closeAfter > 0) {
                this.closeTimer = setTimeout(() => this.startHide(), offset + closeAfter);
            }
        },
        /** Pointer or focus on the toast pauses the auto-close; it starts over once both left. */
        interact(kind, on) {
            this[kind] = on;
            if (!this.entry || !this.visible) {
                return;
            }
            if (on) {
                this.clearCloseTimer();
            } else if (!this.hovered && !this.focused) {
                this.scheduleClose();
            }
        },
        onFocusOut(event) {
            if (this.$refs.toast && this.$refs.toast.contains(event.relatedTarget)) {
                return;
            }
            this.interact('focused', false);
        },
        /**
         * `closeAfter || default` - the legacy expression, quirk included: for
         * info/success/warn a 0 or undefined value means "use the default", and
         * only `error` (whose default is 0) stays until dismissed.
         */
        autoCloseDelay(entry) {
            return entry.closeAfter || AUTOCLOSE[entry.level] || 0;
        },
        startHide(after) {
            this.clearTimers();
            this.visible = false;
            this.hideTimer = setTimeout(() => {
                this.entry = null;
                this.detailsOpen = false;
                if (after) {
                    after();
                }
            }, TRANSITION_MS);
        },
        close() {
            this.startHide();
        },
        toggleDetails() {
            if (this.hasDetails) {
                this.detailsOpen = !this.detailsOpen;
            }
        },
        clearCloseTimer() {
            if (this.closeTimer) {
                clearTimeout(this.closeTimer);
                this.closeTimer = null;
            }
        },
        clearTimers() {
            this.clearCloseTimer();
            if (this.hideTimer) {
                clearTimeout(this.hideTimer);
                this.hideTimer = null;
            }
        },
    },
};
</script>
