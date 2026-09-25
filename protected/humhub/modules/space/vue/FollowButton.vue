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
        @blur="focused = false"
    >
        <span v-if="busy" class="spinner-border spinner-border-sm" aria-hidden="true"></span>
        <span v-else-if="isFollowing" v-html="checkIconHtml"></span>{{ label }}
    </button>
</template>

<script>
/**
 * The follow button of a space — mounted by `space\widgets\FollowButton` (space header, space
 * directory) or used directly by another island (the space directory's card).
 *
 * ## States
 *
 * | state                          | rendered                                            |
 * |--------------------------------|-----------------------------------------------------|
 * | member                         | nothing — members cannot follow (the server's rule) |
 * | `!canFollow && !isFollowing`   | nothing                                             |
 * | not following                  | "Follow" (`followClass`) → `PUT`                    |
 * | following                      | check icon + "Following" (`followingClass`), which  |
 * |                                | reads "Unfollow" on hover/focus → confirm, `DELETE` |
 *
 * "Following" stays available when following was disabled after the fact (`canFollow` false),
 * so an existing follow can still be ended. Every call answers `{isFollowing, followerCount,
 * canFollow}` (`space/<id>/follow`, see `spaceApi.js`), which simply becomes the new state; a
 * failed call is logged (`log.error(response, true)`) and leaves the previous state in place.
 *
 * ## Domain events
 *
 * Membership and following of the same space may be shown by several islands on one page (the
 * header's `MembershipButton` next to this button, several cards). They stay in sync through
 * the `events` bridge instead of touching each other's markup:
 *
 * - `space:follow-changed` `{spaceId, isFollowing, followerCount, canFollow}` — dispatched after
 *   every successful follow/unfollow; every other `FollowButton` of that space takes it over.
 * - `space:membership-changed` `{spaceId, state}` — dispatched by `MembershipButton`. Becoming
 *   a `member` hides this button; leaving the membership refetches the follow state, since
 *   following is available again (unless disabled) and the server knows which.
 *
 * The component itself emits `change` with the same payload as `space:follow-changed`
 * whenever its state changed (own call, event, refetch) — a card uses it for its follower
 * count.
 *
 * @since 1.20
 */
import { events, i18n, log, modal } from '@humhub/vue';
import { fetchFollow, follow, unfollow } from './components/spaceApi.js';

const FOLLOW_CHANGED = 'space:follow-changed';
const MEMBERSHIP_CHANGED = 'space:membership-changed';
const STATE_MEMBER = 'member';

export default {
    i18nCategories: ['SpaceModule.base'],
    props: {
        spaceId: { type: Number, required: true },
        // Used in the unfollow confirmation, as escaped markup (see `spaceNameHtml`).
        spaceName: { type: String, default: '' },
        // `{isFollowing, followerCount, canFollow}`, inlined by the widget. Fetched when absent.
        initial: { type: Object, default: null },
        // Whether the viewer is a member of the space (members cannot follow).
        isMember: { type: Boolean, default: false },
        followClass: { type: String, default: 'btn btn-secondary' },
        followingClass: { type: String, default: 'btn btn-secondary active' },
        // Server-rendered icon markup (the icon provider is pluggable, see `Icon`).
        checkIconHtml: { type: String, default: '' },
    },
    emits: ['change'],
    data() {
        return {
            loaded: !!this.initial,
            isFollowing: this.initial ? !!this.initial.isFollowing : false,
            // `null` where the space does not show its followers - never turned into a 0.
            followerCount: this.initial ? this.initial.followerCount ?? null : null,
            canFollow: this.initial ? !!this.initial.canFollow : false,
            member: this.isMember,
            busy: false,
            hovered: false,
            focused: false,
        };
    },
    computed: {
        visible() {
            return this.loaded && !this.member && (this.canFollow || this.isFollowing);
        },
        label() {
            if (!this.isFollowing) {
                return i18n.t('SpaceModule.base', 'Follow');
            }

            return this.hovered || this.focused
                ? i18n.t('SpaceModule.base', 'Unfollow')
                : i18n.t('SpaceModule.base', 'Following');
        },
        payload() {
            return {
                spaceId: this.spaceId,
                isFollowing: this.isFollowing,
                followerCount: this.followerCount,
                canFollow: this.canFollow,
            };
        },
        spaceNameHtml() {
            return `<strong>${this.escape(this.spaceName)}</strong>`;
        },
    },
    watch: {
        isMember(value) {
            this.member = value;
        },
    },
    created() {
        // Set while this instance dispatches `space:follow-changed`, so it skips its own event.
        this.dispatching = false;

        if (!this.loaded) {
            this.load();
        }
    },
    mounted() {
        events.on(FOLLOW_CHANGED, this.onFollowChanged);
        events.on(MEMBERSHIP_CHANGED, this.onMembershipChanged);
    },
    beforeUnmount() {
        events.off(FOLLOW_CHANGED, this.onFollowChanged);
        events.off(MEMBERSHIP_CHANGED, this.onMembershipChanged);
    },
    methods: {
        load() {
            return fetchFollow(this.spaceId).then((state) => {
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
                return this.mutate(() => follow(this.spaceId));
            }

            return modal.confirm({
                body: i18n.t(
                    'SpaceModule.base',
                    'Would you like to unfollow Space {spaceName}?',
                    { spaceName: this.spaceNameHtml },
                ),
            }).then((confirmed) => (confirmed ? this.mutate(() => unfollow(this.spaceId)) : null));
        },
        mutate(request) {
            if (this.busy) {
                return Promise.resolve();
            }

            this.busy = true;

            return request().then((state) => {
                this.busy = false;
                // The pointer/focus that just followed would otherwise read "Unfollow" at once.
                this.hovered = false;
                this.focused = false;
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
        apply(state) {
            this.isFollowing = !!state.isFollowing;
            this.followerCount = state.followerCount ?? null;
            this.canFollow = !!state.canFollow;
            this.$emit('change', this.payload);
        },
        onFollowChanged(event, payload) {
            if (this.dispatching || !payload || payload.spaceId !== this.spaceId) {
                return;
            }

            this.apply(payload);
            this.loaded = true;
        },
        onMembershipChanged(event, payload) {
            if (!payload || payload.spaceId !== this.spaceId) {
                return;
            }

            if (payload.state === STATE_MEMBER) {
                this.member = true;
                return;
            }

            if (this.member) {
                this.member = false;
                this.load();
            }
        },
        escape(value) {
            const element = document.createElement('div');
            element.textContent = String(value);

            return element.innerHTML;
        },
    },
};
</script>
