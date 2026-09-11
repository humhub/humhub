import { afterEach, beforeEach, describe, expect, it } from 'vitest';
import { config, mount } from '@vue/test-utils';
import DropdownMenu from '../../vue/DropdownMenu.vue';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const vueModule = globalThis.humhub.modules.vue;

// `v-additions` is registered by the island runtime on the real app; the `html` escape-hatch
// branch of an entry carries it, so the compiled render function resolves the directive for
// every render of this component. Registered file-wide rather than per mount, since every
// test here mounts DropdownMenu (see activityBox.test.js for the same stub).
config.global.directives = {
    ...config.global.directives,
    additions: {
        mounted(el) {
            globalThis.humhubStubs.additions.applyTo(jQuery(el));
        },
        updated(el) {
            globalThis.humhubStubs.additions.applyTo(jQuery(el));
        },
    },
};

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

    // The root element and the toggle's own content. Both defaults reproduce the platform's
    // corner-controls menu exactly; a consumer that is NOT a corner-controls menu (a labelled
    // dropdown button in a toolbar, an inline menu in a flex row) has to be able to opt out —
    // `.nav-pills.preferences` is positioned `absolute` platform-wide (_nav.scss).
    describe('root class and toggle content', () => {
        it('defaults to the corner-controls markup', () => {
            const wrapper = mount(DropdownMenu, {
                props: { toggleAriaLabel: 'Toggle menu' },
            });

            expect(wrapper.element.classList.contains('nav')).toBe(true);
            expect(wrapper.element.classList.contains('nav-pills')).toBe(true);
            expect(wrapper.element.classList.contains('preferences')).toBe(true);
            // No content of its own: the meatball icon is a CSS ::after on .preferences.
            expect(wrapper.find('a[data-bs-toggle="dropdown"]').text()).toBe('');
        });

        it('replaces the root classes entirely when rootClass is given', () => {
            const wrapper = mount(DropdownMenu, {
                props: { toggleAriaLabel: 'Toggle menu', rootClass: 'nav nav-pills mb-0' },
            });

            expect(wrapper.element.className).toBe('nav nav-pills mb-0');
            expect(wrapper.element.classList.contains('preferences')).toBe(false);
        });

        it('renders the toggle slot inside the toggle, so it can carry a label', () => {
            const wrapper = mount(DropdownMenu, {
                props: { toggleAriaLabel: 'Sort by' },
                slots: { toggle: 'Name \u2191' },
            });

            expect(wrapper.find('a[data-bs-toggle="dropdown"]').text()).toBe('Name \u2191');
        });
    });

    // Server-described entries — the shapes `MenuEntry::describe()` produces, consumed by the
    // ContentControls island. See the component's own "Server-described entries" docblock.
    describe('server-described entries', () => {
        it('renders a url entry as a real link and lets the click through', async () => {
            let clicked = false;
            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.url',
                    entries: [{ id: 'edit', label: 'Edit', url: '/wiki/edit?id=3' }],
                },
            });

            const link = wrapper.find('.dropdown-item');
            expect(link.attributes('href')).toBe('/wiki/edit?id=3');

            link.element.addEventListener('click', (event) => {
                clicked = !event.defaultPrevented;
                event.preventDefault();
            });
            await link.trigger('click');

            // A plain link must navigate; swallowing the click would break middle-click and
            // "open in new tab" just as much as the navigation itself.
            expect(clicked).toBe(true);
        });

        it('still swallows the click of an entry without a url', async () => {
            let defaultPrevented = null;
            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.nourl',
                    entries: [{ id: 'edit', label: 'Edit' }],
                },
            });

            const link = wrapper.find('.dropdown-item');
            expect(link.attributes('href')).toBe('#');

            link.element.addEventListener('click', (event) => {
                defaultPrevented = event.defaultPrevented;
            });
            await link.trigger('click');

            expect(defaultPrevented).toBe(true);
        });

        it('binds htmlOptions onto the anchor so legacy data-action handlers keep working', () => {
            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.htmloptions',
                    entries: [{
                        id: 'share',
                        label: 'Share',
                        icon: 'share',
                        htmlOptions: {
                            'data-action-click': 'ui.modal.load',
                            'data-action-url': '/share?id=3',
                            'class': 'share-link',
                        },
                    }],
                },
            });

            const link = wrapper.find('.dropdown-item');
            expect(link.attributes('data-action-click')).toBe('ui.modal.load');
            expect(link.attributes('data-action-url')).toBe('/share?id=3');
            // The entry's own class is merged with the component's, not replaced by it.
            expect(link.classes()).toContain('share-link');
            expect(link.classes()).toContain('dropdown-item');
        });

        it('lets url win over an href smuggled in through htmlOptions', () => {
            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.hrefclash',
                    entries: [{ id: 'x', label: 'X', url: '/right', htmlOptions: { href: '/wrong' } }],
                },
            });

            expect(wrapper.find('.dropdown-item').attributes('href')).toBe('/right');
        });

        it('renders an html entry as the whole li and runs the ui additions over it', () => {
            let enhanced = 0;
            globalThis.humhubStubs.additions.register('test-legacy-item', '.legacy-item', function () {
                enhanced++;
            });

            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.html',
                    entries: [{ id: 'legacy', html: '<a class="dropdown-item legacy-item">Legacy</a>' }],
                },
            });

            const item = wrapper.find('.dropdown-menu > li');
            // No wrapper element around the injected markup — the anchor is the li's own child,
            // which is what Bootstrap's dropdown styling expects.
            expect(item.element.children).toHaveLength(1);
            expect(item.find('a.legacy-item').exists()).toBe(true);
            // v-additions handed the injected markup to the legacy enhancer pipeline, which is
            // the whole point of the escape hatch: the entry's own JS still initializes.
            expect(enhanced).toBeGreaterThan(0);
        });

        it('renders a divider entry', () => {
            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.divider',
                    entries: [
                        { id: 'a', label: 'A', sortOrder: 10 },
                        { id: 'sep', divider: true, sortOrder: 20 },
                        { id: 'b', label: 'B', sortOrder: 30 },
                    ],
                },
            });

            const items = wrapper.findAll('.dropdown-menu > li');
            expect(items).toHaveLength(3);
            expect(items[1].find('hr.dropdown-divider').exists()).toBe(true);
        });

        it('lets a registry entry override a server-described one by id', () => {
            vueModule.registerMenuEntry('test.dropdown.serveroverride', { id: 'share', label: 'Native share' });

            const wrapper = mount(DropdownMenu, {
                props: {
                    toggleAriaLabel: 'Toggle menu',
                    menuId: 'test.dropdown.serveroverride',
                    entries: [{ id: 'share', label: 'Server share', url: '/share' }],
                },
            });

            const items = wrapper.findAll('.dropdown-item');
            expect(items).toHaveLength(1);
            expect(items[0].text()).toBe('Native share');
            expect(items[0].attributes('href')).toBe('#');
        });
    });

    /**
     * `open()` is how a host raises this menu from outside its own toggle — a right-click on
     * the row the menu belongs to. Bootstrap is stubbed here: what matters is the `reference`
     * this component hands it, not Popper's arithmetic.
     */
    describe('open()', () => {
        class DropdownStub {
            static instances = new Map();

            static getInstance(element) {
                return DropdownStub.instances.get(element) || null;
            }

            constructor(element, config = {}) {
                this.element = element;
                this.config = config;
                this.shows = 0;
                this.hides = 0;
                this.disposed = false;
                DropdownStub.instances.set(element, this);
            }

            show() {
                this.shows += 1;
                this.element.dispatchEvent(new Event('show.bs.dropdown'));
            }

            hide() {
                this.hides += 1;
                this.element.dispatchEvent(new Event('hidden.bs.dropdown'));
            }

            dispose() {
                this.disposed = true;
                DropdownStub.instances.delete(this.element);
            }
        }

        const mountMenu = () => mount(DropdownMenu, { props: { toggleAriaLabel: 'Toggle menu' } });
        const instanceOf = (wrapper) => DropdownStub.getInstance(wrapper.find('a[data-bs-toggle="dropdown"]').element);

        beforeEach(() => {
            DropdownStub.instances = new Map();
            globalThis.bootstrap = { Dropdown: DropdownStub };
        });

        afterEach(() => {
            delete globalThis.bootstrap;
        });

        it('opens at the pointer when handed a mouse event', () => {
            const wrapper = mountMenu();

            wrapper.vm.open({ clientX: 120, clientY: 40 });

            const dropdown = instanceOf(wrapper);
            expect(dropdown.shows).toBe(1);
            // Popper's virtual-element convention: a zero-size rect where the cursor is.
            expect(dropdown.config.reference.getBoundingClientRect()).toEqual({
                width: 0, height: 0, top: 40, right: 120, bottom: 40, left: 120, x: 120, y: 40,
            });
        });

        it('opens under the toggle when handed no event', () => {
            const wrapper = mountMenu();
            const toggle = wrapper.find('a[data-bs-toggle="dropdown"]').element;
            const toggleRect = { width: 20, height: 20, top: 5, right: 25, bottom: 25, left: 5, x: 5, y: 5 };
            toggle.getBoundingClientRect = () => toggleRect;

            wrapper.vm.open();

            expect(instanceOf(wrapper).config.reference.getBoundingClientRect()).toBe(toggleRect);
        });

        it('forces a context-menu placement only while pointer-opened', () => {
            const wrapper = mountMenu();

            wrapper.vm.open({ clientX: 10, clientY: 10 });

            const { popperConfig } = instanceOf(wrapper).config;
            const defaults = { placement: 'bottom-end', modifiers: [] };
            expect(popperConfig(undefined, defaults).placement).toBe('bottom-start');

            // Closed again, the menu is back to whatever alignEnd asked for.
            instanceOf(wrapper).hide();
            expect(popperConfig(undefined, defaults)).toBe(defaults);
        });

        it('repositions an already-open menu instead of being ignored by Bootstrap', () => {
            const wrapper = mountMenu();

            wrapper.vm.open({ clientX: 10, clientY: 10 });
            wrapper.vm.open({ clientX: 200, clientY: 90 });

            const dropdown = instanceOf(wrapper);
            expect(dropdown.hides).toBe(1);
            expect(dropdown.shows).toBe(2);
            expect(dropdown.config.reference.getBoundingClientRect().left).toBe(200);
        });

        it('goes back to the toggle once the menu is closed', () => {
            const wrapper = mountMenu();
            const toggle = wrapper.find('a[data-bs-toggle="dropdown"]').element;
            toggle.getBoundingClientRect = () => ({ left: 5, top: 5 });

            wrapper.vm.open({ clientX: 120, clientY: 40 });
            instanceOf(wrapper).hide();

            expect(instanceOf(wrapper).config.reference.getBoundingClientRect().left).toBe(5);
        });

        it('replaces an instance the data-api created, which cannot be re-pointed', () => {
            const wrapper = mountMenu();
            const toggle = wrapper.find('a[data-bs-toggle="dropdown"]').element;
            const fromDataApi = new DropdownStub(toggle);

            wrapper.vm.open({ clientX: 1, clientY: 2 });

            expect(fromDataApi.disposed).toBe(true);
            expect(instanceOf(wrapper)).not.toBe(fromDataApi);
        });

        it('reuses its own instance across opens', () => {
            const wrapper = mountMenu();

            wrapper.vm.open({ clientX: 1, clientY: 2 });
            const first = instanceOf(wrapper);
            instanceOf(wrapper).hide();
            wrapper.vm.open({ clientX: 3, clientY: 4 });

            expect(instanceOf(wrapper)).toBe(first);
            expect(first.disposed).toBe(false);
        });

        it('disposes its instance on unmount, so Bootstrap does not keep the element alive', () => {
            const wrapper = mountMenu();
            wrapper.vm.open({ clientX: 1, clientY: 2 });
            const dropdown = instanceOf(wrapper);

            wrapper.unmount();

            expect(dropdown.disposed).toBe(true);
        });

        it('emits open for the consumer to lazy-load on, exactly as a toggle click does', () => {
            const wrapper = mountMenu();

            wrapper.vm.open({ clientX: 1, clientY: 2 });

            expect(wrapper.emitted('open')).toHaveLength(1);
        });

        it('falls back to clicking the toggle when Bootstrap is not around', () => {
            delete globalThis.bootstrap;
            const wrapper = mountMenu();
            const toggle = wrapper.find('a[data-bs-toggle="dropdown"]').element;
            let clicks = 0;
            toggle.addEventListener('click', () => { clicks += 1; });

            wrapper.vm.open({ clientX: 1, clientY: 2 });

            // Without Bootstrap there is no menu state to inspect and no positioning to be
            // had — the toggle's own click is all that is left, and it must still happen.
            expect(clicks).toBe(1);
        });
    });
});
