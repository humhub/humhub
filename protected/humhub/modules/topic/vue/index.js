/**
 * The topic module's Vue entry (see "Module file layout" in docs/develop/ui-js-vuejs-components.md).
 *
 * Registers every top-level component under its filename, as the generated entry does, plus the
 * `topic` filter type of `FilterBar` (`TopicFilterControl`, the control of a `TopicFilter`).
 * Also registers the `topic` picker source (see components/pickerSource.js).
 */
import { register, registerFilterType, registerPickerSource } from '@humhub/vue';
import { topicPickerSource } from './components/pickerSource.js';

Object.entries(import.meta.glob('./*.vue', { eager: true, import: 'default' })).forEach(([path, component]) => {
    register(path.slice('./'.length, -'.vue'.length), component);
});

registerFilterType('topic', 'TopicFilterControl');

registerPickerSource('topic', topicPickerSource);
