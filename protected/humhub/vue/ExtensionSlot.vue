<template>
    <component
        v-for="entry in visibleEntries"
        :is="entry.component"
        :key="entry.component"
        v-bind="context"
    />
</template>

<script>
/**
 * The Vue analog of PHP widget stacks (`\humhub\widgets\BaseStack` and friends): renders a
 * named extension point that other modules hook into without forking the host component's
 * template.
 *
 * ```html
 * <!-- inside CommentEntry.vue -->
 * <ExtensionSlot name="comment.links" :context="{ comment }" />
 * ```
 *
 * ```js
 * // another module's own vue/index.js
 * import { registerSlotComponent } from '@humhub/vue';
 * registerSlotComponent('comment.links', 'MyReactionButton');
 * ```
 *
 * ## Naming convention
 *
 * Slot names follow `<module>.<region>` (e.g. `comment.controls`, `comment.links`) — the
 * same "who owns this extension point" convention PHP stack widget names already use.
 *
 * ## Silent when empty
 *
 * A slot with no registrations, or whose only registrations are for components that are
 * not (yet) registered, renders nothing — no placeholder, no warning. Modules stay
 * optional: a host component's template does not need to know or care whether anything
 * extends it.
 *
 * ## Reactivity
 *
 * Entries appear automatically once both halves of a registration are in place, without
 * the host island remounting — the established "late artifact" tolerance the whole Vue
 * island runtime is built on (see docs/develop/ui-js-vuejs.md and humhub.vue.js's own
 * top-of-file docblock):
 *
 * - `getSlotComponents()` reads from `registerSlotComponent()`'s own registry, which is a
 *   genuine `Vue.reactive()` store — a slot gaining an entry (even its very first one)
 *   invalidates every computed that has read it, this component's `visibleEntries` below
 *   included.
 * - The component REGISTRY (`register()`'s `components` map in humhub.vue.js) is a plain
 *   object, not reactive — reading it directly would not trigger `visibleEntries` to
 *   re-evaluate once a not-yet-registered entry's component finally registers. humhub.vue.js
 *   solves this with a small reactive generation counter bumped once per `register()` call;
 *   its exported `isRegistered()` reads that counter for its dependency-tracking side effect
 *   before returning the (non-reactive) lookup — see its own declaration comment. Reading
 *   `isRegistered()` from inside this computed is what makes late component registration
 *   visible here too, not just late slot registration.
 *
 * @since 1.19
 */
import { getSlotComponents, isRegistered } from '@humhub/vue';

export default {
    name: 'ExtensionSlot',
    props: {
        name: { type: String, required: true },
        context: { type: Object, default: () => ({}) },
    },
    computed: {
        visibleEntries() {
            return getSlotComponents(this.name).filter((entry) => isRegistered(entry.component));
        },
    },
};
</script>
