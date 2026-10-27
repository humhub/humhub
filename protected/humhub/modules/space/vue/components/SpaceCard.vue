<template>
    <article class="card entity-card space-card" :class="{ 'is-archived': space.archived }">
        <div class="entity-card-cover" :style="coverStyle">
            <SpaceImage
                class="entity-card-avatar"
                :id="space.id"
                :name="space.name"
                :url="space.url"
                :color="space.color"
                :image-url="space.imageUrl"
                :content-container-id="space.contentContainerId"
                :width="80"
                link
                aria-hidden="true"
                tabindex="-1"
            />
            <span v-if="showFollowers || showMembers" class="badge rounded-pill entity-card-pill">
                <template v-if="showFollowers">
                    <button
                        v-if="canOpenFollowers"
                        type="button"
                        class="entity-card-stat entity-card-stat-followers"
                        :title="followersLabel"
                        :aria-label="followersLabel"
                        @click="openFollowers"
                    ><i class="ti ti-user-check" aria-hidden="true"></i><span>{{ followerCount }}</span></button>
                    <span
                        v-else
                        class="entity-card-stat entity-card-stat-followers"
                        :title="followersLabel"
                    ><i class="ti ti-user-check" aria-hidden="true"></i><span aria-hidden="true">{{ followerCount }}</span><span class="visually-hidden">{{ followersLabel }}</span></span>
                </template>
                <template v-if="showMembers">
                    <button
                        v-if="canOpenMembers"
                        type="button"
                        class="entity-card-stat entity-card-stat-members"
                        :title="membersLabel"
                        :aria-label="membersLabel"
                        @click="openMembers"
                    ><i class="ti ti-users-group" aria-hidden="true"></i><span>{{ memberCount }}</span></button>
                    <span
                        v-else
                        class="entity-card-stat entity-card-stat-members"
                        :title="membersLabel"
                    ><i class="ti ti-users-group" aria-hidden="true"></i><span aria-hidden="true">{{ memberCount }}</span><span class="visually-hidden">{{ membersLabel }}</span></span>
                </template>
            </span>
        </div>

        <div class="entity-card-header">
            <a class="entity-card-title" :href="space.url">{{ space.name }}</a>
            <ExtensionSlot name="space.card-subtitle" :context="{ space }" />
        </div>

        <div class="entity-card-body">
            <p v-if="space.description" class="entity-card-text">{{ space.description }}</p>
            <p v-if="space.archived" class="space-card-archived">
                <i class="ti ti-archive" aria-hidden="true"></i><span>{{ labels.archived }}</span>
            </p>
        </div>

        <div v-if="tags.length" class="entity-card-tags">
            <button
                v-for="tag in tags"
                :key="tag"
                type="button"
                class="badge entity-card-tag"
                :title="filterByLabel(tag)"
                :aria-label="filterByLabel(tag)"
                @click="$emit('filter-tag', tag)"
            >{{ tag }}</button>
        </div>

        <div v-if="state !== null" class="entity-card-footer">
            <button v-if="state === undefined" type="button" class="entity-card-action entity-card-placeholder" :class="buttons.placeholderClass" disabled aria-hidden="true" tabindex="-1">&nbsp;</button>
            <ExtensionSlot v-else name="space.card-actions" :context="actionContext" />
        </div>
    </article>
</template>

<script>
import { i18n, modal, url } from '@humhub/vue';
import SpaceImage from '../SpaceImage.vue';

// As many tags as the server-rendered directory card showed.
const MAX_TAGS = 5;

