import { beforeEach, describe, expect, it } from 'vitest';
import { mount } from '@vue/test-utils';
import DropdownMenu from '../../vue/DropdownMenu.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

describe('DropdownMenu', () => {
    it('renders the toggle/menu structure with the given aria-label', () => {
        const wrapper = mount(DropdownMenu, {
            props: { toggleAriaLabel: 'Toggle menu' },
        });

        expect(wrapper.find('ul.nav.nav-pills.preferences').exists()).toBe(true);
        expect(wrapper.find('li.nav-item.dropdown').exists()).toBe(true);

        const toggle = wrapper.find('a[data-bs-toggle="dropdown"]');
        expect(toggle.exists()).toBe(true);
        expect(toggle.attributes('aria-label')).toBe('Toggle menu');
        expect(toggle.attributes('aria-haspopup')).toBe('true');
        expect(toggle.attributes('aria-expanded')).toBe('false');
        expect(toggle.attributes('role')).toBe('button');
        expect(toggle.classes()).toEqual(['nav-link', 'dropdown-toggle']);
    });

    it('renders slot content as the menu items', () => {
        const wrapper = mount(DropdownMenu, {
            props: { toggleAriaLabel: 'Toggle menu' },
            slots: {
                default: '<li><a href="#" class="dropdown-item">Edit</a></li><li><a href="#" class="dropdown-item">Delete</a></li>',
            },
        });

        const items = wrapper.findAll('.dropdown-menu > li');
        expect(items).toHaveLength(2);
        expect(items[0].text()).toBe('Edit');
        expect(items[1].text()).toBe('Delete');
    });

    it('aligns the menu to the end by default', () => {
        const wrapper = mount(DropdownMenu, {
            props: { toggleAriaLabel: 'Toggle menu' },
        });

        expect(wrapper.find('.dropdown-menu').classes()).toContain('dropdown-menu-end');
    });

    it('drops dropdown-menu-end when alignEnd is false', () => {
        const wrapper = mount(DropdownMenu, {
            props: { toggleAriaLabel: 'Toggle menu', alignEnd: false },
        });

        expect(wrapper.find('.dropdown-menu').classes()).not.toContain('dropdown-menu-end');
    });

    it('replaces the toggle class entirely when toggleClass is set', () => {
        const wrapper = mount(DropdownMenu, {
            props: { toggleAriaLabel: 'Toggle menu', toggleClass: 'btn btn-icon-only' },
        });

        const toggle = wrapper.find('a[data-bs-toggle="dropdown"]');
        expect(toggle.classes()).toEqual(['btn', 'btn-icon-only']);
        expect(toggle.classes()).not.toContain('nav-link');
    });

    // Data-driven mode: `menuId`/`entries` — see the component's own "Data-driven mode"
    // docblock for the resolution pipeline these tests exercise. Every test uses its own
    // unique `menuId` to stay isolated from every other test's registry state (the registry
    // is a real module-level singleton — same convention as slotRegistry.test.js/
    // extensionSlot.test.js).
    describe('data-driven mode (menuId/entries)', () => {
        beforeEach(() => {
            globalThis.humhubStubs.logCalls.warn.length = 0;
        });

        it('does not render an entries block at all when menuId is not set, even if entries is given — slot mode is untouched', () => {
            const wrapper = mount(DropdownMenu, {
                props: { toggleAriaLabel: 'Toggle menu', entries: [{ id: 'x', label: 'X' }] },
                slots: { default: '<li><a href="#" class="dropdown-item">Legacy</a></li>' },
            });

            const items = wrapper.findAll('.dropdown-menu > li');
            expect(items).toHaveLength(1);
            expect(items[0].text()).toBe('Legacy');
        });

        it('renders slot content first, then the resolved entries', () => {
            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.slotplusentries',
                    entries: [{ id: 'edit', label: 'Edit' }],
                },
                slots: { default: '<li><a href="#" class="dropdown-item">Permalink</a></li>' },
            });

            const items = wrapper.findAll('.dropdown-menu > li');
            expect(items.map((item) => item.text())).toEqual(['Permalink', 'Edit']);
        });

        it('renders built-in entries as dropdown-item anchors calling onClick(context) on click', async () => {
            const clicks = [];
            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.click',
                    entries: [{ id: 'edit', label: 'Edit', onClick: (context) => clicks.push(context) }],
                    context: { comment: { id: 42 } },
                },
            });

            const link = wrapper.find('.dropdown-item');
            expect(link.text()).toBe('Edit');
            await link.trigger('click');

            expect(clicks).toEqual([{ comment: { id: 42 } }]);
        });

        it('resolves a function label with the context', () => {
            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.funclabel',
                    entries: [{ id: 'greet', label: (context) => `Hi ${context.name}` }],
                    context: { name: 'Ada' },
                },
            });

            expect(wrapper.find('.dropdown-item').text()).toBe('Hi Ada');
        });

        it('renders no icon element and no extra class for an entry without an icon — byte-stable with the pre-existing plain items', () => {
            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.noicon',
                    entries: [{ id: 'edit', label: 'Edit' }],
                },
            });

            const link = wrapper.find('.dropdown-item');
            expect(link.element.innerHTML).toBe('Edit');
            expect(link.classes()).toEqual(['dropdown-item']);
        });

        it('renders a leading <i class="fa fa-<icon>"> for an entry with an icon', () => {
            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.icon',
                    entries: [{ id: 'edit', label: 'Edit', icon: 'pencil' }],
                },
            });

            const link = wrapper.find('.dropdown-item');
            const icon = link.find('i');
            expect(icon.exists()).toBe(true);
            expect(icon.classes()).toEqual(['fa', 'fa-pencil']);
            expect(icon.attributes('aria-hidden')).toBe('true');
            expect(link.text()).toBe('Edit');
        });

        it('drops an entry whose condition(context) is falsy, and re-evaluates reactively when context changes', async () => {
            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.condition',
                    entries: [{ id: 'edit', label: 'Edit', condition: (context) => context.canEdit }],
                    context: { canEdit: false },
                },
            });

            expect(wrapper.findAll('.dropdown-item')).toHaveLength(0);

            await wrapper.setProps({ context: { canEdit: true } });
            expect(wrapper.findAll('.dropdown-item')).toHaveLength(1);
        });

        it('sorts built-in entries by ascending sortOrder', () => {
            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.sort',
                    entries: [
                        { id: 'c', label: 'C', sortOrder: 300 },
                        { id: 'a', label: 'A', sortOrder: 100 },
                        { id: 'b', label: 'B', sortOrder: 200 },
                    ],
                },
            });

            expect(wrapper.findAll('.dropdown-item').map((item) => item.text())).toEqual(['A', 'B', 'C']);
        });

        it('keeps insertion order for entries sharing a sortOrder, built-ins before registry entries', () => {
            vueModule.registerMenuEntry('test.dropdown.tiebreak', { id: 'registered', label: 'Registered' }); // default sortOrder 1000

            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.tiebreak',
                    entries: [
                        { id: 'builtin1', label: 'Builtin 1' }, // default sortOrder 1000
                        { id: 'builtin2', label: 'Builtin 2' }, // default sortOrder 1000
                    ],
                },
            });

            expect(wrapper.findAll('.dropdown-item').map((item) => item.text()))
                .toEqual(['Builtin 1', 'Builtin 2', 'Registered']);
        });

        it('appends a registry entry with no matching built-in id after the built-ins, honoring its own sortOrder', () => {
            vueModule.registerMenuEntry('test.dropdown.merge', { id: 'extra', label: 'Extra', sortOrder: 1 });

            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.merge',
                    entries: [{ id: 'edit', label: 'Edit', sortOrder: 500 }],
                },
            });

            // Appended after the built-in in the pre-sort merge (step 1), but sortOrder
            // (step 6) is what actually decides render order — a low-sortOrder registry
            // addition still renders ahead of a higher-sortOrder built-in despite being
            // merged in after it.
            expect(wrapper.findAll('.dropdown-item').map((item) => item.text())).toEqual(['Extra', 'Edit']);
        });

        it('overrides a built-in entry sharing its id, keeping the built-in\'s tie-break position', () => {
            vueModule.registerMenuEntry('test.dropdown.override', { id: 'edit', label: 'Overridden Edit' });

            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.override',
                    entries: [
                        { id: 'permalink', label: 'Permalink' },
                        { id: 'edit', label: 'Original Edit' },
                        { id: 'delete', label: 'Delete' },
                    ],
                },
            });

            const texts = wrapper.findAll('.dropdown-item').map((item) => item.text());
            // Same position as the original built-in — overriding does not reorder the menu.
            expect(texts).toEqual(['Permalink', 'Overridden Edit', 'Delete']);
        });

        it('removeMenuEntry suppresses a BUILT-IN entry, never registered at all', () => {
            vueModule.removeMenuEntry('test.dropdown.removebuiltin', 'edit');

            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.removebuiltin',
                    entries: [{ id: 'edit', label: 'Edit' }, { id: 'delete', label: 'Delete' }],
                },
            });

            expect(wrapper.findAll('.dropdown-item').map((item) => item.text())).toEqual(['Delete']);
        });

        it('removeMenuEntry suppresses a REGISTERED entry', () => {
            vueModule.registerMenuEntry('test.dropdown.removeregistered', { id: 'extra', label: 'Extra' });
            vueModule.removeMenuEntry('test.dropdown.removeregistered', 'extra');

            const wrapper = mount(DropdownMenu, {
                props: { toggleAriaLabel: 'Toggle menu', menuId: 'test.dropdown.removeregistered', entries: [] },
            });

            expect(wrapper.findAll('.dropdown-item')).toHaveLength(0);
        });

        it('removeMenuEntry wins over a LATER registration of the same id', async () => {
            vueModule.removeMenuEntry('test.dropdown.removelater', 'extra');

            const wrapper = mount(DropdownMenu, {
                props: { toggleAriaLabel: 'Toggle menu', menuId: 'test.dropdown.removelater', entries: [] },
            });

            expect(wrapper.findAll('.dropdown-item')).toHaveLength(0);

            vueModule.registerMenuEntry('test.dropdown.removelater', { id: 'extra', label: 'Extra' });
            await Vue.nextTick();

            expect(wrapper.findAll('.dropdown-item')).toHaveLength(0);
        });

        it('picks up a late registerMenuEntry() call in an already-mounted menu, without remounting', async () => {
            const wrapper = mount(DropdownMenu, {
                props: { toggleAriaLabel: 'Toggle menu', menuId: 'test.dropdown.late', entries: [] },
            });

            expect(wrapper.findAll('.dropdown-item')).toHaveLength(0);

            vueModule.registerMenuEntry('test.dropdown.late', { id: 'late', label: 'Late' });
            await Vue.nextTick();

            expect(wrapper.findAll('.dropdown-item').map((item) => item.text())).toEqual(['Late']);
        });

        it('renders a component entry with a single context prop, ignoring label/icon/onClick', () => {
            const probeDef = {
                props: { context: { type: Object, required: true } },
                render() {
                    return Vue.h('span', { class: 'probe-menu-entry' }, `probe:${this.context.id}`);
                },
            };
            vueModule.register('TestDropdownProbeEntry', probeDef);

            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.component',
                    entries: [{
                        id: 'probe',
                        label: 'Should be ignored',
                        icon: 'ignored-too',
                        onClick: () => { throw new Error('must not be called for a component entry'); },
                        component: 'TestDropdownProbeEntry',
                    }],
                    context: { id: 7 },
                },
                global: { components: { TestDropdownProbeEntry: probeDef } },
            });

            const probe = wrapper.find('.probe-menu-entry');
            expect(probe.exists()).toBe(true);
            expect(probe.text()).toBe('probe:7');
            expect(wrapper.find('.dropdown-item').exists()).toBe(false); // no <a>, no icon/label rendered
        });

        it('skips a component entry whose component is not (yet) registered, without a Vue warning, and picks it up once it registers', async () => {
            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.latecomponent',
                    entries: [{ id: 'probe', component: 'TestDropdownLateProbeEntry' }],
                    context: {},
                },
                global: { components: { TestDropdownLateProbeEntry: { render: () => Vue.h('mark', 'late') } } },
            });

            expect(wrapper.find('mark').exists()).toBe(false);
            expect(globalThis.humhubStubs.logCalls.warn.length).toBe(0);

            vueModule.register('TestDropdownLateProbeEntry', { render: () => Vue.h('mark', 'late') });
            await Vue.nextTick();

            expect(wrapper.find('mark').exists()).toBe(true);
        });
    });
});
