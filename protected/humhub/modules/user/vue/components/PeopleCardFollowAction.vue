<template>
    <UserFollowButton
        v-if="followEnabled || state.isFollowing"
        class="c-entity-card__action"
        :user-id="user.id"
        :user-name="user.displayName"
        :initial="initial"
        :follow-class="buttons.followClass"
        :following-class="buttons.followingClass"
        :check-icon-html="icons.check || ''"
        @change="$emit('follow-change', $event)"
    />
</template>

<script>
import UserFollowButton from '../UserFollowButton.vue';

/**
 * The follow action of a People directory card, the core's entry `follow` (sortOrder 200) of
 * the `user.card-actions` slot (registered in `vue/index.js`): the `UserFollowButton`, fed from
 * the card's state without a request of its own. Rendered while following is enabled, and
 * while the viewer follows the user after following was disabled, so that follow can still be
 * ended.
 *
 * Receives the `user.card-actions` context (see `PeopleCard`) and emits `follow-change` with
 * the button's `change` payload — the context's `onFollowChange`.
 *
 * @since 1.20
 */
export default {
    name: 'PeopleCardFollowAction',
    components: { UserFollowButton },
    // The whole context is spread onto the component; what it does not use must not become
    // attributes of the button.
    inheritAttrs: false,
    props: {
        user: { type: Object, required: true },
        state: { type: Object, required: true },
        buttons: { type: Object, default: () => ({}) },
        icons: { type: Object, default: () => ({}) },
        followEnabled: { type: Boolean, default: false },
    },
    emits: ['follow-change'],
    computed: {
        initial() {
            return {
                isFollowing: !!this.state.isFollowing,
                followerCount: this.user.followerCount ?? null,
                canFollow: !!this.state.canFollow,
            };
        },
    },
};
</script>
