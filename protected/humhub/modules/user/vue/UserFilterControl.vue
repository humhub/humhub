<template>
    <PickerFilterControl
        :filter="filter"
        :model-value="modelValue"
        :input-id="inputId"
        :search="search"
        :resolve="resolve"
        :item-label="itemLabel"
        icon="ti-user"
        block="c-user-filter"
        @update:model-value="$emit('update:modelValue', $event)"
    >
        <template #option="{ item }">
            <UserImage v-bind="imageProps(item)" :size="24" :link="false" />
        </template>
        <template #chip="{ item }">
            <UserImage v-bind="imageProps(item)" :size="18" :link="false" />
        </template>
    </PickerFilterControl>
</template>

<script>
import { apiUrl, client } from '@humhub/vue';
import UserImage from './UserImage.vue';

/**
 * The typing pause before a search — the core `PickerFilterControl`'s `SEARCH_DEBOUNCE_MS`
 * (`FilterBar`'s `TEXT_DEBOUNCE_MS`), mirrored for callers of this component, which cannot
 * import it: module source uses core components by name, it does not bundle them.
 */
export const SEARCH_DEBOUNCE_MS = 300;

/**
 * The control of the `user` filter type of `FilterBar` (`registerFilterType('user',
 * 'UserFilterControl')` in the user module's `vue/index.js`; the PHP side is
 * `humhub\modules\user\components\listing\UserFilter`): one person, searched by name — a list's
 * "Author" or "Assignee". A page renders it by a `user` definition in its bar and needs the user
 * module's Vue bundle (`UserVueAsset` in its asset bundle's `$depends`).
 *
 * The combobox is the core's `PickerFilterControl` (a global component — see there for
 * the keyboard, the resolving of a URL value and the error handling), led by a person icon, with
 * avatars (`.c-user-filter`):
 *
 * - Props: the filter-type contract — `filter` (the definition: `placeholder`, else `label`, as
 *   placeholder; `label` as accessible name), `modelValue` (the user id as a string, `''` for
 *   none — how the bar reads it from the URL), `inputId` (the id of the focusable control).
 *   Emits `update:modelValue` with the chosen user's id as a string, `''` when removed.
 * - Suggestions: `GET /api/v2/user/picker?q=<text>&pageSize=8` — the users the caller may see,
 *   the ones the server accepts as the value; avatar and name.
 * - With a value: a chip (avatar, name, a remove X), a `group` named after the filter's `label`.
 *   A value the control has not seen among its suggestions (from the page URL) is resolved once
 *   with `GET /api/v2/user/picker?ids=<id>`; a value naming nobody the caller may see is emptied.
 *
 * Its messages are of the `base` category (those of `FilterPicker`), which every page with a
 * filter bar loads already — a page embedding it declares no category of the user module.
 *
 * @since 1.20
 */
export default {
    name: 'UserFilterControl',
    // `PickerFilterControl` is a core component, resolved through the global registry.
    components: { UserImage },
    props: {
        filter: { type: Object, required: true },
        modelValue: { type: [String, Number], default: '' },
        inputId: { type: String, default: null },
    },
    emits: ['update:modelValue'],
    methods: {
        search(q, pageSize) {
            return client.get(apiUrl('user/picker', { q, pageSize })).then((response) => response.results || []);
        },
        // One id at most (the control takes a single user): within any page size.
        resolve(ids) {
            return client.get(apiUrl('user/picker', { ids: ids.join(',') })).then((response) => response.results || []);
        },
        itemLabel(user) {
            return user.displayName;
        },
        // What `UserImage` takes of a list item — the item carries more (`title`, `tags` …),
        // which would otherwise fall through to the DOM as attributes.
        imageProps(user) {
            return {
                guid: user.guid,
                displayName: user.displayName,
                url: user.url,
                imageUrl: user.imageUrl,
                contentContainerId: user.contentContainerId ?? null,
            };
        },
    },
};
</script>
