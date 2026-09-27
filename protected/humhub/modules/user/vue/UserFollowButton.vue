<template>
    <button
        v-if="visible"
        type="button"
        :class="isFollowing ? followingClass : followClass"
        :disabled="busy"
        :aria-pressed="isFollowing ? 'true' : 'false'"
        :aria-busy="busy ? 'true' : null"
        @click="toggle"
        @mouseenter="hovered = true"
        @mouseleave="hovered = false"
        @focus="focused = true"
        @blur="onBlur"
    >
        <span v-if="busy" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        <span v-else-if="isFollowing" v-html="checkIconHtml"></span>
        <span v-else-if="followIconHtml" v-html="followIconHtml"></span>{{ label }}
    </button>
</template>

<script>
/**
 * The follow button of a user — mounted by `user\widgets\UserFollowButton` (profile header,
 * as a button or as the entry of its controls menu, `HeaderControlsMenu`) or used directly by
 * another island (the People directory's card). The user counterpart of the space's
 * `FollowButton`.
 *
 * ## States
 *
 * | state                          | rendered                                            |
 * |--------------------------------|-----------------------------------------------------|
 * | `!canFollow && !isFollowing`   | nothing (oneself, following disabled)               |
 * | not following                  | "Follow" (`followClass`) → `PUT`                    |
 * | following                      | check icon + "Following" (`followingClass`), which  |
 * |                                | reads "Unfollow" on hover/focus → confirm, `DELETE` |
 *
 * "Following" stays available when following was disabled after the fact (`canFollow` false),
 * so an existing follow can still be ended. Every call answers `{isFollowing, followerCount,
 * canFollow}` (`user/<id>/follow`, see `components/userApi.js`), which simply becomes the new
 * state; a failed call is logged (`log.error(response, true)`) and leaves the previous state in
 * place.
 *
 * ## Domain events
 *
 * Following and friendship of the same user may be shown by several islands on one page. They
 * stay in sync through the `events` bridge instead of touching each other's markup:
 *
 * - `user:follow-changed` `{userId, isFollowing, followerCount, canFollow}` — dispatched after
 *   every successful follow/unfollow; every other `UserFollowButton` of that user takes it over.
 * - `user:friendship-changed` `{userId, state, isFollowing}` — dispatched by the friendship
 *   module's `FriendshipButton`. Sending or accepting a request makes the viewer follow the
 *   other user (`Friendship::add()`): when its `isFollowing` differs from the shown state, the
 *   follow state is refetched (the follower count changed with it, and the server knows it).
 *
 * The component itself emits `change` with the same payload as `user:follow-changed` whenever
 * its state changed (own call, event, refetch) — a card uses it for its follower count.
 *
 * @since 1.20
 */
import { events, i18n, log, modal } from '@humhub/vue';
import { fetchFollow, follow, unfollow } from './components/userApi.js';

const FOLLOW_CHANGED = 'user:follow-changed';
const FRIENDSHIP_CHANGED = 'user:friendship-changed';

