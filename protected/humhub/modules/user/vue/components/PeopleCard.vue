<template>
    <article class="c-entity-card c-people-card">
        <div class="c-entity-card__cover" :style="coverStyle">
            <UserImage
                class="c-entity-card__avatar"
                :guid="user.guid"
                :display-name="user.displayName"
                :url="user.url"
                :image-url="user.imageUrl"
                :content-container-id="user.contentContainerId"
                :online="onlineStatus"
                :size="80"
                link
                aria-hidden="true"
                tabindex="-1"
            />
            <span v-if="stat" class="c-entity-card__pill">
                <button
                    type="button"
                    class="c-entity-card__stat"
                    :class="`c-entity-card__stat--${stat.kind}`"
                    :title="stat.label"
                    :aria-label="stat.label"
                    @click="openStat"
                ><i class="ti" :class="stat.icon" aria-hidden="true"></i><span>{{ stat.count }}</span></button>
            </span>
        </div>

        <div class="c-entity-card__header">
            <a class="c-entity-card__title" :href="user.url">{{ user.displayName }}</a>
            <p v-if="user.title" class="c-entity-card__subtitle">{{ user.title }}</p>
            <ExtensionSlot name="user.card-subtitle" :context="{ user }" />
        </div>

        <div class="c-entity-card__body">
            <p v-for="(detail, index) in details" :key="index" class="c-people-card__detail" :title="detail">{{ detail }}</p>
        </div>

        <div v-if="tags.length" class="c-entity-card__tags">
            <button
                v-for="tag in tags"
                :key="tag"
                type="button"
                class="c-entity-card__tag"
                :title="filterByLabel(tag)"
                :aria-label="filterByLabel(tag)"
                @click="$emit('filter-tag', tag)"
            >{{ tag }}</button>
        </div>

        <div v-if="showFooter" class="c-entity-card__footer">
            <button v-if="state === undefined" type="button" class="c-entity-card__action c-entity-card__placeholder" :class="buttons.placeholderClass" disabled aria-hidden="true" tabindex="-1">&nbsp;</button>
            <ExtensionSlot v-else name="user.card-actions" :context="actionContext" />
        </div>
    </article>
</template>

<script>
import { i18n, modal, url } from '@humhub/vue';
import UserImage from '../UserImage.vue';

// As many tags as the space card shows.
const MAX_TAGS = 5;

/**
 * A user of the People directory (`PeopleDirectory`), after the HumHub design system v2 people
 * card, on the shared entity card (`_entity-card.scss`): the cover (the user's banner, else
 * plain grey) with the avatar linking to the profile and the stat pill (the friends while the
 * friendship system is on, else the followers — clicking opens the existing friend list
 * (`friendship/list/popup`) or follower list (`user/profile/follower-list`) in the global
 * modal), the name linking to the profile, the `title` line and the extension slot
 * `user.card-subtitle`, the detail lines (the administrator's People card fields), the tags (at
 * most five; a click emits `filter-tag`), and the footer: the extension slot
 * `user.card-actions`. One's own card has no footer. The avatar carries the online status
 * (`state.isOnline`, see `UserImage`'s `online`) once the state is loaded and tells one.
 *
 * - `user`: an item of `GET /api/v2/user` (`UserSerializer::list()`).
 * - `state`: the viewer's `user/states` entry for it — `undefined` while the page's states load
 *   (a disabled placeholder button stands in for the footer), `null` when there is none (no
 *   footer), else the actions are fed from it without a request of their own.
 * - `followEnabled`, `friendshipEnabled`: which of the two the platform offers.
 * - `buttons`: the button classes (`friendClass`, `friendStateClass`, `friendTogglerClass`,
 *   `friendGroupClass` of the `FriendshipButton`; `followClass`, `followingClass` of the
 *   `UserFollowButton`; `placeholderClass`), `icons`: the rendered icon markup (`check`, `plus`,
 *   `clock`, `times`) — both from `user\widgets\PeopleDirectory`.
 * - Extension slot `user.card-subtitle` (props: `user`): a subtitle line of its own, typically
 *   a `p.c-entity-card__subtitle`.
 * - Extension slot `user.card-actions`: the footer's actions, the core's own included —
 *   `friendship` (sortOrder 100, the friendship module's `PeopleCardFriendshipAction`) and
 *   `follow` (200, `PeopleCardFollowAction`) — so a module adds
 *   (`registerSlotComponent('user.card-actions', 'MailCardAction', {id: 'mail', sortOrder:
 *   300})`), replaces (its own component under a core id) or removes
 *   (`removeSlotComponent('user.card-actions', 'follow')`) any of them. Each action gets the
 *   context spread as props and renders a `.c-entity-card__action`, or nothing:
 *   `{user, state, buttons, icons, followEnabled, friendshipEnabled, onFollowChange,
 *   onFriendshipChange}` — `state` is always a loaded entry of another user; the two callbacks
 *   are the card's `follow-change`/`friendship-change` (an action declaring and emitting
 *   `follow-change` reaches `onFollowChange`).
 * - Emits `filter-tag` (the tag), `follow-change` (the `UserFollowButton`'s `change` payload),
 *   `friendship-change` (the `FriendshipButton`'s) — the latter two from the actions.
 *
 * @since 1.20
 */
