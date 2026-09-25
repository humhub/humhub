<template>
    <div class="c-space-directory">
        <CardDirectory
            ref="directory"
            :url="listUrl"
            :title="labels.title"
            :filters="filters"
            :actions="actions"
            :item-states="itemStates"
            :page-size="24"
            id-prefix="space-filter"
        >
            <template #card="{ item, state }">
                <SpaceCard
                    :space="item"
                    :state="state"
                    :buttons="buttons"
                    :icons="icons"
                    @filter-tag="filterByTag"
                    @follow-change="onFollowChange"
                />
            </template>
            <template #skeleton>
                <SpaceCardSkeleton />
            </template>
        </CardDirectory>
    </div>
</template>

<script>
import { apiUrl, events, i18n, log } from '@humhub/vue';
import SpaceCard from './components/SpaceCard.vue';
import SpaceCardSkeleton from './components/SpaceCardSkeleton.vue';
import { fetchStates } from './components/spaceApi.js';

const MEMBERSHIP_CHANGED = 'space:membership-changed';

/**
 * The spaces directory (`/spaces`), mounted by `space\widgets\SpaceDirectory`: the core
 * `CardDirectory` titled "Spaces", fed by `GET /api/v2/space?purpose=directory` (the platform's
 * space search, see `SpaceListQuery`), with the toolbar actions (`SpaceDirectoryHeadingButtons`
 * as data) and the filters (`SpaceDirectoryFilterSet`). The viewer's states of every loaded
 * page come from one `GET space/states` (`itemStates`) and feed each `SpaceCard`'s membership
 * and follow buttons.
 *
 * - A card's tag sets the search (`setFilter('q', tag)`).
 * - A card's follow change (its `FollowButton`'s `change`) is merged into that space's state and
 *   item, so the card's follower count follows.
 * - A membership change (`space:membership-changed`, dispatched by the `MembershipButton`)
 *   refetches that space's state: membership, the follow offer and the counts all change with it.
 *
 * Props: `filters`, `actions`, `buttons` and `icons` (see `SpaceCard`).
 *
 * @since 1.20
 */
export default {
    name: 'SpaceDirectory',
    i18nCategories: ['SpaceModule.base', 'base'],
    // `CardDirectory` is a core component, resolved through the global registry (CoreVueAsset).
    components: { SpaceCard, SpaceCardSkeleton },
    props: {
        filters: { type: Array, default: () => [] },
        actions: { type: Array, default: () => [] },
        buttons: { type: Object, default: () => ({}) },
        icons: { type: Object, default: () => ({}) },
    },
    computed: {
        listUrl() {
            return apiUrl('space', { purpose: 'directory' });
        },
        labels() {
            return {
                title: i18n.t('SpaceModule.base', 'Spaces'),
            };
        },
    },
    mounted() {
        events.on(MEMBERSHIP_CHANGED, this.onMembershipChanged);
    },
    beforeUnmount() {
        events.off(MEMBERSHIP_CHANGED, this.onMembershipChanged);
    },
    methods: {
        itemStates(ids) {
            return fetchStates(ids);
        },
        filterByTag(tag) {
            this.$refs.directory.setFilter('q', tag);
        },
        onFollowChange(payload) {
            if (!payload) {
                return;
            }
            const directory = this.$refs.directory;
            directory.replaceState(payload.spaceId, {
                isFollowing: payload.isFollowing,
                followerCount: payload.followerCount,
                canFollow: payload.canFollow,
            });
            const item = directory.items.find((candidate) => candidate.id === payload.spaceId);
            if (item && item.followerCount !== payload.followerCount) {
                directory.replaceItem(item.id, { ...item, followerCount: payload.followerCount });
            }
        },
        onMembershipChanged(event, payload) {
            const directory = this.$refs.directory;
            if (!payload || !directory || !directory.items.some((item) => item.id === payload.spaceId)) {
                return;
            }
            fetchStates([payload.spaceId]).then((states) => {
                const state = states[payload.spaceId];
                if (state) {
                    this.$refs.directory?.replaceState(payload.spaceId, state);
                }
            }).catch((response) => {
                log.error(response);
            });
        },
    },
};
</script>
