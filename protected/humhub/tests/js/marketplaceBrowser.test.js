import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { config, flushPromises, mount } from '@vue/test-utils';
import MarketplaceBrowser from '../../modules/marketplace/vue/MarketplaceBrowser.vue';
import CardDirectory from '../../vue/CardDirectory.vue';
import DropdownMenu from '../../vue/DropdownMenu.vue';
import UiModal from '../../vue/UiModal.vue';
import { marketplaceModule } from './support/marketplaceFixtures.mjs';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

// `v-additions` is registered by the island runtime on the real app; DropdownMenu's `html`
// entry branch carries it (see dropdownMenu.test.js for the same stub).
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

const listEnvelope = (results, updateCount = 0) => ({ results, total: results.length, page: 1, pageSize: 24, pages: 1, updateCount });

const mountBrowser = () => mount(MarketplaceBrowser, {
    props: {
        filters: [
            { key: 'q', type: 'text', label: 'Search' },
            { key: 'tag', type: 'select', label: 'Type', options: [{ value: 'official', label: 'Official' }, { value: 'community', label: 'Community' }] },
        ],
        settings: { includeBetaUpdates: false, includeCommunityModules: false },
        urls: { moduleAdministration: '/admin/module/list', professionalEdition: '/pe' },
        installationId: 'abc',
    },
    global: { components: { CardDirectory, DropdownMenu, UiModal } },
    attachTo: document.body,
});

const dialog = () => document.body.querySelector('.modal[role="dialog"]');
const footerButton = (label) => [...dialog().querySelectorAll('.modal-footer button, .modal-footer a')].find((b) => b.textContent.trim() === label);
const dialogTitle = () => dialog().querySelector('.modal-title').textContent;
const click = (element) => element.dispatchEvent(new MouseEvent('click', { bubbles: true }));
const deferred = () => {
    let resolve;
    let reject;
    const promise = new Promise((done, fail) => {
        resolve = done;
        reject = fail;
    });
    return { promise, resolve, reject };
};
const postsTo = (part) => globalThis.humhubStubs.client.post.mock.calls.filter(([url]) => url.includes(part));
const patches = () => globalThis.humhubStubs.client.ajax.mock.calls.filter(([, cfg]) => cfg && cfg.method === 'PATCH');
const cardAction = (wrapper, id) => wrapper.find(`[data-id="${id}"] .c-module-card__action`);
const updateAll = (wrapper) => wrapper.find('.c-marketplace__update-all');
const updateAllSlot = (wrapper) => wrapper.find('.c-marketplace__update-all-slot');
// The list requests of the grid (not the settings dialog's purchased modules).
const moduleListCalls = () => globalThis.humhubStubs.client.get.mock.calls.filter(([url]) => url.includes('marketplace/module?') && !url.includes('tag=purchased'));

// The update presentation ("Updated!", the remove animation) is skipped under
// `prefers-reduced-motion`, which is what the tests about the flow itself use.
const setReducedMotion = (reduce) => {
    window.matchMedia = vi.fn(() => ({ matches: reduce, addEventListener() {}, removeEventListener() {} }));
};

