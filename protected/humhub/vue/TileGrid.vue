<template>
    <div class="c-tile-grid" :class="{ 'is-selecting': selection.length > 0 }" :aria-busy="loading ? 'true' : null">
        <div class="c-tile-grid__stage">
            <Transition :name="swapName">
                <div :key="level" class="c-tile-grid__pane">
                    <ul v-if="showSkeleton" class="c-tile-grid__tiles" aria-hidden="true">
                        <li v-for="n in skeletonCount" :key="'skeleton-' + n" class="c-tile-grid__skeleton">
                            <span class="c-tile-grid__skeleton-block c-tile-grid__skeleton-thumb"></span>
                            <span class="c-tile-grid__skeleton-block c-tile-grid__skeleton-name"></span>
                        </li>
                    </ul>
                    <TransitionGroup
                        v-else-if="items.length"
                        tag="ul"
                        name="c-tile-grid-fade"
                        class="c-tile-grid__tiles"
                    >
                        <li
                            v-for="item in items"
                            :key="item[itemKey]"
                            class="c-tile-grid__tile"
                            :class="tileClass(item)"
                            :data-key="item[itemKey]"
                            :draggable="isDraggable(item) ? 'true' : null"
                            @contextmenu="onContextMenu(item, $event)"
                            @dragstart="onDragStart(item, $event)"
                            @dragend="onDragEnd(item, $event)"
                            @dragover="onDragOver(item, $event)"
                            @dragleave="onDragLeave(item, $event)"
                            @drop="onDrop(item, $event)"
                        >
                            <div class="c-tile-grid__thumb" aria-hidden="true"><slot name="thumb" :item="item"></slot></div>
                            <div class="c-tile-grid__name"><slot name="name" :item="item"></slot></div>
                            <span class="c-tile-grid__meta"><slot name="meta" :item="item"></slot></span>
                            <ProgressFrame
                                v-if="item.uploading"
                                class="c-tile-grid__progress"
                                :value="item.progress || 0"
                                :label="uploadingLabel"
                            />
                            <label v-if="selectable && !item.uploading" class="c-tile-grid__check">
                                <input
                                    type="checkbox"
                                    class="c-tile-grid__check-input"
                                    :checked="isSelected(item)"
                                    :aria-label="selectLabel(item)"
                                    @click="onCheck(item, $event)"
                                />
                                <i class="ti ti-check c-tile-grid__check-mark" aria-hidden="true"></i>
                            </label>
                            <div v-if="$slots.actions && !item.uploading" class="c-tile-grid__actions">
                                <slot name="actions" :item="item"></slot>
                            </div>
                        </li>
                    </TransitionGroup>
                    <div v-else class="c-tile-grid__empty">
                        <slot name="empty">{{ emptyLabel }}</slot>
                    </div>
                </div>
            </Transition>
        </div>

        <div v-if="hasMore" class="c-tile-grid__more">
            <button
                ref="sentinel"
                type="button"
                class="btn btn-light"
                :disabled="loadingMore"
                @click="$emit('load-more')"
            >{{ loadingMore ? loadingLabel : moreLabel }}</button>
        </div>
    </div>
</template>

<script>
import { i18n } from '@humhub/vue';
import ProgressFrame from './ProgressFrame.vue';

