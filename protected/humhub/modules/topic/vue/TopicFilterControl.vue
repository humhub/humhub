<template>
    <PickerFilterControl
        :filter="filter"
        :model-value="modelValue"
        :input-id="inputId"
        :multiple="true"
        :search="search"
        :resolve="resolve"
        :item-label="itemLabel"
        :item-title="itemTitle"
        block="topic-filter"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <template #option="{ item }">
            <i class="ti ti-star topic-filter-star" :style="starStyle(item)" aria-hidden="true"></i>
        </template>
        <template #option-detail="{ item }">
            <span v-if="isForeign(item)" class="topic-filter-container">{{ item.container.name }}</span>
        </template>
        <!-- In the chip's own colour: a topic's colour may not read on the chip's background -->
        <template #chip>
            <i class="ti ti-star topic-filter-star" aria-hidden="true"></i>
        </template>
    </PickerFilterControl>
</template>

<script>
import { apiUrl, client } from '@humhub/vue';

/**
 * The control of the `topic` filter type of `FilterBar` (`registerFilterType('topic',
 * 'TopicFilterControl')` in the topic module's `vue/index.js`; the PHP side is
 * `humhub\modules\topic\components\listing\TopicFilter`): one or several topics, searched by
 * name — a content list's "Topic". A page renders it by a `topic` definition in its bar and
 * needs the topic module's Vue bundle (`TopicVueAsset` in its asset bundle's `$depends`).
 *
 * The combobox is the core's `PickerFilterControl` (a global component — see there for the
 * keyboard, the resolving of a URL value and the error handling), with the topic module's
 * star per topic (`.topic-filter`):
 *
 * - Props: the filter-type contract — `filter` (the definition: `placeholder`, else `label`, as
 *   placeholder; `label` as accessible name; always `multiple`, as the PHP filter sets it;
 *   `props.containerId`), `modelValue` (the ids as strings, `[]` for none — how the bar reads
 *   them from the URL), `inputId`. Emits `update:modelValue` with the new ids.
 * - Suggestions: `GET /api/v2/topic/picker?q=<text>&pageSize=8[&containerId=<props.containerId>]`
 *   — with a container its topics and the global ones. A suggestion is a star in the topic's
 *   colour (a neutral one without a colour), its name and, for a topic of another container than the
 *   filter's (every container's, without one), that container's name, muted.
 * - A chip per topic (a star in the chip's colour, name, a remove X; a topic of a foreign container names it in the
 *   chip's `title`), choosing appends. Ids the control has not seen
 *   among its suggestions (from the page URL) are resolved with one
 *   `GET /api/v2/topic/picker?ids=<ids>[&containerId=…]&pageSize=<count>` — at most 20, as the
 *   PHP filter takes —; ids it does not name are dropped.
 *
 * Its messages are of the `base` category (those of `FilterPicker`), which every page with a
 * filter bar loads already — a page embedding it declares no category of the topic module.
 *
 * @since 1.20
 */
export default {
    name: 'TopicFilterControl',
    props: {
        filter: { type: Object, required: true },
        modelValue: { type: [String, Number, Array], default: () => [] },
        inputId: { type: String, default: null },
    },
    emits: ['update:modelValue'],
    computed: {
        containerId() {
            return this.filter.props?.containerId ?? null;
        },
    },
    methods: {
        params(params) {
            return this.containerId === null ? params : { ...params, containerId: this.containerId };
        },
        search(q, pageSize) {
            return client.get(apiUrl('topic/picker', this.params({ q, pageSize })))
                .then((response) => response.results || []);
        },
        resolve(ids) {
            return client.get(apiUrl('topic/picker', { ids: ids.join(','), ...this.params({}), pageSize: ids.length }))
                .then((response) => response.results || []);
        },
        itemLabel(topic) {
            return topic.name;
        },
        // A chip names a foreign container only in its tooltip: the chip stays short.
        itemTitle(topic) {
            return this.isForeign(topic) ? `${topic.name} (${topic.container.name})` : null;
        },
        // Without a colour the star keeps the neutral colour of its class.
        starStyle(topic) {
            return topic.color ? { color: topic.color } : null;
        },
        isForeign(topic) {
            return !!topic.container && String(topic.container.id) !== String(this.containerId);
        },
    },
};
</script>
