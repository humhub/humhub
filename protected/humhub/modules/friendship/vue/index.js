/**
 * The friendship module's Vue entry (see "Module file layout" in docs/develop/ui-js-vuejs-components.md).
 *
 * Registers every top-level component under its filename, as the generated entry does, plus
 * the module's entry `friendship` of the People card's `user.card-actions` slot (see the user
 * module's `PeopleCard`).
 */
import { register, registerSlotComponent } from '@humhub/vue';
import PeopleCardFriendshipAction from './components/PeopleCardFriendshipAction.vue';

Object.entries(import.meta.glob('./*.vue', { eager: true, import: 'default' })).forEach(([path, component]) => {
    register(path.slice('./'.length, -'.vue'.length), component);
});

register('PeopleCardFriendshipAction', PeopleCardFriendshipAction);
registerSlotComponent('user.card-actions', 'PeopleCardFriendshipAction', { id: 'friendship', sortOrder: 100 });