/**
 * A space of the spaces browser (`SpacesBrowser`), after the HumHub design system v2 space
 * card: the cover (the space's banner, else its colour) with the avatar linking to the space
 * and the stat pill (followers and members — each shown only where the space shows the count,
 * i.e. the count is not `null`; clicking opens the existing follower list
 * (`space/space/follower-list`) or member list (`space/membership/members-list`, only with the
 * state's `canViewMembers`) in the global modal), the name linking to the space, the extension
 * slot `space.card-subtitle`, the description (clamped to three lines), an "Archived" note, the
 * tags (at most five; a click emits `filter-tag`), and the footer: the extension slot
 * `space.card-actions`.
 *
 * - `space`: an item of `GET /api/v2/space` (`SpaceSerializer::list()`).
 * - `state`: the viewer's `space/states` entry for it — `undefined` while the page's states load
 *   (a disabled placeholder button stands in for the footer), `null` when there is none (no
 *   footer), else the actions are fed from it (`membership`, `isMember`, `isFollowing`,
 *   `canFollow`, `followerCount`) without a request of their own. The counts of a loaded state
 *   win over the list item's (they follow the viewer's own follow/join).
 * - `buttons`: the button classes (`buttonClass`, `pendingClass`, `memberClass`,
 *   `togglerClass`, `groupClass` of the `MembershipButton`; `followClass`, `followingClass` of
 *   the `FollowButton`; `placeholderClass`), `icons`: the rendered icon markup (`check`,
 *   `clock`, `user`) — both from `space\widgets\SpacesBrowser`.
 * - Extension slot `space.card-subtitle` (`registerSlotComponent`): a registered component gets
 *   the `space` as prop and renders its own subtitle line (e.g. a category), typically a
 *   `p.entity-card-subtitle`.
 * - Extension slot `space.card-actions`: the footer's actions, the core's own included —
 *   `membership` (sortOrder 100, `SpaceCardMembershipAction`) and `follow` (200,
 *   `SpaceCardFollowAction`) — so a module adds, replaces (its own component under a core id)
 *   or removes (`removeSlotComponent('space.card-actions', 'follow')`) any of them. Each action
 *   gets the context spread as props and renders a `.entity-card-action`, or nothing:
 *   `{space, state, buttons, icons, followerCount, onFollowChange}` — `state` is always a
 *   loaded entry, `followerCount` the count the card shows (the state's, else the item's), the
 *   callback the card's `follow-change` (an action declaring and emitting `follow-change`
 *   reaches it).
 * - Emits `filter-tag` (the tag), `follow-change` (the `FollowButton`'s `change` payload, from
 *   the `follow` action).
 *
 * @since 1.20
 */
export default {
    name: 'SpaceCard',
    i18nCategories: ['SpaceModule.base'],
    // `ExtensionSlot` is a core component (CoreVueAsset), the actions are registered into their
    // slot by `vue/index.js` — all resolved through the global registry.
    components: { SpaceImage },
    props: {
        space: { type: Object, required: true },
        state: { type: Object, default: undefined },
        buttons: { type: Object, default: () => ({}) },
        icons: { type: Object, default: () => ({}) },
    },
    emits: ['filter-tag', 'follow-change'],
    computed: {
        loaded() {
            return this.state !== undefined && this.state !== null;
        },
        followerCount() {
            return this.loaded && this.state.followerCount !== undefined ? this.state.followerCount : this.space.followerCount;
        },
        memberCount() {
            return this.loaded && this.state.memberCount !== undefined ? this.state.memberCount : this.space.memberCount;
        },
        showFollowers() {
            return this.followerCount !== null && this.followerCount !== undefined
                && (!this.loaded || this.state.canViewFollowers !== false);
        },
        showMembers() {
            return this.memberCount !== null && this.memberCount !== undefined;
        },
        canOpenFollowers() {
            return this.loaded && !!this.state.canViewFollowers;
        },
        canOpenMembers() {
            return this.loaded && !!this.state.canViewMembers;
        },
        actionContext() {
            return {
                space: this.space,
                state: this.state,
                buttons: this.buttons,
                icons: this.icons,
                followerCount: this.followerCount ?? null,
                onFollowChange: (payload) => this.$emit('follow-change', payload),
            };
        },
        tags() {
            return (this.space.tags || [])
                .map((tag) => String(tag).trim())
                .filter((tag) => tag !== '')
                .slice(0, MAX_TAGS);
        },
        coverStyle() {
            return this.space.bannerUrl
                ? { backgroundImage: `url(${JSON.stringify(this.space.bannerUrl)})` }
                : {};
        },
        followersLabel() {
            return i18n.t('SpaceModule.base', '{count} Followers', { count: this.followerCount });
        },
        membersLabel() {
            return i18n.t('SpaceModule.base', '{count} Members', { count: this.memberCount });
        },
        labels() {
            return {
                archived: i18n.t('SpaceModule.base', 'Archived'),
            };
        },
    },
    methods: {
        filterByLabel(tag) {
            return i18n.t('SpaceModule.base', 'Filter by {tag}', { tag });
        },
        openFollowers() {
            modal.load(url('space/space/follower-list', { cguid: this.space.guid }));
        },
        openMembers() {
            modal.load(url('space/membership/members-list', { cguid: this.space.guid }));
        },
    },
};
</script>
