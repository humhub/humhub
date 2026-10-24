<template>
    <Transition name="c-selection-menu">
        <DropdownMenu
            v-if="count > 0"
            root-class="nav c-selection-menu"
            toggle-class="btn c-icon-button c-icon-button--ghost c-selection-menu__toggle"
            :toggle-aria-label="toggleLabel"
            :toggle-title="toggleLabel"
            :menu-id="menuId"
            :entries="entries"
            :context="context"
        >
            <template #toggle><i class="ti ti-dots-vertical" aria-hidden="true"></i></template>
        </DropdownMenu>
    </Transition>
</template>

<script>
import { i18n } from '@humhub/vue';
import DropdownMenu from './DropdownMenu.vue';

/**
 * The actions for a selection (`<selection-menu>`): an icon button with the selection's size in
 * its accessible name ("Actions for 3 selected") that opens a `DropdownMenu` of the given
 * entries, sliding in once something is selected and out again when the selection is empty —
 * e.g. in the `end` slot of a `PathBar`.
 *
 * - `count`: size of the selection; nothing renders at 0.
 * - `entries`, `context`: this menu's own built-in entries and their shared `context`, handed to
 *   `DropdownMenu` (data-driven mode — `{ id, label, icon, sortOrder, onClick(context),
 *   condition(context) }`, `{ id, divider: true }` for a divider).
 * - `menuId` (required): names this menu's entries in the `registerMenuEntry()`/
 *   `removeMenuEntry()` registry — namespace it by the owning view (e.g. `cfiles.selection`),
 *   since `context` differs per consumer; `DropdownMenu` renders no `entries` without one.
 * - Styling: `.c-selection-menu` in `resources/scss/_item-browser.scss`.
 *
 * @since 1.20
 */
export default {
    name: 'SelectionMenu',
    components: { DropdownMenu },
    props: {
        count: { type: Number, required: true },
        menuId: { type: String, required: true },
        entries: { type: Array, default: () => [] },
        context: { type: Object, default: () => ({}) },
    },
    computed: {
        toggleLabel() {
            return i18n.t('base', 'Actions for {count} selected', { count: this.count });
        },
    },
};
</script>
