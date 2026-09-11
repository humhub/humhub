<template>
    <div class="form-checkboxes-normal">
        <div class="btn-group w-100 mb-3" role="group" :aria-label="seenFilterLabel">
            <button
                v-for="option in seenOptions"
                :key="option.value || 'all'"
                type="button"
                class="btn btn-sm"
                :class="option.value === seen ? 'btn-primary' : 'btn-light'"
                :aria-pressed="option.value === seen ? 'true' : 'false'"
                @click="selectSeen(option.value)"
            ><span v-if="option.icon" v-html="option.icon"></span> {{ option.label }}</button>
        </div>

        <div style="padding-left:5px">
            <div class="form-check">
                <input
                    id="notification-filter-all"
                    class="form-check-input"
                    type="checkbox"
                    :checked="allSelected"
                    @change="toggleAll($event.target.checked)"
                >
                <label class="form-check-label" for="notification-filter-all">{{ allLabel }}</label>
            </div>

            <div v-for="category in categories" :key="category.id" class="form-check">
                <input
                    :id="'notification-filter-' + category.id"
                    class="form-check-input"
                    type="checkbox"
                    :checked="selected.includes(category.id)"
                    @change="toggleCategory(category.id, $event.target.checked)"
                >
                <label class="form-check-label" :for="'notification-filter-' + category.id">{{ category.title }}</label>
            </div>
        </div>
    </div>
</template>

<script>
/**
 * The notification overview's filter sidebar: the seen state and the notification categories a
 * module contributed.
 *
 * Emits `change` with `{categories, seen}` on every interaction; the island turns that into a
 * request (see `NotificationOverview.vue`). Categories arrive as a prop — they are
 * module-defined and localized, so the server hands over the list it would have rendered
 * checkboxes for.
 *
 * ## Deliberate deviations from the server-rendered filter
 *
 * - The seen state is a button group instead of `radioList(['template' => 'pills'])` — the same
 *   three options with the same icons, but without reproducing that ActiveField template.
 * - The "All" checkbox works both ways (checking it selects every category, unchecking it
 *   clears them) and reflects "every category selected", which is what the legacy JS did with
 *   its own click handlers in `humhub.notification.js`.
 *
 * @since 1.20
 */
import { i18n } from '@humhub/vue';

export default {
    props: {
        // [{id, title}] - the categories the server offers (localized).
        categories: { type: Array, default: () => [] },
        // Currently selected category ids.
        selected: { type: Array, default: () => [] },
        // '' (all), 'unseen' or 'seen'.
        seen: { type: String, default: '' },
        // Server-rendered icon markup per option: {all, unseen, seen}.
        icons: { type: Object, default: () => ({}) },
    },
    emits: ['change'],
    computed: {
        seenOptions() {
            return [
                { value: '', label: i18n.t('NotificationModule.base', 'All'), icon: this.icons.all },
                { value: 'unseen', label: i18n.t('NotificationModule.base', 'Unseen'), icon: this.icons.unseen },
                { value: 'seen', label: i18n.t('NotificationModule.base', 'Seen'), icon: this.icons.seen },
            ];
        },
        seenFilterLabel() {
            return i18n.t('NotificationModule.base', 'Filter');
        },
        allLabel() {
            return i18n.t('NotificationModule.base', 'All');
        },
        allSelected() {
            return this.categories.length > 0 && this.selected.length === this.categories.length;
        },
    },
    methods: {
        selectSeen(value) {
            this.emitChange({ seen: value });
        },
        toggleAll(checked) {
            this.emitChange({ categories: checked ? this.categories.map((category) => category.id) : [] });
        },
        toggleCategory(id, checked) {
            const categories = checked
                ? [...this.selected, id]
                : this.selected.filter((candidate) => candidate !== id);

            this.emitChange({ categories });
        },
        emitChange(changed) {
            this.$emit('change', {
                categories: changed.categories !== undefined ? changed.categories : [...this.selected],
                seen: changed.seen !== undefined ? changed.seen : this.seen,
            });
        },
    },
};
</script>
