import { beforeEach, describe, expect, it } from 'vitest';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

describe('humhub.vue filter type registry', () => {
    beforeEach(() => {
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.logCalls.debug.length = 0;
    });

    it('answers the component registered for a type, and null for an unknown one', () => {
        vueModule.registerFilterType('test.registry.status', 'TestRegistryStatusFilter');

        expect(vueModule.getFilterType('test.registry.status')).toBe('TestRegistryStatusFilter');
        expect(vueModule.getFilterType('test.registry.unknown')).toBeNull();
    });

    it('does not require the component to be registered yet', () => {
        vueModule.registerFilterType('test.registry.late', 'TestRegistryNotYetThere');

        expect(vueModule.getFilterType('test.registry.late')).toBe('TestRegistryNotYetThere');
        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(0);
    });

    it('keeps the first registration of a type', () => {
        vueModule.registerFilterType('test.registry.first', 'TestRegistryFirstFilter');
        vueModule.registerFilterType('test.registry.first', 'TestRegistryFirstFilter');
        expect(globalThis.humhubStubs.logCalls.debug).toHaveLength(1);
        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(0);

        vueModule.registerFilterType('test.registry.first', 'TestRegistryOtherFilter');
        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(1);
        expect(vueModule.getFilterType('test.registry.first')).toBe('TestRegistryFirstFilter');
    });

    it('refuses the core types, empty type names and invalid component names', () => {
        vueModule.registerFilterType('select', 'TestRegistrySelectFilter');
        vueModule.registerFilterType('', 'TestRegistryEmptyFilter');
        vueModule.registerFilterType('test.registry.invalid', 'not-pascal');
        // The rule of register(): a single word gives no dashed tag name.
        vueModule.registerFilterType('test.registry.single', 'Status');

        expect(globalThis.humhubStubs.logCalls.error).toHaveLength(4);
        expect(vueModule.getFilterType('select')).toBeNull();
        expect(vueModule.getFilterType('test.registry.invalid')).toBeNull();
        expect(vueModule.getFilterType('test.registry.single')).toBeNull();
    });

    it('is reactive: a computed that asked for a type re-evaluates once it registers', () => {
        const type = Vue.computed(() => vueModule.getFilterType('test.registry.reactive'));
        expect(type.value).toBeNull();

        vueModule.registerFilterType('test.registry.reactive', 'TestRegistryReactiveFilter');
        expect(type.value).toBe('TestRegistryReactiveFilter');
    });
});
