import { beforeEach, describe, expect, it } from 'vitest';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

describe('humhub.vue slot registry', () => {
    beforeEach(() => {
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.logCalls.debug.length = 0;
    });

    it('sorts registered slot components by sortOrder, defaulting to 100', () => {
        vueModule.registerSlotComponent('test.registry.sort', 'TestRegistryDefault'); // default sortOrder: 100
        vueModule.registerSlotComponent('test.registry.sort', 'TestRegistryFirst', { sortOrder: 10 });
        vueModule.registerSlotComponent('test.registry.sort', 'TestRegistryLast', { sortOrder: 200 });

        const entries = vueModule.getSlotComponents('test.registry.sort');
        expect(entries.map((entry) => entry.component)).toEqual([
            'TestRegistryFirst',
            'TestRegistryDefault',
            'TestRegistryLast',
        ]);
        expect(entries.map((entry) => entry.sortOrder)).toEqual([10, 100, 200]);
    });

    it('keeps registration order stable among entries sharing the same sortOrder', () => {
        vueModule.registerSlotComponent('test.registry.stable', 'TestRegistryAlpha', { sortOrder: 50 });
        vueModule.registerSlotComponent('test.registry.stable', 'TestRegistryBeta', { sortOrder: 50 });
        vueModule.registerSlotComponent('test.registry.stable', 'TestRegistryGamma', { sortOrder: 50 });

        const entries = vueModule.getSlotComponents('test.registry.stable');
        expect(entries.map((entry) => entry.component)).toEqual([
            'TestRegistryAlpha',
            'TestRegistryBeta',
            'TestRegistryGamma',
        ]);
    });

    it('returns an empty array for a slot with no registrations', () => {
        expect(vueModule.getSlotComponents('test.registry.nonexistent')).toEqual([]);
    });

    it('rejects a non-string or empty slot name at error level and registers nothing', () => {
        vueModule.registerSlotComponent('', 'TestRegistryEmptySlot');
        expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
        expect(vueModule.getSlotComponents('')).toEqual([]);

        globalThis.humhubStubs.logCalls.error.length = 0;
        vueModule.registerSlotComponent(null, 'TestRegistryNullSlot');
        expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
    });

    it('rejects a non-PascalCase component name at error level and registers nothing', () => {
        vueModule.registerSlotComponent('test.registry.invalidname', 'lowerCaseName');
        expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
        expect(vueModule.getSlotComponents('test.registry.invalidname')).toEqual([]);
    });

    it('replaces the entry of a re-registered id in place — artifact re-execution, overrides', () => {
        vueModule.registerSlotComponent('test.registry.duplicate', 'TestRegistryDup', { sortOrder: 5 });
        vueModule.registerSlotComponent('test.registry.duplicate', 'TestRegistryDupOther', { sortOrder: 5 });

        vueModule.registerSlotComponent('test.registry.duplicate', 'TestRegistryDup', { sortOrder: 5 });
        expect(globalThis.humhubStubs.logCalls.error.length).toBe(0);
        expect(vueModule.getSlotComponents('test.registry.duplicate')).toEqual([
            { id: 'TestRegistryDup', component: 'TestRegistryDup', sortOrder: 5 },
            { id: 'TestRegistryDupOther', component: 'TestRegistryDupOther', sortOrder: 5 },
        ]);

        // Another component under a taken id replaces it, keeping its place among equal sortOrders.
        vueModule.registerSlotComponent('test.registry.duplicate', 'TestRegistryDupReplacement', { id: 'TestRegistryDup', sortOrder: 5 });
        expect(vueModule.getSlotComponents('test.registry.duplicate')).toEqual([
            { id: 'TestRegistryDup', component: 'TestRegistryDupReplacement', sortOrder: 5 },
            { id: 'TestRegistryDupOther', component: 'TestRegistryDupOther', sortOrder: 5 },
        ]);
    });

    it('identifies an entry by an explicit id and moves it with a new sortOrder', () => {
        vueModule.registerSlotComponent('test.registry.ids', 'TestRegistryIdA', { id: 'a', sortOrder: 100 });
        vueModule.registerSlotComponent('test.registry.ids', 'TestRegistryIdB', { id: 'b', sortOrder: 200 });
        vueModule.registerSlotComponent('test.registry.ids', 'TestRegistryIdA', { id: 'a', sortOrder: 300 });

        expect(vueModule.getSlotComponents('test.registry.ids')).toEqual([
            { id: 'b', component: 'TestRegistryIdB', sortOrder: 200 },
            { id: 'a', component: 'TestRegistryIdA', sortOrder: 300 },
        ]);
    });

    it('rejects an empty or non-string id at error level and registers nothing', () => {
        vueModule.registerSlotComponent('test.registry.badid', 'TestRegistryBadId', { id: '' });
        vueModule.registerSlotComponent('test.registry.badid', 'TestRegistryBadId', { id: 5 });

        expect(globalThis.humhubStubs.logCalls.error.length).toBe(2);
        expect(vueModule.getSlotComponents('test.registry.badid')).toEqual([]);
    });

    describe('removeSlotComponent', () => {
        it('drops the entry of an id, registered before or after the removal, for good', () => {
            vueModule.registerSlotComponent('test.registry.remove', 'TestRegistryKeep', { id: 'keep' });
            vueModule.registerSlotComponent('test.registry.remove', 'TestRegistryGone', { id: 'gone' });
            vueModule.removeSlotComponent('test.registry.remove', 'gone');
            vueModule.removeSlotComponent('test.registry.remove', 'later');

            vueModule.registerSlotComponent('test.registry.remove', 'TestRegistryLater', { id: 'later' });
            vueModule.registerSlotComponent('test.registry.remove', 'TestRegistryGoneAgain', { id: 'gone' });

            expect(vueModule.getSlotComponents('test.registry.remove').map((entry) => entry.id)).toEqual(['keep']);
        });

        it('rejects an invalid slot name or id at error level', () => {
            vueModule.removeSlotComponent('', 'x');
            vueModule.removeSlotComponent('test.registry.remove.invalid', '');

            expect(globalThis.humhubStubs.logCalls.error.length).toBe(2);
        });

        it('re-evaluates a computed over getSlotComponents after a removal', () => {
            vueModule.registerSlotComponent('test.registry.remove.reactive', 'TestRegistryRemoveReactive', { id: 'x' });
            const names = Vue.computed(() => vueModule.getSlotComponents('test.registry.remove.reactive').map((e) => e.id));
            expect(names.value).toEqual(['x']);

            vueModule.removeSlotComponent('test.registry.remove.reactive', 'x');
            expect(names.value).toEqual([]);
        });

        it('is undone by the test-only resetSlotRegistry()', () => {
            vueModule.registerSlotComponent('test.registry.reset', 'TestRegistryReset');
            vueModule.removeSlotComponent('test.registry.reset', 'TestRegistryReset');
            vueModule.resetSlotRegistry();

            vueModule.registerSlotComponent('test.registry.reset', 'TestRegistryReset');
            expect(vueModule.getSlotComponents('test.registry.reset').map((e) => e.id)).toEqual(['TestRegistryReset']);
        });
    });

    it('allows the same component to register for two different slots', () => {
        vueModule.registerSlotComponent('test.registry.slotone', 'TestRegistryShared');
        vueModule.registerSlotComponent('test.registry.slottwo', 'TestRegistryShared');

        expect(vueModule.getSlotComponents('test.registry.slotone')).toEqual([
            { id: 'TestRegistryShared', component: 'TestRegistryShared', sortOrder: 100 },
        ]);
        expect(vueModule.getSlotComponents('test.registry.slottwo')).toEqual([
            { id: 'TestRegistryShared', component: 'TestRegistryShared', sortOrder: 100 },
        ]);
    });

    it('logs at debug level (not error) when the component is not registered yet at call time — late artifact tolerance', () => {
        vueModule.registerSlotComponent('test.registry.late', 'TestRegistryNotYetRegistered');

        expect(globalThis.humhubStubs.logCalls.error.length).toBe(0);
        expect(globalThis.humhubStubs.logCalls.debug.length).toBe(1);
        // It is still recorded — the component simply isn't registered yet.
        expect(vueModule.getSlotComponents('test.registry.late')).toEqual([
            { id: 'TestRegistryNotYetRegistered', component: 'TestRegistryNotYetRegistered', sortOrder: 100 },
        ]);
    });

    it('does not log at debug level when the component is already registered at call time', () => {
        vueModule.register('TestRegistryAlreadyThere', { render: () => Vue.h('i') });
        globalThis.humhubStubs.logCalls.debug.length = 0;

        vueModule.registerSlotComponent('test.registry.already', 'TestRegistryAlreadyThere');

        expect(globalThis.humhubStubs.logCalls.debug.length).toBe(0);
    });

    describe('isRegistered', () => {
        it('reflects whether a component name is present in the component registry', () => {
            expect(vueModule.isRegistered('TestRegistryUnknownName')).toBe(false);

            vueModule.register('TestRegistryKnownName', { render: () => Vue.h('i') });

            expect(vueModule.isRegistered('TestRegistryKnownName')).toBe(true);
        });
    });

    describe('reactivity', () => {
        // The load-bearing test: a computed() reading getSlotComponents() must re-evaluate
        // after a late registerSlotComponent() call, without anything re-mounting or
        // re-subscribing — this is what lets an already-mounted island's <ExtensionSlot>
        // pick up a module artifact that registers after the island itself mounted.
        it('re-evaluates a computed over getSlotComponents after a late registration', () => {
            const names = Vue.computed(() => vueModule.getSlotComponents('test.registry.reactive').map((e) => e.component));

            expect(names.value).toEqual([]);

            vueModule.registerSlotComponent('test.registry.reactive', 'TestRegistryReactiveA');
            expect(names.value).toEqual(['TestRegistryReactiveA']);

            // Sorts ahead of the first entry — proves the computed re-sorts, not just re-appends.
            vueModule.registerSlotComponent('test.registry.reactive', 'TestRegistryReactiveB', { sortOrder: 1 });
            expect(names.value).toEqual(['TestRegistryReactiveB', 'TestRegistryReactiveA']);
        });

        it('re-evaluates a computed over getSlotComponents after a late registration on a BRAND NEW slot name', () => {
            // No prior registerSlotComponent call for this slot name has ever happened —
            // the computed must still pick up the very first registration.
            const names = Vue.computed(() => vueModule.getSlotComponents('test.registry.reactive.brandnew').map((e) => e.component));

            expect(names.value).toEqual([]);

            vueModule.registerSlotComponent('test.registry.reactive.brandnew', 'TestRegistryBrandNew');
            expect(names.value).toEqual(['TestRegistryBrandNew']);
        });
    });
});
