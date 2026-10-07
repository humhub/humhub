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

            <div v-for="filter in filters" :key="filter.id" class="form-check">
                <input
                    :id="'notification-filter-' + filter.id"
                    class="form-check-input"
                    type="checkbox"
                    :checked="selected.includes(filter.id)"
                    @change="toggleGroup(filter.id, $event.target.checked)"
                >
                <label class="form-check-label" :for="'notification-filter-' + filter.id">{{ filter.title }}</label>
            </div>
        </div>
    </div>
</template>

<script>
/**
 * The notification overview's filter sidebar: the seen state and the notification groups
 * (`direct`, `social`, `content`, `admin` and the groups modules add).
 *
 * Emits `change` with `{groups, seen}` on every interaction; the island turns that into a
 * request (see `NotificationOverview.vue`). The groups arrive as the `filters` prop — they are
 * module-defined and localized, so the server hands over the list to render checkboxes for.
 *
 * ## Deliberate deviations from the server-rendered filter
 *
 * - The seen state is a button group instead of `radioList(['template' => 'pills'])` — the same
 *   three options with the same icons, but without reproducing that ActiveField template.
 * - The "All" checkbox works both ways (checking it selects every group, unchecking it clears
 *   them) and reflects "every group selected", which is what the legacy JS did with its own
 *   click handlers in `humhub.notification.js`.
 *
 * @since 1.20
 */
import { i18n } from '@humhub/vue';

export default {
    props: {
        // [{id, title}] - the notification groups the server offers (localized).
        filters: { type: Array, default: () => [] },
        // Currently selected group ids.
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
            return this.filters.length > 0 && this.selected.length === this.filters.length;
        },
    },
    methods: {
        selectSeen(value) {
            this.emitChange({ seen: value });
        },
        toggleAll(checked) {
            this.emitChange({ groups: checked ? this.filters.map((filter) => filter.id) : [] });
        },
        toggleGroup(id, checked) {
            const groups = checked
                ? [...this.selected, id]
                : this.selected.filter((candidate) => candidate !== id);

            this.emitChange({ groups });
        },
        emitChange(changed) {
            this.$emit('change', {
                groups: changed.groups !== undefined ? changed.groups : [...this.selected],
                seen: changed.seen !== undefined ? changed.seen : this.seen,
            });
        },
    },
};
</script>
