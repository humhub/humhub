import { beforeEach, describe, expect, it } from 'vitest';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

describe('humhub.vue picker registries', () => {
    beforeEach(() => {
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.logCalls.debug.length = 0;
    });

    it('answers a registered source factory, null for an unknown one', () => {
        const factory = () => ({ search: () => Promise.resolve([]) });
        vueModule.registerPickerSource('test.source', factory);

        expect(vueModule.getPickerSource('test.source')).toBe(factory);
        expect(vueModule.getPickerSource('test.unknown')).toBeNull();
    });

    it('keeps the first source registration and logs another one as error', () => {
        const first = () => ({});
        vueModule.registerPickerSource('test.twice', first);
        vueModule.registerPickerSource('test.twice', first);
        vueModule.registerPickerSource('test.twice', () => ({}));

        expect(vueModule.getPickerSource('test.twice')).toBe(first);
        expect(globalThis.humhubStubs.logCalls.debug).toHaveLength(1);
        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(1);
    });

    it('rejects a source that is no function', () => {
        vueModule.registerPickerSource('test.invalid', 'nope');

        expect(vueModule.getPickerSource('test.invalid')).toBeNull();
        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(1);
    });

    it('answers a registered image type with its component and props mapper', () => {
        const toProps = (image, size) => ({ ...image, size });
        vueModule.registerPickerImage('test.image', 'TestPickerImage', toProps);

        expect(vueModule.getPickerImage('test.image')).toEqual({ component: 'TestPickerImage', toProps });
        expect(vueModule.getPickerImage('test.none')).toBeNull();
    });

    it('defaults the props mapper to the description plus size', () => {
        vueModule.registerPickerImage('test.plain', 'TestPlainImage');

        expect(vueModule.getPickerImage('test.plain').toProps({ a: 1 }, 24)).toEqual({ a: 1, size: 24 });
    });

    it('refuses the built-in image types and invalid component names', () => {
        vueModule.registerPickerImage('icon', 'TestIconImage');
        vueModule.registerPickerImage('test.badname', 'lowercase');

        expect(vueModule.getPickerImage('icon')).toBeNull();
        expect(vueModule.getPickerImage('test.badname')).toBeNull();
        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(2);
    });
});
