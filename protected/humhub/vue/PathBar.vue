<template>
    <div class="c-path-bar">
        <button
            v-if="path.length"
            type="button"
            class="btn c-icon-button c-icon-button--ghost c-path-bar__back"
            :aria-label="backLabel"
            :title="backLabel"
            @click="$emit('navigate', parentId)"
        ><i class="ti ti-arrow-left" aria-hidden="true"></i></button>

        <nav class="c-path-bar__path" :aria-label="navLabel">
            <ol class="c-path-bar__crumbs">
                <li class="c-path-bar__crumb" :class="targetClass(null)" v-on="path.length ? dropHandlers(null) : {}">
                    <a
                        :href="rootUrl"
                        class="c-path-bar__root"
                        :class="{ 'is-current': !path.length }"
                        :aria-label="rootLabel"
                        :title="rootLabel"
                        :aria-current="path.length ? null : 'page'"
                        @click="follow(null, $event)"
                    ><i class="ti ti-folders" aria-hidden="true"></i></a>
                </li>
                <li
                    v-for="(crumb, index) in path"
                    :key="crumb.id"
                    class="c-path-bar__crumb"
                    :class="targetClass(crumb.id)"
                    v-on="index < path.length - 1 ? dropHandlers(crumb.id) : {}"
                >
                    <i class="ti ti-chevron-right c-path-bar__separator" aria-hidden="true"></i>
                    <a
                        v-if="index < path.length - 1"
                        :href="crumb.url"
                        class="c-path-bar__link"
                        :title="crumb.title"
                        @click="follow(crumb.id, $event)"
                    >{{ crumb.title }}</a>
                    <span v-else class="c-path-bar__current" :title="crumb.title" aria-current="page">{{ crumb.title }}</span>
                </li>
            </ol>
        </nav>

        <slot name="end"></slot>
    </div>
</template>

<script>
import { i18n } from '@humhub/vue';

const plainClick = (event) => !(event.button > 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey);

/**
 * The path of a hierarchical view (`<path-bar>`): a grey bar with the root as an icon link,
 * the ancestors as links and the current level as text, a back button on narrow screens and an
 * `end` slot on the right (e.g. a `SelectionMenu`).
 *
 * - `path`: the levels below the root, outermost first — `[{ id, title, url }]`; the last one
 *   is the current level. Empty at the top level, where the root is current.
 * - `rootLabel` (accessible name and tooltip of the root link), `rootUrl`.
 * - Emits `navigate(id)` for a plain click on the root (`id` = `null`), an ancestor or the back
 *   button (the parent level); a click with a modifier or the middle button is left to the
 *   browser, so the `url`s open in a new tab.
 * - Drop targets: the root (below the top level) and every ancestor, never the current level.
 *   `canDrop(id, event)` decides whether a drag may land there; for those it emits
 *   `drag-over(id, event)` (repeatedly, for as long as the drag hovers it), `drag-leave(id, event)`
 *   (once, when the pointer actually leaves the crumb rather than moving onto one of its children)
 *   and `drop-on(id, event)` (after `preventDefault()` and `stopPropagation()`, so a surrounding
 *   `DropZone` stays quiet). No `drag-leave` follows a `drop-on` — the owner is expected to clear
 *   its own drop target on drop too. `dropTargetId` highlights one (`null` = the root, `undefined`
 *   = none) — unlike `TileGrid`'s `dropTargetKey`, whose `null` means no target; an owner
 *   tracking drop targets across both keeps two separate values, not one shared `null`.
 * - Styling: `.c-path-bar` in `resources/scss/_item-browser.scss`; the back button shows at
 *   768px and below.
 *
 * @since 1.20
 */
export default {
    name: 'PathBar',
    props: {
        path: { type: Array, default: () => [] },
        rootLabel: { type: String, required: true },
        rootUrl: { type: String, required: true },
        canDrop: { type: Function, default: () => false },
        // No `type`: `dropTargetId` must keep `null` (the root) distinct from `undefined` (no
        // target, the default) - `type: [Number, String, null]` trips Vue's prop validator (it
        // treats `null` as "no type restriction" only when it is the *sole* type, not inside an
        // array) and can warn or coerce `null` back to the default.
        dropTargetId: { default: undefined },
    },
    emits: ['navigate', 'drag-over', 'drag-leave', 'drop-on'],
    computed: {
        parentId() {
            return this.path.length > 1 ? this.path[this.path.length - 2].id : null;
        },
        backLabel() {
            return i18n.t('base', 'Back');
        },
        navLabel() {
            return i18n.t('base', 'Breadcrumb');
        },
    },
    methods: {
        follow(id, event) {
            if (!plainClick(event)) {
                return;
            }
            event.preventDefault();
            this.$emit('navigate', id);
        },
        targetClass(id) {
            return { 'is-drop-target': this.dropTargetId !== undefined && this.dropTargetId === id };
        },
        dropHandlers(id) {
            return {
                dragover: (event) => {
                    if (this.canDrop(id, event)) {
                        event.preventDefault();
                        this.$emit('drag-over', id, event);
                    }
                },
                dragleave: (event) => {
                    if (!event.currentTarget.contains(event.relatedTarget)) {
                        this.$emit('drag-leave', id, event);
                    }
                },
                drop: (event) => {
                    if (!this.canDrop(id, event)) {
                        return;
                    }
                    event.preventDefault();
                    event.stopPropagation();
                    this.$emit('drop-on', id, event);
                },
            };
        },
    },
};
</script>