/**
 * A grid of tiles (`<tile-grid>`) for the items of one level of a browsable collection —
 * folders and files, albums and photos, pages: cells at least 136px wide
 * (`repeat(auto-fill, minmax(136px, 1fr))`, gap 8px), after the HumHub design system v2's file
 * browser. Holds no state of its own: what is selected, where a drag may land and which level
 * is shown come in as props, what the user wants goes out as events.
 *
 * - `items`, `itemKey` (default `id`) — the key must be unique across the item kinds. `labelFor`
 *   (default `(item) => item.title ?? ''`) names an item for messages built from it, e.g. the
 *   selection checkbox's accessible name ("Select {name}").
 * - Slots per tile, `{ item }`: `thumb` (100px high preview area — render images there with
 *   `draggable="false"`: a page `<img>` is draggable on its own, and in Chrome its drag carries
 *   `Files`, so it would start an image drag instead of the tile's and light up a surrounding
 *   `DropZone`), `name` (an `<a>` or `<button>` — it is stretched over the whole tile, so the
 *   tile is one click target), `meta` (a line below), `actions` (top right, shown on touch
 *   devices — typically the item's context-menu toggle). `empty` replaces the
 *   "No results found!" text.
 * - Selection: `selectable` adds a checkbox per tile (on hover and focus, on every tile while
 *   `selection` — the selected keys — is not empty, always on touch devices). Emits
 *   `toggle-select(item, { range })`, `range` being true for a Shift-click; the owner resolves
 *   a range against its own order, which it shares with other views of the same items.
 * - Context menu: a right-click emits `context-menu(item, event)` (Ctrl+right-click stays the
 *   browser's). For a keyboard-opened menu (`event.button !== 2`) position it at
 *   `event.target.getBoundingClientRect()` rather than at `clientX`/`clientY`.
 * - Drag and drop: `draggable` makes tiles draggable (`drag-start(item, event)`,
 *   `drag-end(item, event)`, the tile as the drag image); render the name `<a>` with
 *   `draggable="false"` then too, so the tile is the drag source and no link URL rides along
 *   in the drag data. `canDrop(item, event)` decides where a drag may land — for those it emits
 *   `drag-over(item, event)` (repeatedly, for as long as the drag hovers the tile) and
 *   `drop-on(item, event)` (after `preventDefault()` and `stopPropagation()`, so a surrounding
 *   `DropZone` stays quiet). `drag-leave(item, event)` fires for any tile the pointer leaves,
 *   whatever `canDrop` says (once, when it actually leaves the tile rather than moving onto one
 *   of its children). No `drag-leave` follows a `drop-on` — the owner is expected to clear its
 *   own drop target on drop too. `dropTargetKey` highlights one tile — its `null` means no
 *   target, unlike `PathBar`'s `dropTargetId`, where `undefined` means no target and `null` is
 *   the root; an owner tracking drop targets across both keeps two separate values (or a small
 *   map keyed by which view a target belongs to), not one shared `null`.
 * - Uploads: an item with `uploading: true` is dimmed and framed by a `ProgressFrame`
 *   (`progress`, 0–100), without checkbox, actions or drag.
 * - Loading: skeleton tiles (`skeletonCount`) while `loading` and no items are there yet;
 *   `level` identifies the level shown — changing it slides the new level in, from the right
 *   (`direction` `forward`, deeper) or from the left (`back`). Set `items: []` and
 *   `loading: true` together with the new `level`, or the new pane slides in with the old items.
 * - Paging: a "Show more" button while `hasMore`, pressed by an `IntersectionObserver` as it
 *   scrolls into view (like `CardGrid`); emits `load-more`, not while `loadingMore`.
 * - Styling: `.c-tile-grid` in `resources/scss/_item-browser.scss`.
 *
 * @since 1.20
 */
