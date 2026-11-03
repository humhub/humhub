import { positionDrawer, resetDrawer } from './drawerPosition.js';

/**
 * The listbox's own height limit, in px — `$select-menu-max-height` (`_picker.scss`).
 */
export const DRAWER_MAX_HEIGHT = 384;

/**
 * How many options PageUp/PageDown move: what the listbox shows but one, 10 without layout.
 */
export const pageStep = (listbox) => {
    const option = listbox?.querySelector('[role="option"]');
    const height = option ? option.offsetHeight : 0;
    return height > 0 ? Math.max(1, Math.floor(listbox.clientHeight / height) - 1) : 10;
};

/**
 * What the comboboxes of the platform share — `FilterPicker`, `PickerFilterControl` and
 * `PickerInput` (see docs/develop/ui-js-vuejs-picker.md, "Shared combobox"): the open state,
 * the active option (`aria-activedescendant`) and moving it (also a page, `movePage()`), the click outside that closes,
 * and the fixed positioning of the listbox while open.
 *
 * The host defines `listboxId` (data), `comboboxOptions` (computed: the listed options, in
 * order), the refs `root` (the whole control), `field` (optional: what the list is placed
 * against, else `root`) and `listbox`, a method `close()`, and calls `bindOutside()` when it
 * opens and `unbindOutside()` when it closes.
 *
 * @since 1.20
 */
export default {
    data() {
        return {
            open: false,
            activeIndex: -1,
            dropUp: false,
        };
    },
    beforeUnmount() {
        this.unbindOutside();
    },
    methods: {
        optionId(index) {
            return `${this.listboxId}-${index}`;
        },
        // PageUp (-1) / PageDown (1): a page of options, stopping at the first and the last.
        movePage(direction) {
            const last = this.comboboxOptions.length - 1;
            const from = Math.max(this.activeIndex, 0);
            this.moveTo(Math.min(Math.max(from + direction * pageStep(this.$refs.listbox), 0), last));
        },
        moveTo(index) {
            if (index < 0 || !this.comboboxOptions.length) {
                return;
            }
            this.activeIndex = Math.min(index, this.comboboxOptions.length - 1);
            this.$nextTick(() => this.scrollActiveIntoView());
        },
        scrollActiveIntoView() {
            const node = this.activeIndex >= 0 ? this.$refs.listbox?.querySelectorAll('[role="option"]')[this.activeIndex] : null;
            if (node && typeof node.scrollIntoView === 'function') {
                node.scrollIntoView({ block: 'nearest' });
            }
        },
        placeDrawer() {
            const anchor = this.$refs.field || this.$refs.root;
            if (!this.open || !anchor || !this.$refs.listbox) {
                return;
            }
            this.dropUp = positionDrawer(anchor, this.$refs.listbox, DRAWER_MAX_HEIGHT).dropUp;
        },
        bindOutside() {
            if (this.outsideHandler) {
                return;
            }
            this.outsideHandler = (event) => {
                if (this.$refs.root && !this.$refs.root.contains(event.target)) {
                    this.close();
                }
            };
            // A scroll inside the listbox does not move it.
            this.placeHandler = (event) => {
                if (!(event?.target instanceof Node) || !this.$refs.listbox?.contains(event.target)) {
                    this.placeDrawer();
                }
            };
            document.addEventListener('pointerdown', this.outsideHandler, true);
            document.addEventListener('focusin', this.outsideHandler, true);
            window.addEventListener('scroll', this.placeHandler, true);
            window.addEventListener('resize', this.placeHandler);
            window.visualViewport?.addEventListener('resize', this.placeHandler);
            this.$nextTick(() => this.placeDrawer());
        },
        unbindOutside() {
            if (!this.outsideHandler) {
                return;
            }
            document.removeEventListener('pointerdown', this.outsideHandler, true);
            document.removeEventListener('focusin', this.outsideHandler, true);
            window.removeEventListener('scroll', this.placeHandler, true);
            window.removeEventListener('resize', this.placeHandler);
            window.visualViewport?.removeEventListener('resize', this.placeHandler);
            this.outsideHandler = null;
            this.placeHandler = null;
            this.dropUp = false;
            if (this.$refs.listbox) {
                resetDrawer(this.$refs.listbox);
            }
        },
    },
};
