<template>
    <div v-if="column.type === 'main'" class="data-table-main">
        <img v-if="cell.image" :src="cell.image" class="data-table-main-image" alt="" aria-hidden="true">
        <span
            v-else-if="cell.initials"
            class="data-table-main-image data-table-main-initials"
            :style="cell.color ? { backgroundColor: cell.color } : null"
            aria-hidden="true"
        >{{ cell.initials }}</span>
        <span class="data-table-main-text">
            <a v-if="cell.url" :href="cell.url" class="data-table-main-name">{{ cell.text }}</a>
            <span v-else class="data-table-main-name">{{ cell.text }}</span>
            <span v-if="cell.subtitle" class="data-table-main-subtitle">{{ cell.subtitle }}</span>
        </span>
    </div>
    <span v-else-if="column.type === 'chip'" class="data-table-chips">
        <template v-for="chip in chips" :key="chip.value || chip.text">
            <button
                v-if="column.filter && chip.value !== undefined"
                type="button"
                class="badge"
                :class="chip.tone ? `badge-${chip.tone}` : null"
                :title="filterLabel(chip)"
                :aria-label="filterLabel(chip)"
                @click.stop="$emit('filter', column.filter, chip.value)"
            >{{ chip.text }}</button>
            <span v-else class="badge" :class="chip.tone ? `badge-${chip.tone}` : null">{{ chip.text }}</span>
        </template>
    </span>
    <time v-else-if="column.type === 'date' && cell.value" :datetime="cell.value" :title="cell.title">{{ cell.text }}</time>
    <span v-else>{{ cell.text }}</span>
</template>

<script>
import { i18n } from '@humhub/vue';

/**
 * One cell of a `DataTable` (internal, see `DataTable.vue`), rendered by its column's type: the
 * main column's image (or the initials on a coloured tile), linked name and subtitle, the badges of a chip column (buttons setting the
 * column's filter when it has one), a date with its full date and time as tooltip, or a text.
 *
 * @since 1.20
 */
export default {
    name: 'TableCell',
    props: {
        column: { type: Object, required: true },
        cell: { type: [Object, Array], required: true },
    },
    emits: ['filter'],
    computed: {
        chips() {
            return Array.isArray(this.cell) ? this.cell : [this.cell];
        },
    },
    methods: {
        filterLabel(chip) {
            return i18n.t('base', 'Filter by {value}', { value: chip.text });
        },
    },
};
</script>
