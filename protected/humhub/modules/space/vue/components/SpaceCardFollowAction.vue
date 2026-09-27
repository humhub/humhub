<template>
    <FollowButton
        class="c-entity-card__action"
        :space-id="space.id"
        :space-name="space.name"
        :initial="initial"
        :is-member="!!state.isMember"
        :follow-class="buttons.followClass"
        :following-class="buttons.followingClass"
        :check-icon-html="icons.check || ''"
        @change="$emit('follow-change', $event)"
    />
</template>

<script>
import FollowButton from '../FollowButton.vue';

/**
 * The follow action of a spaces directory card, the core's entry `follow` (sortOrder 200) of
 * the `space.card-actions` slot (registered in `vue/index.js`): the space's `FollowButton`,
 * fed from the card's state without a request of its own.
 *
 * Receives the `space.card-actions` context (see `SpaceCard`) and emits `follow-change` with
 * the button's `change` payload — the context's `onFollowChange`.
 *
 * @since 1.20
 */
export default {
    name: 'SpaceCardFollowAction',
    components: { FollowButton },
    // The whole context is spread onto the component; what it does not use must not become
    // attributes of the button.
    inheritAttrs: false,
    props: {
        space: { type: Object, required: true },
        state: { type: Object, required: true },
        buttons: { type: Object, default: () => ({}) },
        icons: { type: Object, default: () => ({}) },
        followerCount: { type: Number, default: null },
    },
    emits: ['follow-change'],
    computed: {
        initial() {
            return {
                isFollowing: !!this.state.isFollowing,
                followerCount: this.followerCount ?? null,
                canFollow: !!this.state.canFollow,
            };
        },
    },
};
</script>