describe('MarketplaceBrowser', () => {
    let modules;
    let wrapper;

    beforeEach(() => {
        document.body.replaceChildren();
        window.history.replaceState(null, '', '/marketplace/browse');
        setReducedMotion(true);
        modules = [
            marketplaceModule(),
            marketplaceModule({ id: 'community-x', name: 'Community X', isCommunity: true, isThirdParty: true, badge: 'community' }),
        ];
        globalThis.humhubStubs.client.get = vi.fn((url) => Promise.resolve(
            url.includes('marketplace/module') ? listEnvelope(modules, 1) : {},
        ));
        globalThis.humhubStubs.client.post = vi.fn((url) => Promise.resolve(
            url.includes('/enable')
                ? { id: 'calendar-x', isEnabled: true, configUrl: null }
                : { ...modules[0], installedVersion: '2.0.0' },
        ));
        globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ includeBetaUpdates: false, includeCommunityModules: true }));
    });

    afterEach(() => {
        wrapper?.unmount();
        wrapper = null;
        delete window.matchMedia;
        vi.useRealTimers();
    });

    const mountIt = async () => {
        wrapper = mountBrowser();
        await flushPromises();
        return wrapper;
    };

    it('lists the modules below the toolbar with its title and actions', async () => {
        await mountIt();

        expect(wrapper.findAll('.c-module-card__title').map((n) => n.text())).toEqual(['Calendar X', 'Community X']);
        expect(wrapper.find('.c-page-toolbar__title').text()).toBe('Marketplace');
        expect(wrapper.find('.c-page-toolbar').text()).not.toContain('Find all the modules');
        expect(wrapper.find('#marketplace-include-community').exists()).toBe(false);

        const settings = wrapper.find('.c-page-toolbar__actions button.c-marketplace__settings');
        expect(settings.attributes('aria-label')).toBe('Marketplace Settings');
        expect(settings.attributes('title')).toBe('Marketplace Settings');
        expect(settings.classes()).toEqual(expect.arrayContaining(['btn', 'btn-secondary', 'c-icon-button']));
        expect(settings.find('.ti-settings').exists()).toBe(true);
        expect(wrapper.find('.dropdown-menu').exists()).toBe(false);

        expect(updateAllSlot(wrapper).classes()).not.toContain('is-collapsed');
        expect(updateAll(wrapper).attributes('aria-label')).toBe('Update all modules');
        expect(updateAll(wrapper).attributes('title')).toBe('Update All');
        expect(updateAll(wrapper).classes()).toContain('btn-accent');
    });

    it('collapses the "Update all" button when there is nothing to update', async () => {
        globalThis.humhubStubs.client.get = vi.fn((url) => Promise.resolve(url.includes('marketplace/module') ? listEnvelope(modules, 0) : {}));
        await mountIt();

        expect(updateAllSlot(wrapper).classes()).toContain('is-collapsed');
        expect(updateAllSlot(wrapper).attributes('aria-hidden')).toBe('true');
        expect(updateAll(wrapper).attributes('tabindex')).toBe('-1');
    });

    describe('the core version notice', () => {
        const withCoreVersion = (info) => {
            globalThis.humhubStubs.client.get = vi.fn((url) => Promise.resolve(
                url.includes('core-version') ? info : listEnvelope(modules, 1),
            ));
        };

        it('shows a banner when a newer HumHub version exists', async () => {
            withCoreVersion({ latest: '1.21.0', updateAvailable: true, updateUrl: 'https://www.humhub.com/update' });
            await mountIt();

            const notice = wrapper.find('.c-card-directory__notice .c-marketplace-notice');
            expect(notice.text()).toContain('A new update is available (HumHub 1.21.0)!');
            expect(notice.find('a').attributes('href')).toBe('https://www.humhub.com/update');
        });

        it.each([
            ['up to date', { latest: '1.20.0', updateAvailable: false }],
            ['unknown', {}],
        ])('shows nothing when the installation is %s', async (_, info) => {
            withCoreVersion(info);
            await mountIt();

            expect(wrapper.find('.c-card-directory__notice').exists()).toBe(false);
        });
    });

    it('filters by the type of a clicked pill', async () => {
        await mountIt();
        const setFilter = vi.spyOn(wrapper.vm.$refs.directory, 'setFilter');

        await wrapper.find('[data-id="community-x"] button.c-module-card__badge--community').trigger('click');
        await flushPromises();

        expect(setFilter).toHaveBeenCalledWith('tag', 'community');
        expect(moduleListCalls().at(-1)[0]).toContain('tag=community');
    });

    it('marks the search the list was loaded with in the cards', async () => {
        window.history.replaceState(null, '', '/marketplace/browse?q=cal');
        await mountIt();

        expect(moduleListCalls()[0][0]).toContain('q=cal');
        expect(wrapper.find('[data-id="calendar-x"] .c-module-card__title mark').text()).toBe('Cal');
        expect(wrapper.find('[data-id="community-x"] mark').exists()).toBe(false);
    });

    describe('installing', () => {
        it('confirms first, installs, turns the card "Installed" and activates', async () => {
            await mountIt();

            await cardAction(wrapper, 'calendar-x').trigger('click');
            await flushPromises();

            expect(dialogTitle()).toBe('Install Module');
            expect(dialog().querySelector('.c-install-dialog__name').textContent).toBe('Calendar X');
            expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();

            click(footerButton('Install'));
            await flushPromises();

            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/marketplace/module/calendar-x/install');
            expect(cardAction(wrapper, 'calendar-x').text()).toBe('Installed');
            expect(dialogTitle()).toBe('Activate Module');

            click(footerButton('Activate'));
            await flushPromises();

            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/module/calendar-x/enable');
            expect(dialog()).toBeNull();
        });

        it('hands the focus to the installed card after closing the dialog', async () => {
            await mountIt();

            cardAction(wrapper, 'calendar-x').element.focus();
            await cardAction(wrapper, 'calendar-x').trigger('click');
            await flushPromises();
            click(footerButton('Install'));
            await flushPromises();
            click(footerButton('Close'));
            await flushPromises();

            expect(dialog()).toBeNull();
            expect(document.activeElement).toBe(wrapper.find('[data-id="calendar-x"] a.c-module-card__title').element);
        });

        it('shows the community warning before installing a community module', async () => {
            await mountIt();

            await cardAction(wrapper, 'community-x').trigger('click');
            await flushPromises();

            expect(dialog().querySelector('.c-mp-notice--warning').textContent).toContain('Unverified Community');
            click(footerButton('Cancel'));
            await flushPromises();
            expect(dialog()).toBeNull();
            expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();
        });
    });

    describe('buying', () => {
        beforeEach(() => {
            modules.splice(0, modules.length,
                marketplaceModule({ id: 'paid-x', name: 'Paid <X>', availability: 'buy', price: { amount: 45, currency: 'EUR', onRequest: false }, checkoutUrl: 'https://checkout/paid-x' }),
                marketplaceModule({ id: 'paid-c', name: 'Paid C', availability: 'buy', isCommunity: true, isThirdParty: true, badge: 'community', price: { amount: 9, currency: 'EUR', onRequest: false }, checkoutUrl: 'https://checkout/paid-c' }));
        });

        const addKey = async (key) => {
            click(footerButton('Add License'));
            await flushPromises();
            const input = dialog().querySelector('.c-licence-field__input');
            input.value = key;
            input.dispatchEvent(new Event('input'));
            await flushPromises();
            click(footerButton('Install'));
            await flushPromises();
        };

        it('opens the install dialog on its buy step with the purchase page', async () => {
            await mountIt();

            await cardAction(wrapper, 'paid-x').trigger('click');
            await flushPromises();

            expect(dialogTitle()).toBe('Buy Module');
            expect(dialog().querySelector('.c-mp-dialog__text b').textContent).toBe('Paid <X>');
            expect(dialog().querySelector('a.c-install-dialog__checkout').getAttribute('href')).toBe('https://checkout/paid-x');
        });

        it('warns on the buy step of a community module', async () => {
            await mountIt();

            await cardAction(wrapper, 'paid-c').trigger('click');
            await flushPromises();

            expect(dialogTitle()).toBe('Buy Module');
            expect(dialog().querySelector('.c-mp-notice--warning')).not.toBeNull();
        });

        it('registers the key and installs the module', async () => {
            globalThis.humhubStubs.client.post = vi.fn((url) => Promise.resolve(url.includes('licence-key')
                ? { message: 'Module license added!' }
                : { ...modules[0], installedVersion: '2.0.0', availability: 'install' }));
            await mountIt();
            const loads = moduleListCalls().length;

            await cardAction(wrapper, 'paid-x').trigger('click');
            await flushPromises();
            await addKey('KEY-1');

            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/marketplace/licence-key', { data: { licenceKey: 'KEY-1' } });
            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/marketplace/module/paid-x/install');
            expect(cardAction(wrapper, 'paid-x').text()).toBe('Installed');
            expect(dialogTitle()).toBe('Activate Module');

            click(footerButton('Close'));
            await flushPromises();
            expect(dialog()).toBeNull();
            expect(moduleListCalls().length).toBe(loads);
        });

        it('reloads the list on closing when a key was registered but did not license the module', async () => {
            globalThis.humhubStubs.client.post = vi.fn((url) => (url.includes('licence-key')
                ? Promise.resolve({ message: 'Module license added!' })
                : Promise.reject({ status: 422, errors: { id: ['This module cannot be installed.'] } })));
            await mountIt();
            const loads = moduleListCalls().length;

            await cardAction(wrapper, 'paid-x').trigger('click');
            await flushPromises();
            await addKey('KEY-1');

            expect(dialog().querySelector('.invalid-feedback').textContent).toBe('This License Key does not license Paid <X>.');
            expect(moduleListCalls().length).toBe(loads);

            click(dialog().querySelector('.btn-close'));
            await flushPromises();
            expect(dialog()).toBeNull();
            expect(moduleListCalls().length).toBe(loads + 1);
        });
    });

    describe('the settings dialog', () => {
        const openSettings = async () => {
            await wrapper.find('button.c-marketplace__settings').trigger('click');
            await flushPromises();
        };
        const tab = (label) => [...dialog().querySelectorAll('[role="tab"]')].find((t) => t.textContent.trim() === label);

        it('opens from the toolbar on the licences, with the installation id', async () => {
            await mountIt();
            await openSettings();

            expect(dialogTitle()).toBe('Marketplace Settings');
            expect(tab('Licenses').getAttribute('aria-selected')).toBe('true');
            expect(dialog().querySelector('.c-mp-settings__installation').textContent).toBe('Installation ID: abc');
            expect(dialog().querySelector('.c-mp-settings__admin').getAttribute('href')).toBe('/admin/module/list');
        });

        it('reloads the list and the filter options after a setting changed, and stays open', async () => {
            await mountIt();
            const reloadFilterOptions = vi.spyOn(wrapper.vm.$refs.directory, 'reloadFilterOptions');
            const loads = moduleListCalls().length;
            await openSettings();
            click(tab('Settings'));
            await flushPromises();

            const community = [...dialog().querySelectorAll('input[data-setting]')][1];
            community.checked = true;
            community.dispatchEvent(new Event('change'));
            await flushPromises();
            const acknowledge = dialog().querySelector('.c-setting-row.is-expanded input[type="checkbox"]:not([data-setting])');
            acknowledge.checked = true;
            acknowledge.dispatchEvent(new Event('change'));
            await flushPromises();
            click([...dialog().querySelectorAll('.c-setting-row button')].find((b) => b.textContent.trim() === 'Confirm'));
            await flushPromises();

            expect(patches()[0][1]).toMatchObject({ data: { includeCommunityModules: 1 } });
            expect(dialog()).not.toBeNull();
            expect(wrapper.vm.settingsState.includeCommunityModules).toBe(true);
            expect(moduleListCalls().length).toBe(loads + 1);
            expect(reloadFilterOptions).toHaveBeenCalledTimes(1);
        });

        it('reloads the list after a key was registered there', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ message: 'Module license added!' }));
            await mountIt();
            await openSettings();
            const loads = moduleListCalls().length;

            const input = dialog().querySelector('.c-licence-field__input');
            input.value = 'KEY-1';
            input.dispatchEvent(new Event('input'));
            await flushPromises();
            click(dialog().querySelector('.c-mp-settings__add'));
            await flushPromises();

            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/marketplace/licence-key', { data: { licenceKey: 'KEY-1' } });
            expect(moduleListCalls().length).toBe(loads + 1);
            expect(dialog()).not.toBeNull();
        });

        it('opens the install dialog on its confirm step for a licence row', async () => {
            const licensed = marketplaceModule({ id: 'licensed-x', name: 'Licensed X', purchased: true, licenceKey: 'KEY-9' });
            globalThis.humhubStubs.client.get = vi.fn((url) => Promise.resolve(url.includes('tag=purchased') ? listEnvelope([licensed]) : listEnvelope(modules, 1)));
            await mountIt();
            await openSettings();

            click(dialog().querySelector('.c-licence-list__action'));
            await flushPromises();

            expect(dialogTitle()).toBe('Install Module');
            expect(dialog().querySelector('.c-install-dialog__name').textContent).toBe('Licensed X');
            expect(document.body.querySelectorAll('.modal[role="dialog"]')).toHaveLength(1);
            expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();
        });
    });

    describe('one action at a time', () => {
        let statuses;

        beforeEach(() => {
            statuses = [];
            globalThis.humhub.modules.vue.setStatusHandler((entry) => statuses.push(entry));
        });

        afterEach(() => {
            globalThis.humhub.modules.vue.setStatusHandler(null);
        });

        it('counts an open install dialog as busy for every other install and update', async () => {
            modules.push(
                marketplaceModule({ id: 'wiki-x', name: 'Wiki X' }),
                marketplaceModule({ id: 'old-x', name: 'Old X', installedVersion: '1.0.0', updateAvailable: true }),
            );
            const pending = deferred();
            globalThis.humhubStubs.client.post = vi.fn(() => pending.promise);
            await mountIt();

            await cardAction(wrapper, 'calendar-x').trigger('click');
            await flushPromises();

            expect(cardAction(wrapper, 'wiki-x').attributes('disabled')).toBeDefined();
            expect(cardAction(wrapper, 'old-x').attributes('disabled')).toBeDefined();
            expect(updateAll(wrapper).attributes('disabled')).toBeDefined();
            expect(wrapper.vm.install(modules[2])).toBe(false);
            await wrapper.vm.updateOne(modules[3]);
            expect(postsTo('/update')).toHaveLength(0);

            click(footerButton('Install'));
            await flushPromises();
            expect(postsTo('/install')).toHaveLength(1);
            pending.resolve({ ...modules[0], installedVersion: '2.0.0' });
            await flushPromises();
            expect(cardAction(wrapper, 'wiki-x').attributes('disabled')).toBeDefined();

            click(footerButton('Close'));
            await flushPromises();
            expect(cardAction(wrapper, 'wiki-x').attributes('disabled')).toBeUndefined();
            expect(updateAll(wrapper).attributes('disabled')).toBeUndefined();
        });

        it('blocks installing while a single card update is running (not "Update all")', async () => {
            modules.push(marketplaceModule({ id: 'wiki-x', name: 'Wiki X', installedVersion: '1.0.0', updateAvailable: true }));
            const pending = deferred();
            globalThis.humhubStubs.client.post = vi.fn(() => pending.promise);
            await mountIt();

            wrapper.vm.updateOne(modules[2]);
            await flushPromises();

            expect(cardAction(wrapper, 'calendar-x').attributes('disabled')).toBeDefined();
            expect(updateAll(wrapper).attributes('disabled')).toBeDefined();
            expect(wrapper.vm.install(marketplaceModule({ id: 'other' }))).toBe(false);
            expect(dialog()).toBeNull();

            pending.resolve({ ...modules[2], installedVersion: '2.0.0', updateAvailable: false });
            await flushPromises();
            expect(cardAction(wrapper, 'calendar-x').attributes('disabled')).toBeUndefined();
        });

        it('ignores a second single update while one runs', async () => {
            modules.push(
                marketplaceModule({ id: 'wiki-x', name: 'Wiki X', installedVersion: '1.0.0', updateAvailable: true }),
                marketplaceModule({ id: 'old-x', name: 'Old X', installedVersion: '1.0.0', updateAvailable: true }),
            );
            const pending = deferred();
            globalThis.humhubStubs.client.post = vi.fn(() => pending.promise);
            await mountIt();

            wrapper.vm.updateOne(modules[2]);
            await flushPromises();
            await wrapper.vm.updateOne(modules[3]);

            expect(postsTo('/update')).toHaveLength(1);
            pending.resolve({ ...modules[2], installedVersion: '2.0.0', updateAvailable: false });
            await flushPromises();
        });

        it('opens every install dialog as a fresh instance', async () => {
            await mountIt();

            wrapper.vm.install(modules[0]);
            await flushPromises();
            click(footerButton('Install'));
            await flushPromises();
            expect(dialogTitle()).toBe('Activate Module');
            click(footerButton('Close'));
            await flushPromises();

            wrapper.vm.install({ ...modules[0], id: 'wiki-x', name: 'Wiki X' });
            await flushPromises();
            expect(dialogTitle()).toBe('Install Module');
            expect(dialog().querySelector('.c-install-dialog__name').textContent).toBe('Wiki X');
        });

        describe('updating a module', () => {
            it('says "Updated!", removes the card and shows it again with the updated module', async () => {
                setReducedMotion(false);
                vi.useFakeTimers();
                modules.splice(0, 1, marketplaceModule({ installedVersion: '1.0.0', updateAvailable: true, latestCompatibleVersion: '2.0.0' }));
                const pending = deferred();
                globalThis.humhubStubs.client.post = vi.fn(() => pending.promise);
                await mountIt();
                const card = () => wrapper.find('[data-id="calendar-x"] .c-module-card');

                await cardAction(wrapper, 'calendar-x').trigger('click');
                await flushPromises();
                expect(card().classes()).toContain('is-updating');
                expect(cardAction(wrapper, 'calendar-x').text()).toBe('Updating');

                pending.resolve({ ...modules[0], installedVersion: '2.0.0', updateAvailable: false });
                await flushPromises();
                expect(cardAction(wrapper, 'calendar-x').text()).toBe('Updated!');
                expect(card().find('.c-module-card__badge--update').exists()).toBe(true);

                await vi.advanceTimersByTimeAsync(1199);
                expect(card().classes()).not.toContain('is-removing');
                await vi.advanceTimersByTimeAsync(1);
                expect(card().classes()).toContain('is-removing');
                expect(statuses).toEqual([]);

                await vi.advanceTimersByTimeAsync(360);
                await flushPromises();
                expect(card().classes()).toContain('is-entering');
                expect(card().classes()).not.toContain('is-updating');
                expect(cardAction(wrapper, 'calendar-x').text()).toBe('Installed');
                expect(card().find('button.c-module-card__badge--official').exists()).toBe(true);
                expect(card().find('.c-module-card__version').text()).toBe('2.0.0');
                expect(statuses.map((entry) => entry.level)).toEqual(['success']);
                expect(updateAllSlot(wrapper).classes()).toContain('is-collapsed');
            });

            it('replaces the card at once under reduced motion', async () => {
                modules.splice(0, 1, marketplaceModule({ installedVersion: '1.0.0', updateAvailable: true }));
                globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ ...modules[0], installedVersion: '2.0.0', updateAvailable: false }));
                await mountIt();

                await cardAction(wrapper, 'calendar-x').trigger('click');
                await flushPromises();

                expect(cardAction(wrapper, 'calendar-x').text()).toBe('Installed');
                expect(wrapper.vm.stateOf('calendar-x').update).toBe('done');
            });

            it('announces the result in one live region and keeps the focus on the replaced card', async () => {
                modules.splice(0, 1, marketplaceModule({ installedVersion: '1.0.0', updateAvailable: true }));
                globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ ...modules[0], installedVersion: '2.0.0', updateAvailable: false }));
                await mountIt();
                const live = () => wrapper.findAll('[aria-live]');

                expect(live()).toHaveLength(1);
                expect(live()[0].attributes('role')).toBe('status');
                expect(live()[0].classes()).toContain('visually-hidden');
                expect(live()[0].text()).toBe('');
                expect(wrapper.find('.c-module-card [role="status"]').exists()).toBe(false);

                cardAction(wrapper, 'calendar-x').element.focus();
                await cardAction(wrapper, 'calendar-x').trigger('click');
                await flushPromises();

                expect(live()[0].text()).toBe('Module "Calendar X" has been updated to version 2.0.0 successfully.');
                expect(document.activeElement).toBe(wrapper.find('[data-id="calendar-x"] a.c-module-card__title').element);
            });

            it('leaves the focus where it is when it was not on the card', async () => {
                modules.splice(0, 1, marketplaceModule({ installedVersion: '1.0.0', updateAvailable: true }));
                globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ ...modules[0], installedVersion: '2.0.0', updateAvailable: false }));
                await mountIt();
                const settings = wrapper.find('.c-marketplace__settings').element;
                settings.focus();

                await wrapper.vm.updateOne(modules[0]);
                await flushPromises();

                expect(document.activeElement).toBe(settings);
            });

            it('announces a failed update', async () => {
                modules.splice(0, 1, marketplaceModule({ installedVersion: '1.0.0', updateAvailable: true }));
                globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({ message: 'Download failed' }));
                await mountIt();

                await cardAction(wrapper, 'calendar-x').trigger('click');
                await flushPromises();

                expect(wrapper.find('[aria-live]').text()).toBe('Calendar X: Download failed');
            });

            it('shows the error of a failed update', async () => {
                modules.splice(0, 1, marketplaceModule({ installedVersion: '1.0.0', updateAvailable: true }));
                globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({ message: 'Download failed' }));
                await mountIt();

                await cardAction(wrapper, 'calendar-x').trigger('click');
                await flushPromises();

                expect(wrapper.find('[data-id="calendar-x"] [role="alert"]').text()).toBe('Download failed');
                expect(cardAction(wrapper, 'calendar-x').text()).toBe('Update');
                expect(statuses.map((entry) => entry.level)).toEqual(['error']);
            });
        });

        const setUpUpdates = () => {
            const outdated = [
                marketplaceModule({ installedVersion: '1.0.0', updateAvailable: true, availability: 'install' }),
                marketplaceModule({ id: 'wiki-x', name: 'Wiki X', installedVersion: '1.0.0', updateAvailable: true }),
            ];
            modules.splice(0, modules.length, ...outdated);
            let updateCount = 2;
            globalThis.humhubStubs.client.get = vi.fn((url) => Promise.resolve(
                url.includes('marketplace/module') ? listEnvelope(modules, updateCount) : {},
            ));
            const updates = [];
            globalThis.humhubStubs.client.post = vi.fn((url) => {
                const next = deferred();
                updates.push({ url, ...next });
                return next.promise;
            });
            return { outdated, updates, setUpdateCount: (count) => { updateCount = count; } };
        };

        it('starts a single queue on a double click of "Update all"', async () => {
            const { outdated, updates, setUpdateCount } = setUpUpdates();
            await mountIt();

            updateAll(wrapper).element.click();
            await wrapper.vm.$nextTick();
            updateAll(wrapper).element.click();
            await flushPromises();

            const candidateFetches = globalThis.humhubStubs.client.get.mock.calls.filter(([url]) => url.includes('status=update'));
            expect(candidateFetches).toHaveLength(1);
            expect(postsTo('/update')).toHaveLength(1);
            expect(wrapper.find('[data-id="wiki-x"] .c-module-card__action').text()).toBe('Queued');

            updates[0].resolve({ ...outdated[0], installedVersion: '2.0.0', updateAvailable: false });
            await flushPromises();
            expect(wrapper.find('[aria-live]').text()).toBe('Module "Calendar X" has been updated to version 2.0.0 successfully.');
            setUpdateCount(0);
            updates[1].resolve({ ...outdated[1], installedVersion: '2.0.0', updateAvailable: false });
            await flushPromises();

            expect(postsTo('/update')).toHaveLength(2);
            expect(statuses.map((entry) => entry.level)).toEqual(['success']);
            expect(wrapper.find('[aria-live]').text()).toBe('Module "Wiki X" has been updated to version 2.0.0 successfully.');
            expect(wrapper.vm.updatingAll).toBe(false);
            expect(updateAllSlot(wrapper).classes()).toContain('is-collapsed');
        });

        it('turns into "Cancel all" while running, which stops after the running update', async () => {
            const { outdated, updates } = setUpUpdates();
            await mountIt();

            await updateAll(wrapper).trigger('click');
            await flushPromises();

            expect(updateAll(wrapper).classes()).toEqual(expect.arrayContaining(['btn-warning', 'is-running']));
            expect(updateAll(wrapper).attributes('aria-label')).toBe('Cancel all module updates');
            expect(updateAll(wrapper).attributes('title')).toBe('Cancel All');
            expect(updateAll(wrapper).attributes('disabled')).toBeUndefined();

            await updateAll(wrapper).trigger('click');
            expect(updateAll(wrapper).attributes('disabled')).toBeDefined();
            updates[0].resolve({ ...outdated[0], installedVersion: '2.0.0', updateAvailable: false });
            await flushPromises();

            expect(postsTo('/update')).toHaveLength(1);
            expect(statuses).toEqual([]);
            expect(wrapper.vm.updatingAll).toBe(false);
            expect(updateAll(wrapper).classes()).toContain('btn-accent');
            expect(cardAction(wrapper, 'wiki-x').text()).toBe('Update');
        });

        it('ignores single updates and installs while "Update all" runs', async () => {
            setUpUpdates();
            await mountIt();

            await updateAll(wrapper).trigger('click');
            await flushPromises();

            await wrapper.vm.updateOne(modules[1]);
            expect(wrapper.vm.install(marketplaceModule({ id: 'other' }))).toBe(false);
            expect(wrapper.vm.buy(marketplaceModule({ id: 'other', availability: 'buy' }))).toBe(false);

            // Only "Update all"'s own request went out — the blocked single update, install and
            // buy never called the API.
            expect(postsTo('/update')).toHaveLength(1);
            expect(dialog()).toBeNull();
            expect(postsTo('/install')).toHaveLength(0);
        });
    });
});
