import { beforeEach, describe, expect, it } from 'vitest';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

describe('humhub.vue menu entry registry', () => {
    beforeEach(() => {
        globalThis.humhubStubs.logCalls.error.length = 0;
        globalThis.humhubStubs.logCalls.debug.length = 0;
    });

    describe('registerMenuEntry / getMenuEntries', () => {
        it('returns an empty entries/removed pair for a menu with no registrations', () => {
            expect(vueModule.getMenuEntries('test.menu.nonexistent')).toEqual({ entries: [], removed: [] });
        });

        it('registers an entry and returns it (with defaults applied) via getMenuEntries', () => {
            vueModule.registerMenuEntry('test.menu.basic', { id: 'first', label: 'First' });

            expect(vueModule.getMenuEntries('test.menu.basic').entries).toEqual([
                { id: 'first', label: 'First', icon: null, sortOrder: 1000, condition: null, onClick: null, component: null },
            ]);
        });

        it('keeps registration order for entries with no sortOrder specified', () => {
            vueModule.registerMenuEntry('test.menu.order', { id: 'a', label: 'A' });
            vueModule.registerMenuEntry('test.menu.order', { id: 'b', label: 'B' });
            vueModule.registerMenuEntry('test.menu.order', { id: 'c', label: 'C' });

            expect(vueModule.getMenuEntries('test.menu.order').entries.map((e) => e.id)).toEqual(['a', 'b', 'c']);
        });

        it('preserves a numeric sortOrder and a custom icon/condition/onClick verbatim', () => {
            const condition = () => true;
            const onClick = () => {};
            vueModule.registerMenuEntry('test.menu.fields', {
                id: 'fielded',
                label: 'Fielded',
                icon: 'pencil',
                sortOrder: 50,
                condition,
                onClick,
            });

            const [entry] = vueModule.getMenuEntries('test.menu.fields').entries;
            expect(entry.icon).toBe('pencil');
            expect(entry.sortOrder).toBe(50);
            expect(entry.condition).toBe(condition);
            expect(entry.onClick).toBe(onClick);
        });

        it('accepts a component-only entry (no label required)', () => {
            vueModule.registerMenuEntry('test.menu.component', { id: 'comp', component: 'SomeRegisteredComponent' });

            expect(vueModule.getMenuEntries('test.menu.component').entries).toEqual([
                { id: 'comp', label: undefined, icon: null, sortOrder: 1000, condition: null, onClick: null, component: 'SomeRegisteredComponent' },
            ]);
        });

        it('accepts a function label (resolved later by DropdownMenu, not here)', () => {
            const label = (context) => `Hi ${context.name}`;
            vueModule.registerMenuEntry('test.menu.funclabel', { id: 'greet', label });

            expect(vueModule.getMenuEntries('test.menu.funclabel').entries[0].label).toBe(label);
        });

        it('replaces a duplicate id in place — override, not append', () => {
            vueModule.registerMenuEntry('test.menu.override', { id: 'x', label: 'First X', sortOrder: 10 });
            vueModule.registerMenuEntry('test.menu.override', { id: 'y', label: 'Y', sortOrder: 20 });
            vueModule.registerMenuEntry('test.menu.override', { id: 'x', label: 'Second X', sortOrder: 999 });

            const entries = vueModule.getMenuEntries('test.menu.override').entries;
            expect(entries.map((e) => e.id)).toEqual(['x', 'y']); // position kept, not moved to the end
            expect(entries[0].label).toBe('Second X');
            expect(entries[0].sortOrder).toBe(999);
        });

        it('does not log anything when overriding a duplicate id', () => {
            vueModule.registerMenuEntry('test.menu.overridequiet', { id: 'x', label: 'First' });
            globalThis.humhubStubs.logCalls.error.length = 0;
            globalThis.humhubStubs.logCalls.debug.length = 0;

            vueModule.registerMenuEntry('test.menu.overridequiet', { id: 'x', label: 'Second' });

            expect(globalThis.humhubStubs.logCalls.error.length).toBe(0);
            expect(globalThis.humhubStubs.logCalls.debug.length).toBe(0);
        });

        it('allows the same entry id to be registered independently on two different menus', () => {
            vueModule.registerMenuEntry('test.menu.one', { id: 'shared', label: 'One' });
            vueModule.registerMenuEntry('test.menu.two', { id: 'shared', label: 'Two' });

            expect(vueModule.getMenuEntries('test.menu.one').entries[0].label).toBe('One');
            expect(vueModule.getMenuEntries('test.menu.two').entries[0].label).toBe('Two');
        });

        describe('validation — logs at error level and registers nothing', () => {
            it('rejects a missing/empty menuId', () => {
                vueModule.registerMenuEntry('', { id: 'a', label: 'A' });
                expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
                expect(vueModule.getMenuEntries('')).toEqual({ entries: [], removed: [] });

                globalThis.humhubStubs.logCalls.error.length = 0;
                vueModule.registerMenuEntry(null, { id: 'a', label: 'A' });
                expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
            });

            it('rejects a missing entry object', () => {
                vueModule.registerMenuEntry('test.menu.invalid.noentry', null);
                expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
                expect(vueModule.getMenuEntries('test.menu.invalid.noentry').entries).toEqual([]);
            });

            it('rejects a missing/empty entry.id', () => {
                vueModule.registerMenuEntry('test.menu.invalid.noid', { label: 'No id' });
                expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);

                globalThis.humhubStubs.logCalls.error.length = 0;
                vueModule.registerMenuEntry('test.menu.invalid.noid', { id: '', label: 'Empty id' });
                expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
                expect(vueModule.getMenuEntries('test.menu.invalid.noid').entries).toEqual([]);
            });

            it('rejects an entry with neither label nor component', () => {
                vueModule.registerMenuEntry('test.menu.invalid.nolabel', { id: 'x' });
                expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
                expect(vueModule.getMenuEntries('test.menu.invalid.nolabel').entries).toEqual([]);
            });
        });

        describe('reactivity', () => {
            it('re-evaluates a computed over getMenuEntries after a late registration', () => {
                const ids = Vue.computed(() => vueModule.getMenuEntries('test.menu.reactive').entries.map((e) => e.id));

                expect(ids.value).toEqual([]);

                vueModule.registerMenuEntry('test.menu.reactive', { id: 'a', label: 'A' });
                expect(ids.value).toEqual(['a']);

                vueModule.registerMenuEntry('test.menu.reactive', { id: 'b', label: 'B' });
                expect(ids.value).toEqual(['a', 'b']);
            });

            it('re-evaluates a computed over getMenuEntries after a late registration on a BRAND NEW menu id', () => {
                const ids = Vue.computed(() => vueModule.getMenuEntries('test.menu.reactive.brandnew').entries.map((e) => e.id));

                expect(ids.value).toEqual([]);

                vueModule.registerMenuEntry('test.menu.reactive.brandnew', { id: 'first', label: 'First' });
                expect(ids.value).toEqual(['first']);
            });

            it('re-evaluates a computed over getMenuEntries.removed after removeMenuEntry', () => {
                const removed = Vue.computed(() => vueModule.getMenuEntries('test.menu.reactive.removed').removed);

                expect(removed.value).toEqual([]);

                vueModule.removeMenuEntry('test.menu.reactive.removed', 'gone');
                expect(removed.value).toEqual(['gone']);
            });
        });
    });

    describe('removeMenuEntry', () => {
        it('records the removed id, retrievable via getMenuEntries().removed', () => {
            vueModule.removeMenuEntry('test.menu.remove.basic', 'gone');

            expect(vueModule.getMenuEntries('test.menu.remove.basic').removed).toEqual(['gone']);
        });

        it('is idempotent — removing the same id twice does not duplicate it', () => {
            vueModule.removeMenuEntry('test.menu.remove.idempotent', 'gone');
            vueModule.removeMenuEntry('test.menu.remove.idempotent', 'gone');

            expect(vueModule.getMenuEntries('test.menu.remove.idempotent').removed).toEqual(['gone']);
        });

        it('does NOT delete the entry from getMenuEntries().entries — suppression is the consumer’s job', () => {
            vueModule.registerMenuEntry('test.menu.remove.stillraw', { id: 'x', label: 'X' });
            vueModule.removeMenuEntry('test.menu.remove.stillraw', 'x');

            const result = vueModule.getMenuEntries('test.menu.remove.stillraw');
            expect(result.entries.map((e) => e.id)).toEqual(['x']);
            expect(result.removed).toEqual(['x']);
        });

        it('a later registerMenuEntry() call for a removed id still registers (removal is enforced by the consumer, not the store)', () => {
            vueModule.removeMenuEntry('test.menu.remove.laterregister', 'x');
            vueModule.registerMenuEntry('test.menu.remove.laterregister', { id: 'x', label: 'X' });

            const result = vueModule.getMenuEntries('test.menu.remove.laterregister');
            expect(result.entries.map((e) => e.id)).toEqual(['x']);
            expect(result.removed).toEqual(['x']);
        });

        describe('validation — logs at error level and records nothing', () => {
            it('rejects a missing/empty menuId', () => {
                vueModule.removeMenuEntry('', 'x');
                expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
            });

            it('rejects a missing/empty entryId', () => {
                vueModule.removeMenuEntry('test.menu.remove.invalid', '');
                expect(globalThis.humhubStubs.logCalls.error.length).toBe(1);
                expect(vueModule.getMenuEntries('test.menu.remove.invalid').removed).toEqual([]);
            });
        });
    });
});