export default {
    name: 'PeopleCard',
    i18nCategories: ['UserModule.base'],
    // `ExtensionSlot` is a core component, the actions are registered by their modules — all
    // resolved through the global registry.
    components: { UserImage },
    props: {
        user: { type: Object, required: true },
        state: { type: Object, default: undefined },
        buttons: { type: Object, default: () => ({}) },
        icons: { type: Object, default: () => ({}) },
        followEnabled: { type: Boolean, default: false },
        friendshipEnabled: { type: Boolean, default: false },
    },
    emits: ['filter-tag', 'follow-change', 'friendship-change'],
    computed: {
        showFooter() {
            return this.state !== null && !(this.state && this.state.isSelf);
        },
        actionContext() {
            return {
                user: this.user,
                state: this.state,
                buttons: this.buttons,
                icons: this.icons,
                followEnabled: this.followEnabled,
                friendshipEnabled: this.friendshipEnabled,
                onFollowChange: (payload) => this.$emit('follow-change', payload),
                onFriendshipChange: (payload) => this.$emit('friendship-change', payload),
            };
        },
        onlineStatus() {
            return this.state && typeof this.state.isOnline === 'boolean' ? this.state.isOnline : null;
        },
        stat() {
            const friends = this.user.friendCount;
            if (this.friendshipEnabled && friends !== null && friends !== undefined) {
                return {
                    kind: 'friends',
                    icon: 'ti-users',
                    count: friends,
                    label: i18n.t('UserModule.base', '{count} Friends', { count: friends }),
                };
            }
            const followers = this.user.followerCount;
            if (followers !== null && followers !== undefined) {
                return {
                    kind: 'followers',
                    icon: 'ti-user-check',
                    count: followers,
                    label: i18n.t('UserModule.base', '{count} Followers', { count: followers }),
                };
            }

            return null;
        },
        details() {
            return (this.user.details || []).filter((detail) => String(detail).trim() !== '');
        },
        tags() {
            return (this.user.tags || [])
                .map((tag) => String(tag).trim())
                .filter((tag) => tag !== '')
                .slice(0, MAX_TAGS);
        },
        coverStyle() {
            return this.user.bannerUrl
                ? { backgroundImage: `url(${JSON.stringify(this.user.bannerUrl)})` }
                : {};
        },
    },
    methods: {
        filterByLabel(tag) {
            return i18n.t('UserModule.base', 'Filter by {tag}', { tag });
        },
        openStat() {
            if (this.stat.kind === 'friends') {
                modal.load(url('friendship/list/popup', { userId: this.user.id }));
            } else {
                modal.load(url('user/profile/follower-list', { cguid: this.user.guid }));
            }
        },
    },
};
</script>
