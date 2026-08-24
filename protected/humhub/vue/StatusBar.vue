<template>
    <div
        v-if="entry"
        class="status-bar-body"
        :class="{ 'status-bar-visible': visible }"
    >
        <div class="status-bar-content">
            <a class="status-bar-close float-end" @click="close">&times;</a>
            <i :class="iconClass"></i>
            <span :class="{ 'status-bar-toggle': hasDetails }" @click="toggleDetails">{{ entry.message }}</span>
            <a v-if="hasDetails" class="showMore" @click="toggleDetails">
                <i :class="detailsOpen ? 'fa fa-angle-down' : 'fa fa-angle-up'"></i>
            </a>
            <div v-if="detailsOpen" class="status-bar-details">
                <pre>{{ detailsText }}</pre>
            </div>
        </div>
    </div>
</template>

<script>
/**
 * The platform's user-feedback bar ("status bar") — the strip that slides in at
 * the bottom of the window for flash messages, AJAX errors and the like.
 *
 * It is an infrastructure island: nothing renders it with props, and no caller
 * ever imports it. It is mounted once per page by `humhub\widgets\StatusBar`
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
 * That is also why this component has no i18n: every string it shows was
 * produced by its caller.
 *
 * ## Parity with the jQuery bar it replaces
 *
 * Deliberately a 1:1 replacement of `humhub.ui.status.js`'s `StatusBar` class
 * (owner decision — theme CSS targets these class names, and a toast stack can
 * be added inside this component later without touching a single caller):
 *
 *  - one message at a time; a new one slides the current one out first, then
 *    appears (the legacy `hide(() => setContent().show())` chain).
 *  - the same markup: `.status-bar-body > .status-bar-content` with a
 *    `a.status-bar-close`, a level icon carrying the level class the SCSS
 *    colours (`info`/`success`/`warning`/`error`), the message in a `<span>`,
 *    and — for a message with details — an `a.showMore` chevron plus a
 *    `.status-bar-details > pre` block. Clicking the message text toggles the
 *    details too, as it did before.
 *  - the same auto-close timings (`AUTOCLOSE_*`), including the legacy quirk
 *    that a falsy `closeAfter` falls back to the level default rather than
 *    meaning "stay" — only `error` stays by default.
 *  - the same 500 ms slide. The animation itself moved from jQuery `animate()`
 *    to a CSS transition on `.status-bar-body` (see `_user-feedback.scss`);
 *    this component only toggles `status-bar-visible` and keeps the element
 *    around for the duration of the slide-out.
 *
 * One intentional difference: the message renders as TEXT (`{{ }}`), not HTML.
 * The server path was HTML-encoded anyway (and no longer needs
 * `View::endBody()`'s `&quot;`-stripping hack), and no core caller passes
 * markup — see `docs/develop/module-migrate.md`.
 *
 * ## Details normalisation
 *
 * `details` reaches this component already flattened to a string by
 * `humhub.ui.status` for the cases only legacy code can recognise (a
 * `client.Response`, a jQuery error envelope). Anything else that still arrives
 * as an object is handled here so a Vue-side caller cannot end up with
 * `[object Object]` in the trace block.
 */

// Matches the transition duration in _user-feedback.scss. Kept in JS as well
// because the component owns the swap/removal timing (an element must outlive
// its slide-out) - the two values have to stay in sync.
const TRANSITION_MS = 500;

// Legacy AUTOCLOSE_* values; 0 means "never auto-close" (error only).
const AUTOCLOSE = {
    info: 6000,
    success: 2000,
    warn: 10000,
    error: 0,
};

const ICONS = {
    info: 'fa fa-info-circle info',
    success: 'fa fa-check-circle success',
    warn: 'fa fa-exclamation-triangle warning',
    error: 'fa fa-exclamation-circle error',
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
    data() {
        return {
            entry: null,
            visible: false,
            detailsOpen: false,
        };
    },
    computed: {
        iconClass() {
            return ICONS[this.entry.level] || ICONS.info;
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
                // Slide the current message out first, then show the new one -
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

            // The slide-in needs the hidden state to be laid out BEFORE the class that
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

            const closeAfter = this.autoCloseDelay(entry);
            if (closeAfter > 0) {
                // Legacy started the timer once the slide-in had finished.
                this.closeTimer = setTimeout(() => this.startHide(), TRANSITION_MS + closeAfter);
            }
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
        clearTimers() {
            if (this.closeTimer) {
                clearTimeout(this.closeTimer);
                this.closeTimer = null;
            }
            if (this.hideTimer) {
                clearTimeout(this.hideTimer);
                this.hideTimer = null;
            }
        },
    },
};
</script>
