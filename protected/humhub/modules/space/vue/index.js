/**
 * The space module's Vue entry (see "Module file layout" in docs/develop/ui-js-vuejs-components.md).
 *
 * Registers every top-level component under its filename, as the generated entry does, plus
 * the core's own entries of the space card's `space.card-actions` slot — registered like any
 * module's, so a module can reorder, replace or remove them by id (see `SpaceCard`) — and the
 * `space` filter type of `FilterBar` (`SpaceFilterControl`, the control of a `SpaceFilter`).
 */
import { register, registerFilterType, registerSlotComponent } from '@humhub/vue';
import SpaceCardFollowAction from './components/SpaceCardFollowAction.vue';
import SpaceCardMembershipAction from './components/SpaceCardMembershipAction.vue';

Object.entries(import.meta.glob('./*.vue', { eager: true, import: 'default' })).forEach(([path, component]) => {
    register(path.slice('./'.length, -'.vue'.length), component);
});

register('SpaceCardMembershipAction', SpaceCardMembershipAction);
register('SpaceCardFollowAction', SpaceCardFollowAction);
registerSlotComponent('space.card-actions', 'SpaceCardMembershipAction', { id: 'membership', sortOrder: 100 });
registerSlotComponent('space.card-actions', 'SpaceCardFollowAction', { id: 'follow', sortOrder: 200 });

registerFilterType('space', 'SpaceFilterControl');
