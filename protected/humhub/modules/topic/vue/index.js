/**
 * The topic module's Vue entry (see "Module file layout" in docs/develop/ui-js-vuejs-components.md).
 *
 * Registers every top-level component under its filename, as the generated entry does, plus the
 * `topic` filter type of `FilterBar` (`TopicFilterControl`, the control of a `TopicFilter`).
 */
import { register, registerFilterType } from '@humhub/vue';

Object.entries(import.meta.glob('./*.vue', { eager: true, import: 'default' })).forEach(([path, component]) => {
    register(path.slice('./'.length, -'.vue'.length), component);
});

registerFilterType('topic', 'TopicFilterControl');
