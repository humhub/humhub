<template>
    <PickerFilterControl
        :filter="filter"
        :model-value="modelValue"
        :input-id="inputId"
        :multiple="multiple"
        :search="search"
        :resolve="resolve"
        :item-label="itemLabel"
        icon="ti-users-group"
        block="c-space-filter"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <template #option="{ item }">
            <SpaceImage v-bind="imageProps(item)" :width="24" :link="false" />
        </template>
        <template #chip="{ item }">
            <SpaceImage v-bind="imageProps(item)" :width="18" :link="false" />
        </template>
    </PickerFilterControl>
</template>

<script>
import { apiUrl, client } from '@humhub/vue';
import SpaceImage from './SpaceImage.vue';

/**
 * The control of the `space` filter type of `FilterBar` (`registerFilterType('space',
 * 'SpaceFilterControl')` in the space module's `vue/index.js`; the PHP side is
 * `humhub\modules\space\components\listing\SpaceFilter`): one or several spaces, searched by
 * name — a list's "Space". A page renders it by a `space` definition in its bar and needs the
 * space module's Vue bundle (`SpaceVueAsset` in its asset bundle's `$depends`).
 *
 * The combobox is the core's `PickerFilterControl` (a global component — see there for
 * the keyboard, the resolving of a URL value and the error handling), led by a spaces icon, with
 * space images (`.c-space-filter`):
 *
 * - Props: the filter-type contract — `filter` (the definition: `placeholder`, else `label`, as
 *   placeholder; `label` as accessible name; `multiple` — `true` as `FilterBar` reads it, the
 *   PHP filter always sets it —; `props.scope`), `modelValue` (with `multiple` the ids as
 *   strings, `[]` for none, else one id, `''` for none — how the bar reads them from the URL),
 *   `inputId`. Emits `update:modelValue` with the new value in the same shape.
 * - Suggestions: `GET /api/v2/space?purpose=picker&scope=<props.scope, default member>&q=<text>&pageSize=8`
 *   — image and name; with `multiple` the spaces chosen already are not suggested again.
 * - With `multiple` a chip per space (image, name, a remove X), choosing appends; without, one
 *   chip, a `group` named after the filter's `label`, as `UserFilterControl`. Ids the control has
 *   not seen among its suggestions (from the page URL) are resolved with one
 *   `GET /api/v2/space?purpose=picker&scope=all&ids=<ids>&pageSize=<count>` — every space the
 *   server accepts as the value, whatever the suggestions' scope —; ids it does not name are
 *   dropped.
 *
 * Its messages are of the `base` category (those of `FilterPicker`), which every page with a
 * filter bar loads already — a page embedding it declares no category of the space module.
 *
 * @since 1.20
 */
export default {
    name: 'SpaceFilterControl',
    // `PickerFilterControl` is a core component, resolved through the global registry.
    components: { SpaceImage },
    props: {
        filter: { type: Object, required: true },
        modelValue: { type: [String, Number, Array], default: '' },
        inputId: { type: String, default: null },
    },
    emits: ['update:modelValue'],
    computed: {
        multiple() {
            return this.filter.multiple === true;
        },
    },
    methods: {
        search(q, pageSize) {
            const scope = this.filter.props?.scope || 'member';
            return client.get(apiUrl('space', { purpose: 'picker', scope, q, pageSize }))
                .then((response) => response.results || []);
        },
        // Every id on one page: at most `IdsFilter::MAX_IDS` (100), the endpoint's largest page.
        resolve(ids) {
            return client.get(apiUrl('space', { purpose: 'picker', scope: 'all', ids: ids.join(','), pageSize: ids.length }))
                .then((response) => response.results || []);
        },
        itemLabel(space) {
            return space.name;
        },
        // What `SpaceImage` takes of a list item — the item carries more (`description`,
        // `tags` …), which would otherwise fall through to the DOM as attributes.
        imageProps(space) {
            return {
                id: space.id,
                name: space.name,
                url: space.url,
                color: space.color,
                imageUrl: space.imageUrl,
                contentContainerId: space.contentContainerId ?? null,
            };
        },
    },
};
</script>
