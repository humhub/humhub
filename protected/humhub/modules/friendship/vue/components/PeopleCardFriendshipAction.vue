<template>
    <FriendshipButton
        v-if="friendshipEnabled && state.friendship"
        :user-id="user.id"
        :user-name="user.displayName"
        :initial="state.friendship"
        :button-class="actionClass(buttons.friendClass)"
        :state-class="actionClass(buttons.friendStateClass)"
        :toggler-class="buttons.friendTogglerClass"
        :group-class="actionClass(buttons.friendGroupClass)"
        :check-icon-html="icons.check || ''"
        :plus-icon-html="icons.plus || ''"
        :clock-icon-html="icons.clock || ''"
        :times-icon-html="icons.times || ''"
        @change="$emit('friendship-change', $event)"
    />
</template>

<script>
import FriendshipButton from '../FriendshipButton.vue';

/**
 * The friendship action of a People directory card, the friendship module's entry
 * `friendship` (sortOrder 100) of the `user.card-actions` slot (registered in `vue/index.js`):
 * the `FriendshipButton`, fed from the card's state (`state.friendship`) without a request of
 * its own. Rendered while the friendship system is on.
 *
 * Receives the `user.card-actions` context (see the user module's `PeopleCard`) and emits
 * `friendship-change` with the button's `change` payload — the context's `onFriendshipChange`.
 *
 * @since 1.20
 */
export default {
    name: 'PeopleCardFriendshipAction',
    components: { FriendshipButton },
    // The whole context is spread onto the component; what it does not use must not become
    // attributes of the button.
    inheritAttrs: false,
    props: {
        user: { type: Object, required: true },
        state: { type: Object, required: true },
        buttons: { type: Object, default: () => ({}) },
        icons: { type: Object, default: () => ({}) },
        friendshipEnabled: { type: Boolean, default: false },
    },
    emits: ['friendship-change'],
    methods: {
        actionClass(classes) {
            return `${classes || ''} c-entity-card__action`.trim();
        },
    },
};
</script>
