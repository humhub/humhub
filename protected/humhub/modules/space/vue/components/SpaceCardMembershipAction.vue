<template>
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
</template>

<script>
import MembershipButton from '../MembershipButton.vue';

/**
 * The membership action of a spaces directory card, the core's entry `membership` (sortOrder
 * 100) of the `space.card-actions` slot (registered in `vue/index.js`): the
 * `MembershipButton`, fed from the card's state (`state.membership`) without a request of its
 * own. A membership change reaches the directory as `space:membership-changed`.
 *
 * Receives the `space.card-actions` context (see `SpaceCard`).
 *
 * @since 1.20
 */
export default {
    name: 'SpaceCardMembershipAction',
    components: { MembershipButton },
    // The whole context is spread onto the component; what it does not use must not become
    // attributes of the button.
    inheritAttrs: false,
    props: {
        space: { type: Object, required: true },
        state: { type: Object, required: true },
        buttons: { type: Object, default: () => ({}) },
        icons: { type: Object, default: () => ({}) },
    },
    methods: {
        actionClass(classes) {
            return `${classes || ''} c-entity-card__action`.trim();
        },
    },
};
</script>