export default {
    name: 'TileGrid',
    components: { ProgressFrame },
    props: {
        items: { type: Array, required: true },
        itemKey: { type: String, default: 'id' },
        selectable: { type: Boolean, default: false },
        selection: { type: Array, default: () => [] },
        draggable: { type: Boolean, default: false },
        canDrop: { type: Function, default: () => false },
        dropTargetKey: { type: [String, Number], default: null },
        loading: { type: Boolean, default: false },
        skeletonCount: { type: Number, default: 12 },
        hasMore: { type: Boolean, default: false },
        loadingMore: { type: Boolean, default: false },
        level: { type: [String, Number], default: 0 },
        direction: { type: String, default: 'forward' },
        labelFor: { type: Function, default: (item) => item.title ?? '' },
    },
    emits: ['toggle-select', 'context-menu', 'drag-start', 'drag-end', 'drag-over', 'drag-leave', 'drop-on', 'load-more'],
    computed: {
        showSkeleton() {
            return this.loading && !this.items.length;
        },
        swapName() {
            return this.direction === 'back' ? 'c-tile-grid-swap-back' : 'c-tile-grid-swap-forward';
        },
        emptyLabel() {
            return i18n.t('base', 'No results found!');
        },
        moreLabel() {
            return i18n.t('base', 'Show more');
        },
        loadingLabel() {
            return i18n.t('base', 'Loading...');
        },
        uploadingLabel() {
            return i18n.t('base', 'Uploading...');
        },
    },
    watch: {
        loadingMore(isLoading, wasLoading) {
            if (wasLoading && !isLoading) {
                this.rearm();
            }
        },
    },
    created() {
        // Key of the tile this grid started a drag on - a plain field, not reactive.
        this.dragKey = undefined;
    },
    mounted() {
        this.observe();
    },
    updated() {
        this.observe();
    },
    beforeUnmount() {
        if (this.observer) {
            this.observer.disconnect();
        }
    },
    methods: {
        isSelected(item) {
            return this.selection.includes(item[this.itemKey]);
        },
        isDraggable(item) {
            return this.draggable && !item.uploading;
        },
        selectLabel(item) {
            return i18n.t('base', 'Select {name}', { name: this.labelFor(item) });
        },
        tileClass(item) {
            return {
                'is-selected': this.isSelected(item),
                'is-drop-target': this.dropTargetKey !== null && this.dropTargetKey === item[this.itemKey],
                'is-uploading': !!item.uploading,
            };
        },
        onContextMenu(item, event) {
            if (event.ctrlKey) {
                return;
            }
            event.preventDefault();
            this.$emit('context-menu', item, event);
        },
        onCheck(item, event) {
            this.$emit('toggle-select', item, { range: event.shiftKey });
            // The browser has already flipped the box; put it back to what `selection` says,
            // in case the owner did not (or not yet) change it.
            this.$nextTick(() => {
                event.target.checked = this.isSelected(item);
            });
        },
        onDragStart(item, event) {
            // Links and images are draggable on their own, and their dragstart bubbles up here.
            if (!this.isDraggable(item)) {
                return;
            }
            this.dragKey = item[this.itemKey];
            // A drag that starts on the stretched name link (the usual case) would show the link's
            // ghost — the tile is what moves.
            if (event.target !== event.currentTarget && event.dataTransfer?.setDragImage) {
                const rect = event.currentTarget.getBoundingClientRect();
                event.dataTransfer.setDragImage(event.currentTarget, event.clientX - rect.left, event.clientY - rect.top);
            }
            this.$emit('drag-start', item, event);
        },
        onDragEnd(item, event) {
            // Pair it with our own drag-start, even if the tile has stopped being draggable since.
            if (this.dragKey === undefined || this.dragKey !== item[this.itemKey]) {
                return;
            }
            this.dragKey = undefined;
            this.$emit('drag-end', item, event);
        },
        onDragOver(item, event) {
            if (this.canDrop(item, event)) {
                event.preventDefault();
                this.$emit('drag-over', item, event);
            }
        },
        onDragLeave(item, event) {
            // Moving onto the tile's own children (thumb, name, checkbox) fires `dragleave` too.
            // Older WebKit sends `relatedTarget` null - then every leave emits, which is acceptable.
            if (!event.currentTarget.contains(event.relatedTarget)) {
                this.$emit('drag-leave', item, event);
            }
        },
        onDrop(item, event) {
            if (!this.canDrop(item, event)) {
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            this.$emit('drop-on', item, event);
        },
        // Same auto-paging as CardGrid: observe the "Show more" button, re-arm after a page.
        observe() {
            if (typeof IntersectionObserver === 'undefined') {
                return;
            }
            const sentinel = this.$refs.sentinel || null;
            if (sentinel === this.observed) {
                return;
            }
            if (this.observer) {
                this.observer.disconnect();
            }
            this.observed = sentinel;
            if (!sentinel) {
                return;
            }
            this.observer = this.observer || new IntersectionObserver((entries) => {
                if (entries.some((entry) => entry.isIntersecting) && this.hasMore && !this.loadingMore) {
                    this.$emit('load-more');
                }
            }, { rootMargin: '400px' });
            this.observer.observe(sentinel);
        },
        rearm() {
            const sentinel = this.$refs.sentinel || null;
            if (this.observer && sentinel) {
                this.observer.unobserve(sentinel);
                this.observer.observe(sentinel);
            }
        },
    },
};
</script>
