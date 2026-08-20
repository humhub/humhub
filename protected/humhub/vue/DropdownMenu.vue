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
                <li v-for="entry in resolvedEntries" :key="entry.id">
                    <component :is="entry.component" v-if="entry.component" :context="context" />
                    <a
                        v-else-if="entry.icon"
                        href="#"
                        class="dropdown-item d-flex align-items-center gap-2"
                        @click.prevent="onEntryClick(entry)"
                    ><i :class="'fa fa-' + entry.icon" aria-hidden="true"></i>{{ resolveLabel(entry) }}</a>
                    <a
                        v-else
                        href="#"
                        class="dropdown-item"
                        @click.prevent="onEntryClick(entry)"
                    >{{ resolveLabel(entry) }}</a>
                </li>
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
 * Free-form items are supplied through the default slot — always rendered
 * first, ahead of any data-driven entries below. Consumers render one `<li>`
 * per item, each normally wrapping an `<a class="dropdown-item">` (or a
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
 * This is still the only option for an item that needs markup the entry
 * descriptor shape below cannot express — e.g. `CommentControls.vue`'s
 * permalink item, which carries legacy `data-action-click`/
 * `data-content-permalink*` attributes picked up by a delegated document
 * click handler (see `humhub.action.js`) rather than a Vue click handler.
 *
 * ## Data-driven mode (`menuId`/`entries`)
 *
 * The alternative to the slot above: an array of entry descriptors, the
 * Vue analog of the server-side `humhub\modules\ui\menu\widgets\Menu` API
 * (`addEntry()`/`removeEntry()`, entries with an `id` and a `sortOrder`).
 * Pass `menuId` (identifies this menu to `registerMenuEntry()`/
 * `removeMenuEntry()` — see `humhub.vue.js`) and `entries` (this
 * component's own BUILT-IN entries, same descriptor shape a module's
 * `registerMenuEntry()` call uses) to render a resolved, reactive list of
 * entries after the slot content:
 *
 * ```html
 * <DropdownMenu :toggle-aria-label="label" menu-id="comment.controls" :entries="entries" :context="{ comment }" />
 * ```
 *
 * **Resolution pipeline** (a computed, so registering/removing an entry
 * anywhere re-renders every currently-mounted menu for that `menuId`
 * without a remount):
 *
 * 1. Start from `entries` (built-ins, in prop order) followed by any
 *    registry entries for `menuId` that do not share an id with a built-in.
 * 2. **Override**: a registry entry whose `id` matches a built-in's `id`
 *    REPLACES that built-in, in the built-in's own position.
 * 3. **Removal**: drop any entry (built-in or registry, already-overridden
 *    or not) whose `id` was passed to `removeMenuEntry(menuId, id)` — a
 *    removal is checked here, not baked into the registry itself, which is
 *    what lets it suppress a built-in that was never registered at all.
 * 4. Drop entries whose `condition(context)` returns falsy (entries with no
 *    `condition` always pass).
 * 5. Drop `component`-entries whose component is not (yet) registered —
 *    same "stay optional, no Vue resolution warning" rule `ExtensionSlot`
 *    applies to its own entries, checked via `isRegistered()`.
 * 6. Sort by `sortOrder` ascending (default `1000`); entries sharing a
 *    `sortOrder` keep their step-1 order — **built-ins before registry
 *    entries**, each group in its own original order.
 *
 * Each resolved entry renders as `<li>`: a `component` entry renders that
 * component with a single `context` prop (not spread — unlike
 * `ExtensionSlot`'s `v-bind="context"`); otherwise an
 * `<a class="dropdown-item">` calling `@click.prevent="onClick(context)"`,
 * with a leading `<i class="fa fa-<icon>">` (plus a flex/gap class on the
 * `<a>` for spacing) when `icon` is set — a separate template branch (not a
 * nested `v-if` on the icon alone), so an entry without an icon renders the
 * exact same `<a class="dropdown-item">{{ label }}</a>` markup as before
 * this mode existed, with no leftover Vue `v-if` comment node.
 *
 * See docs/develop/ui-js-vuejs-extensions.md, "Menu entries", for the full
 * entry shape and worked examples.
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
 *  - `menuId` (default `null`) — identifies this menu's entries in the
 *    `registerMenuEntry()`/`removeMenuEntry()` registry. Data-driven mode
 *    is entirely off (no registry lookup, nothing rendered beyond the slot)
 *    when this is not set, regardless of `entries`.
 *  - `entries` (default `[]`) — this menu's own built-in entries, only
 *    consulted when `menuId` is set. Same descriptor shape as
 *    `registerMenuEntry()`'s second argument.
 *  - `context` (default `{}`) — passed to every entry's `condition`/
 *    `onClick`/`component`, and to every module-registered entry resolved
 *    for `menuId`.
 *
 * @since 1.19
 */
import { getMenuEntries, isRegistered } from '@humhub/vue';

export default {
    props: {
        toggleAriaLabel: { type: String, required: true },
        alignEnd: { type: Boolean, default: true },
        toggleClass: { type: String, default: 'nav-link dropdown-toggle' },
        menuId: { type: String, default: null },
        entries: { type: Array, default: () => [] },
        context: { type: Object, default: () => ({}) },
    },
    computed: {
        resolvedEntries() {
            if (!this.menuId) {
                return [];
            }

            const registry = getMenuEntries(this.menuId);
            const registryById = new Map(registry.entries.map((entry) => [entry.id, entry]));

            // Step 1 + 2: built-ins in prop order, overridden in place by a same-id registry
            // entry, followed by the registry's own non-overriding entries in their own order.
            const usedRegistryIds = new Set();
            const merged = this.entries.map((entry) => {
                const override = registryById.get(entry.id);
                if (override) {
                    usedRegistryIds.add(entry.id);
                    return override;
                }
                return entry;
            });
            registry.entries.forEach((entry) => {
                if (!usedRegistryIds.has(entry.id)) {
                    merged.push(entry);
                }
            });

            const removed = registry.removed;
            const context = this.context;
            const sortOrderOf = (entry) => (typeof entry.sortOrder === 'number' ? entry.sortOrder : 1000);

            // Step 3-5, then a stable sort (step 6) keyed on the pre-sort array position so
            // ties keep the step-1/2 order (built-ins first) rather than whatever order a
            // given JS engine's Array#sort happens to leave equal-key entries in.
            return merged
                .filter((entry) => removed.indexOf(entry.id) === -1
                    && (!entry.condition || entry.condition(context))
                    && (!entry.component || isRegistered(entry.component)))
                .map((entry, index) => ({ entry, index }))
                .sort((a, b) => (sortOrderOf(a.entry) - sortOrderOf(b.entry)) || (a.index - b.index))
                .map((wrapped) => wrapped.entry);
        },
    },
    methods: {
        resolveLabel(entry) {
            return typeof entry.label === 'function' ? entry.label(this.context) : entry.label;
        },
        onEntryClick(entry) {
            if (typeof entry.onClick === 'function') {
                entry.onClick(this.context);
            }
        },
    },
};
</script>
