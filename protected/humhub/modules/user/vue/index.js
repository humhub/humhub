/**
 * The user module's Vue entry (see "Module file layout" in docs/develop/ui-js-vuejs-components.md).
 *
 * Registers every top-level component under its filename, as the generated entry does, plus
 * the core's own entry of the People card's `user.card-actions` slot — registered like any
 * module's, so a module can reorder, replace or remove it by id (see `PeopleCard`) — and the
 * `user` filter type of `FilterBar` (`UserFilterControl`, the control of a `UserFilter`).
 */
import { register, registerFilterType, registerSlotComponent } from '@humhub/vue';
import PeopleCardFollowAction from './components/PeopleCardFollowAction.vue';

Object.entries(import.meta.glob('./*.vue', { eager: true, import: 'default' })).forEach(([path, component]) => {
    register(path.slice('./'.length, -'.vue'.length), component);
});

register('PeopleCardFollowAction', PeopleCardFollowAction);
registerSlotComponent('user.card-actions', 'PeopleCardFollowAction', { id: 'follow', sortOrder: 200 });

registerFilterType('user', 'UserFilterControl');
