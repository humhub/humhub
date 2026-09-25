<template>
    <div class="c-card-grid" :class="{ 'is-loading': loading && items.length > 0 }">
        <div class="c-card-grid__cells" :aria-busy="loading ? 'true' : 'false'">
            <div
                v-for="(item, index) in items"
                :key="item[itemKey]"
                class="c-card-grid__cell"
                :class="cardClass"
                :style="{ '--card-stagger-index': staggerIndex(index) }"
                :data-id="item[itemKey]"
            >
                <slot name="card" :item="item" :index="staggerIndex(index)"></slot>
            </div>
            <template v-if="!items.length">
                <template v-if="loading">
                    <div
                        v-for="n in skeletonCount"
                        :key="`skeleton-${n}`"
                        class="c-card-grid__cell c-card-grid__cell--skeleton"
                        :class="cardClass"
                        :style="{ '--card-stagger-index': n - 1 }"
                    >
                        <CardSkeleton />
                    </div>
                </template>
                <div v-else-if="error" class="c-card-grid__message c-card-grid__message--error">
                    <p role="alert" class="c-card-grid__message-text">{{ error }}</p>
                    <button type="button" class="btn btn-light btn-sm" @click="$emit('retry')">{{ retryLabel }}</button>
                </div>
                <div v-else class="c-card-grid__message c-card-grid__message--empty">
                    <p role="status" class="c-card-grid__message-text">
                        <slot name="empty"><strong>{{ emptyTitle }}</strong><br>{{ emptyHint }}</slot>
                    </p>
                </div>
            </template>
        </div>
        <div v-if="error && items.length" class="c-card-grid__error cards-error">
            <p role="alert" class="c-card-grid__message-text">{{ error }}</p>
            <button type="button" class="btn btn-light btn-sm" @click="$emit('retry')">{{ retryLabel }}</button>
        </div>
        <div v-else-if="hasMore" ref="sentinel" class="c-card-grid__more cards-more">
            <span v-if="loading" class="spinner-border spinner-border-sm" role="status" :aria-label="loadingLabel"></span>
            <button v-else type="button" class="btn btn-light btn-sm" @click="$emit('load-more')">{{ moreLabel }}</button>
        </div>
    </div>
</template>

<script>
import { i18n } from '@humhub/vue';
import CardSkeleton from './CardSkeleton.vue';

/**
 * The card grid of a card directory page (`CardDirectory`): a CSS grid of cells at least 264px
 * wide (`repeat(auto-fill, minmax(264px, 1fr))`, gap 16px), whose cards pop in staggered —
 * `.c-card-grid` in `resources/scss/_card-directory.scss`.
 *
 * - `card` slot, `{ item, index }`: the directory's own card, rendered inside a
 *   `.c-card-grid__cell` (`data-id` = the item's key). `index` is the item's position within
 *   the page it was loaded with (see `pageStarts`); the cell carries it as
 *   `--card-stagger-index`, which delays its `pop-in` by index × 32ms.
 * - `cardClass`: an optional extra class on every cell (card and skeleton) — the grid owns the
 *   layout, so no Bootstrap column classes.
 * - `pageStarts`: the offsets in `items` at which each loaded page begins (`[0, 24, ...]`), so
 *   a further page staggers from 0 again instead of continuing the count.
 * - `empty` slot: replaces the "No results found!" text.
 * - States: skeletons (`CardSkeleton`) while the first page loads; while a later request loads
 *   the cards stay and the grid is dimmed (`.is-loading`); an error without cards replaces the
 *   grid (`.c-card-grid__message--error`), an error while paging sits below it
 *   (`.c-card-grid__error`, hook class `cards-error`) — both with a retry.
 * - Paging: a "Show more" button (`.c-card-grid__more`, hook class `cards-more`) while
 *   `hasMore` is set, which an `IntersectionObserver` presses on its own when it scrolls into
 *   view (the button remains for keyboard users and for browsers without the observer); the
 *   observer is re-armed once a page finishes loading, since a sentinel that never left the
 *   viewport does not refire its intersection on its own, which would otherwise stall
 *   auto-paging after one page. Emits `load-more` for a further page; `retry` for a failed
 *   first page and for a failed later page too (below an already-populated grid) — the owner
 *   decides what to re-fetch either way.
 *
 * @since 1.20
 */
export default {
    name: 'CardGrid',
    components: { CardSkeleton },
    props: {
        items: { type: Array, required: true },
        itemKey: { type: String, default: 'id' },
        loading: { type: Boolean, default: false },
        error: { type: String, default: null },
        hasMore: { type: Boolean, default: false },
        skeletonCount: { type: Number, default: 12 },
        cardClass: { type: String, default: null },
        pageStarts: { type: Array, default: () => [0] },
    },
    emits: ['load-more', 'retry'],
    computed: {
        emptyTitle() {
            return i18n.t('base', 'No results found!');
        },
        emptyHint() {
            return i18n.t('base', 'Try other keywords or remove filters.');
        },
        retryLabel() {
            return i18n.t('base', 'Try again');
        },
        moreLabel() {
            return i18n.t('base', 'Show more');
        },
        loadingLabel() {
            return i18n.t('base', 'Loading...');
        },
    },
    watch: {
        loading(isLoading, wasLoading) {
            // A sentinel that stayed in the viewport across the request never re-enters it, so
            // the browser never fires a fresh intersection entry for it on its own once loading
            // clears - re-arm the same observer/target explicitly so auto-paging does not stall
            // after a single page.
            if (wasLoading && !isLoading) {
                this.rearm();
            }
        },
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
        staggerIndex(index) {
            let start = 0;
            for (const offset of this.pageStarts) {
                if (offset <= index && offset > start) {
                    start = offset;
                }
            }
            return index - start;
        },
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
                if (entries.some((entry) => entry.isIntersecting) && this.hasMore && !this.loading && !this.error) {
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
