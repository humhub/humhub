<template>
    <article class="c-space-card" :class="{ 'is-archived': space.archived }">
        <div class="c-space-card__cover" :style="coverStyle">
            <SpaceImage
                class="c-space-card__avatar"
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
            <span v-if="showFollowers || showMembers" class="c-space-card__pill">
                <template v-if="showFollowers">
                    <button
                        v-if="canOpenFollowers"
                        type="button"
                        class="c-space-card__stat c-space-card__stat--followers"
                        :title="followersLabel"
                        :aria-label="followersLabel"
                        @click="openFollowers"
                    ><i class="ti ti-user-check" aria-hidden="true"></i><span>{{ followerCount }}</span></button>
                    <span
                        v-else
                        class="c-space-card__stat c-space-card__stat--followers"
                        :title="followersLabel"
                    ><i class="ti ti-user-check" aria-hidden="true"></i><span aria-hidden="true">{{ followerCount }}</span><span class="visually-hidden">{{ followersLabel }}</span></span>
                </template>
                <template v-if="showMembers">
                    <button
                        v-if="canOpenMembers"
                        type="button"
                        class="c-space-card__stat c-space-card__stat--members"
                        :title="membersLabel"
                        :aria-label="membersLabel"
                        @click="openMembers"
                    ><i class="ti ti-users-group" aria-hidden="true"></i><span>{{ memberCount }}</span></button>
                    <span
                        v-else
                        class="c-space-card__stat c-space-card__stat--members"
                        :title="membersLabel"
                    ><i class="ti ti-users-group" aria-hidden="true"></i><span aria-hidden="true">{{ memberCount }}</span><span class="visually-hidden">{{ membersLabel }}</span></span>
                </template>
            </span>
        </div>

        <div class="c-space-card__header">
            <a class="c-space-card__title" :href="space.url">{{ space.name }}</a>
            <ExtensionSlot name="space.card-subtitle" :context="{ space }" />
        </div>

        <div class="c-space-card__body">
            <p v-if="space.description" class="c-space-card__text">{{ space.description }}</p>
            <p v-if="space.archived" class="c-space-card__archived">
                <i class="ti ti-archive" aria-hidden="true"></i><span>{{ labels.archived }}</span>
            </p>
        </div>

        <div v-if="tags.length" class="c-space-card__tags">
            <button
                v-for="tag in tags"
                :key="tag"
                type="button"
                class="c-space-card__tag"
                :title="filterByLabel(tag)"
                :aria-label="filterByLabel(tag)"
                @click="$emit('filter-tag', tag)"
            >{{ tag }}</button>
        </div>

        <div v-if="state !== null" class="c-space-card__footer">
            <template v-if="state === undefined">
                <button type="button" class="c-space-card__action c-space-card__placeholder" :class="buttons.placeholderClass" disabled aria-hidden="true" tabindex="-1">&nbsp;</button>
            </template>
            <template v-else>
                <MembershipButton
                    :space-id="space.id"
                    :space-name="space.name"
                    :space-url="space.url"
                    :initial="state.membership || null"
                    :button-class="actionClass(buttons.buttonClass)"
                    :pending-class="actionClass(buttons.pendingClass)"
                    :member-class="actionClass(buttons.memberClass)"
                    :toggler-class="buttons.togglerClass"
                    :group-class="actionClass(buttons.groupClass)"
                    show-member-state
                    :check-icon-html="icons.check || ''"
                    :clock-icon-html="icons.clock || ''"
                    :user-icon-html="icons.user || ''"
                />
                <FollowButton
                    :class="'c-space-card__action'"
                    :space-id="space.id"
                    :space-name="space.name"
                    :initial="followInitial"
                    :is-member="!!state.isMember"
                    :follow-class="buttons.followClass"
                    :following-class="buttons.followingClass"
                    :check-icon-html="icons.check || ''"
                    @change="$emit('follow-change', $event)"
                />
            </template>
        </div>
    </article>
</template>

<script>
import { i18n, modal, url } from '@humhub/vue';
import FollowButton from '../FollowButton.vue';
import MembershipButton from '../MembershipButton.vue';
import SpaceImage from '../SpaceImage.vue';

// As many tags as the server-rendered directory card showed.
const MAX_TAGS = 5;

/**
 * A space of the space directory (`SpaceDirectory`), after the HumHub design system v2 space
 * card: the cover (the space's banner, else its colour) with the avatar linking to the space
 * and the stat pill (followers and members — each shown only where the space shows the count,
 * i.e. the count is not `null`; clicking opens the existing follower list
 * (`space/space/follower-list`) or member list (`space/membership/members-list`, only with the
 * state's `canViewMembers`) in the global modal), the name linking to the space, the extension
 * slot `space.card-subtitle`, the description (clamped to three lines), an "Archived" note, the
 * tags (at most five; a click emits `filter-tag`), and the footer with the space's
 * `MembershipButton` and `FollowButton`.
 *
 * - `space`: an item of `GET /api/v2/space` (`SpaceSerializer::list()`).
 * - `state`: the viewer's `space/states` entry for it — `undefined` while the page's states load
 *   (a disabled placeholder button stands in for the footer), `null` when there is none (no
 *   footer), else the buttons are fed from it (`membership`, `isMember`, `isFollowing`,
 *   `canFollow`, `followerCount`) without a request of their own. The counts of a loaded state
 *   win over the list item's (they follow the viewer's own follow/join).
 * - `buttons`: the button classes (`buttonClass`, `pendingClass`, `memberClass`,
 *   `togglerClass`, `groupClass` of the `MembershipButton`; `followClass`, `followingClass` of
 *   the `FollowButton`; `placeholderClass`), `icons`: the rendered icon markup (`check`,
 *   `clock`, `user`) — both from `space\widgets\SpaceDirectory`.
 * - Extension slot `space.card-subtitle` (`registerSlotComponent`): a registered component gets
 *   the `space` as prop and renders its own subtitle line (e.g. a category), typically a
 *   `p.c-space-card__subtitle`.
 * - Emits `filter-tag` (the tag), `follow-change` (the `FollowButton`'s `change` payload).
 *
 * @since 1.20
 */
export default {
    name: 'SpaceCard',
    i18nCategories: ['SpaceModule.base'],
    // `ExtensionSlot` is a core component, resolved through the global registry (CoreVueAsset).
    components: { FollowButton, MembershipButton, SpaceImage },
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
        followInitial() {
            return {
                isFollowing: !!this.state.isFollowing,
                followerCount: this.followerCount ?? null,
                canFollow: !!this.state.canFollow,
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
        actionClass(classes) {
            return `${classes || ''} c-space-card__action`.trim();
        },
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