export default {
    i18nCategories: ['UserModule.base', 'SpaceModule.base'],
    props: {
        userId: { type: Number, required: true },
        // Used in the unfollow confirmation, as escaped markup (see `userNameHtml`).
        userName: { type: String, default: '' },
        // `{isFollowing, followerCount, canFollow}`, inlined by the widget. Fetched when absent.
        initial: { type: Object, default: null },
        followClass: { type: String, default: 'btn btn-primary' },
        followingClass: { type: String, default: 'btn btn-outline-primary' },
        // Server-rendered icon markup (the icon provider is pluggable, see `Icon`): the check of
        // the "Following" state, and an optional icon of the "Follow" state.
        checkIconHtml: { type: String, default: '' },
        followIconHtml: { type: String, default: '' },
    },
    emits: ['change'],
    data() {
        return {
            loaded: !!this.initial,
            isFollowing: this.initial ? !!this.initial.isFollowing : false,
            // `null` while following is disabled - never turned into a 0.
            followerCount: this.initial ? this.initial.followerCount ?? null : null,
            canFollow: this.initial ? !!this.initial.canFollow : false,
            busy: false,
            hovered: false,
            focused: false,
        };
    },
    computed: {
        visible() {
            return this.loaded && (this.canFollow || this.isFollowing);
        },
        label() {
            if (!this.isFollowing) {
                return i18n.t('UserModule.base', 'Follow');
            }

            return this.hovered || this.focused
                ? i18n.t('UserModule.base', 'Unfollow')
                : i18n.t('UserModule.base', 'Following');
        },
        payload() {
            return {
                userId: this.userId,
                isFollowing: this.isFollowing,
                followerCount: this.followerCount,
                canFollow: this.canFollow,
            };
        },
        userNameHtml() {
            return `<strong>${this.escape(this.userName)}</strong>`;
        },
    },
    created() {
        // Set while this instance dispatches `user:follow-changed`, so it skips its own event.
        this.dispatching = false;

        if (!this.loaded) {
            this.load();
        }
    },
    mounted() {
        events.on(FOLLOW_CHANGED, this.onFollowChanged);
        events.on(FRIENDSHIP_CHANGED, this.onFriendshipChanged);
    },
    beforeUnmount() {
        events.off(FOLLOW_CHANGED, this.onFollowChanged);
        events.off(FRIENDSHIP_CHANGED, this.onFriendshipChanged);
    },
    methods: {
        load() {
            return fetchFollow(this.userId).then((state) => {
                this.apply(state);
                this.loaded = true;
            }).catch((response) => {
                // Without a state there is nothing sensible to offer — render nothing.
                log.error(response, true);
            });
        },
        toggle() {
            if (this.busy) {
                return Promise.resolve();
            }

            if (!this.isFollowing) {
                return this.mutate(() => follow(this.userId));
            }

            // The message of the server-rendered button this island replaces.
            return modal.confirm({
                body: i18n.t(
                    'SpaceModule.base',
                    'Would you like to unfollow {userName}?',
                    { userName: this.userNameHtml },
                ),
            }).then((confirmed) => {
                // The pointer is on the dialog now, and the button (e.g. in a dropdown that
                // closed meanwhile) gets no `mouseleave` - it would keep reading "Unfollow".
                this.resetHover();

                return confirmed ? this.mutate(() => unfollow(this.userId)) : null;
            });
        },
        mutate(request) {
            if (this.busy) {
                return Promise.resolve();
            }

            this.busy = true;

            return request().then((state) => {
                this.busy = false;
                // The pointer/focus that just followed would otherwise read "Unfollow" at once.
                this.resetHover();
                this.apply(state);
                this.dispatching = true;
                try {
                    events.trigger(FOLLOW_CHANGED, [this.payload]);
                } finally {
                    this.dispatching = false;
                }
            }).catch((response) => {
                // Nothing was applied yet, so the previous state is still what is shown.
                this.busy = false;
                log.error(response, true);
            });
        },
        // Losing the focus also ends the hover: a dropdown entry is hidden with its menu, which
        // takes the focus with it, without a `mouseleave`.
        onBlur() {
            this.focused = false;
            this.hovered = false;
        },
        resetHover() {
            this.hovered = false;
            this.focused = false;
        },
        apply(state) {
            this.isFollowing = !!state.isFollowing;
            this.followerCount = state.followerCount ?? null;
            this.canFollow = !!state.canFollow;
            this.$emit('change', this.payload);
        },
        onFollowChanged(event, payload) {
            if (this.dispatching || !payload || payload.userId !== this.userId) {
                return;
            }

            this.apply(payload);
            this.loaded = true;
        },
        onFriendshipChanged(event, payload) {
            if (!payload || payload.userId !== this.userId || !!payload.isFollowing === this.isFollowing) {
                return;
            }

            this.load();
        },
        escape(value) {
            const element = document.createElement('div');
            element.textContent = String(value);

            return element.innerHTML;
        },
    },
};
</script>
