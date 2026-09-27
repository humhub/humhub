<template>
    <div class="c-people-directory">
        <CardDirectory
            ref="directory"
            :url="listUrl"
            :title="labels.title"
            :filters="filters"
            :actions="actions"
            :item-states="itemStates"
            :page-size="24"
            id-prefix="people-filter"
        >
            <template #card="{ item, state }">
                <PeopleCard
                    :user="item"
                    :state="state"
                    :buttons="buttons"
                    :icons="icons"
                    :follow-enabled="followEnabled"
                    :friendship-enabled="friendshipEnabled"
                    @filter-tag="filterByTag"
                    @follow-change="onFollowChange"
                    @friendship-change="onFriendshipChange"
                />
            </template>
            <template #skeleton>
                <PeopleCardSkeleton />
            </template>
        </CardDirectory>
    </div>
</template>

<script>
import { apiUrl, i18n } from '@humhub/vue';
import PeopleCard from './components/PeopleCard.vue';
import PeopleCardSkeleton from './components/PeopleCardSkeleton.vue';
import { fetchStates } from './components/userApi.js';

const STATE_FRIENDS = 'friends';

/**
 * The People directory (`/people`), mounted by `user\widgets\PeopleDirectory`: the core
 * `CardDirectory` titled "People", fed by `GET /api/v2/user?purpose=directory` (the platform's
 * user search, see `UserList`), with the toolbar actions (`PeopleHeadingButtons` as data)
 * and the filters (`UserList::definitions()`). The viewer's states of every loaded page come
 * from one `GET user/states` (`itemStates`) and feed each `PeopleCard`'s friendship and follow
 * buttons.
 *
 * - A card's tag adds itself to the tag picker (`setFilter('tag', [tag], { add: true })`), or
 *   sets the search where there is no tag filter.
 * - A card's follow change (its `UserFollowButton`'s `change`, which accompanies
 *   `user:follow-changed`) is merged into that user's state and item, so the card's follower
 *   count follows.
 * - A card's friendship change (its `FriendshipButton`'s `change`, which accompanies
 *   `user:friendship-changed`) is merged into that user's state, and the friend count follows
 *   a friendship that began or ended. The follow the friendship brought along reaches the card
 *   as a follow change: the `UserFollowButton` refetches its state on the domain event.
 *
 * Props: `filters`, `actions`, `buttons`, `icons`, `followEnabled`, `friendshipEnabled` (see
 * `PeopleCard`).
 *
 * @since 1.20
 */
export default {
    name: 'PeopleDirectory',
    i18nCategories: ['UserModule.base', 'FriendshipModule.base', 'base'],
    // `CardDirectory` is a core component, resolved through the global registry (CoreVueAsset).
    components: { PeopleCard, PeopleCardSkeleton },
    props: {
        filters: { type: Array, default: () => [] },
        actions: { type: Array, default: () => [] },
        buttons: { type: Object, default: () => ({}) },
        icons: { type: Object, default: () => ({}) },
        followEnabled: { type: Boolean, default: false },
        friendshipEnabled: { type: Boolean, default: false },
    },
    computed: {
        listUrl() {
            return apiUrl('user', { purpose: 'directory' });
        },
        labels() {
            return {
                title: i18n.t('UserModule.base', 'People'),
            };
        },
    },
    methods: {
        itemStates(ids) {
            return fetchStates(ids);
        },
        filterByTag(tag) {
            // No tag picker while no user has a tag: fall back to the search.
            if (!this.$refs.directory.setFilter('tag', [tag], { add: true })) {
                this.$refs.directory.setFilter('q', tag);
            }
        },
        onFollowChange(payload) {
            if (!payload) {
                return;
            }
            const directory = this.$refs.directory;
            directory.replaceState(payload.userId, {
                isFollowing: payload.isFollowing,
                canFollow: payload.canFollow,
            });
            const item = directory.items.find((candidate) => candidate.id === payload.userId);
            if (item && payload.followerCount !== null && item.followerCount !== payload.followerCount) {
                directory.replaceItem(item.id, { ...item, followerCount: payload.followerCount });
            }
        },
        onFriendshipChange(payload) {
            if (!payload) {
                return;
            }
            const directory = this.$refs.directory;
            const previous = directory.states[payload.userId]?.friendship?.state ?? null;
            directory.replaceState(payload.userId, {
                friendship: { state: payload.state, isFollowing: payload.isFollowing },
            });

            const wasFriend = previous === STATE_FRIENDS;
            const isFriend = payload.state === STATE_FRIENDS;
            const item = directory.items.find((candidate) => candidate.id === payload.userId);
            if (item && wasFriend !== isFriend && item.friendCount !== null && item.friendCount !== undefined) {
                directory.replaceItem(item.id, { ...item, friendCount: Math.max(0, item.friendCount + (isFriend ? 1 : -1)) });
            }
        },
    },
};
</script>
