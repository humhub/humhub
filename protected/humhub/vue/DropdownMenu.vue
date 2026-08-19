<template>
    <ul class="nav nav-pills preferences">
        <li class="nav-item dropdown">
            <a
                href="#"
                :class="toggleClass"
                data-bs-toggle="dropdown"
                role="button"
                aria-haspopup="true"
                aria-expanded="false"
                :aria-label="toggleAriaLabel"
            ></a>

            <ul class="dropdown-menu" :class="{ 'dropdown-menu-end': alignEnd }">
                <slot />
            </ul>
        </li>
    </ul>
</template>

<script>
/**
 * Generic dropdown-toggle menu — the Vue analog of the `nav nav-pills
 * preferences` / `.dropdown-toggle` + `.dropdown-menu` markup pattern PHP
 * widgets render throughout the app (e.g. `humhub\widgets\PanelMenu`,
 * `content\widgets\WallEntryControls`, the former
 * `comment/widgets/views/commentControls.php`). Core infrastructure, not
 * tied to any one module — any island's template can reach for
 * `<DropdownMenu>` instead of hand-rolling this structure again.
 *
 * Toggling, closing (click-away/Escape) and keyboard navigation are all
 * handled by Bootstrap's own dropdown JS via `data-bs-toggle="dropdown"` —
 * nothing here is Vue-owned. That JS only cares about the DOM structure
 * (a `.dropdown` ancestor containing the toggle and a sibling
 * `.dropdown-menu`), not how it was rendered, so Vue-rendered markup works
 * identically to server-rendered markup — proven in production by the
 * comment island's own controls dropdown before this component existed.
 *
 * ## Slot contract
 *
 * Menu items are supplied entirely through the default slot — this
 * component renders no items itself, only the toggle button and the
 * `<ul class="dropdown-menu">` wrapper. Consumers render one `<li>` per
 * item, each normally wrapping an `<a class="dropdown-item">` (or a
 * `DropdownDivider`-style `<li><hr class="dropdown-divider"></li>`), giving
 * callers full control over links, click handlers, icons and conditional
 * (`v-if`) items:
 *
 * ```html
 * <DropdownMenu :toggle-aria-label="label">
 *     <li><a href="#" class="dropdown-item" @click.prevent="onEdit">Edit</a></li>
 * </DropdownMenu>
 * ```
 *
 * ## Props
 *  - `toggleAriaLabel` (required) — accessible name for the toggle button,
 *    which otherwise carries no visible text (styling supplies the
 *    caret/kebab icon).
 *  - `alignEnd` (default `true`) — adds `dropdown-menu-end`, right-aligning
 *    the menu under its toggle; the overwhelmingly common case for a
 *    controls dropdown anchored at the end of a row. Set `false` to align
 *    it to the start instead.
 *  - `toggleClass` (default `'nav-link dropdown-toggle'`) — replaces the
 *    toggle `<a>`'s classes entirely (not merged), so a caller needing an
 *    icon-only kebab toggle or a differently styled trigger doesn't fight
 *    the default.
 */
export default {
    props: {
        toggleAriaLabel: { type: String, required: true },
        alignEnd: { type: Boolean, default: true },
        toggleClass: { type: String, default: 'nav-link dropdown-toggle' },
    },
};
</script>
