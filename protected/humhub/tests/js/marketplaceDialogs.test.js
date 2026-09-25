import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { flushPromises, mount } from '@vue/test-utils';
import InstallDialog, { PROGRESS_LIMIT } from '../../modules/marketplace/vue/components/InstallDialog.vue';
import SettingsDialog, { ADDED_MS } from '../../modules/marketplace/vue/components/SettingsDialog.vue';
import UiModal from '../../vue/UiModal.vue';
import { marketplaceModule } from './support/marketplaceFixtures.mjs';

await import('../../resources/js/humhub/humhub.url.js');
await import('../../resources/js/humhub/humhub.vue.js');

const mountDialog = (component, props = {}) => mount(component, {
    props,
    global: { components: { UiModal } },
    attachTo: document.body,
});

const dialog = () => document.body.querySelector('.modal[role="dialog"]');
const title = () => dialog().querySelector('.modal-title').textContent;
const footerButtons = () => [...dialog().querySelectorAll('.modal-footer button, .modal-footer a')];
const footerButton = (label) => footerButtons().find((b) => b.textContent.trim() === label);
const click = (element) => element.dispatchEvent(new MouseEvent('click', { bubbles: true }));
const type = (input, value) => {
    input.value = value;
    input.dispatchEvent(new Event('input'));
};
const keydown = (element, key) => element.dispatchEvent(new KeyboardEvent('keydown', { key, bubbles: true }));
const deferred = () => {
    let resolve;
    let reject;
    const promise = new Promise((done, fail) => {
        resolve = done;
        reject = fail;
    });
    return { promise, resolve, reject };
};
const setReducedMotion = (reduce) => {
    window.matchMedia = vi.fn(() => ({ matches: reduce, addEventListener() {}, removeEventListener() {} }));
};

let statuses;
let wrapper;

beforeEach(() => {
    document.body.replaceChildren();
    setReducedMotion(true);
    statuses = [];
    globalThis.humhub.modules.vue.setStatusHandler((entry) => statuses.push(entry));
    globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ results: [] }));
    globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({}));
    globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({}));
});

afterEach(() => {
    wrapper?.unmount();
    wrapper = null;
    globalThis.humhub.modules.vue.setStatusHandler(null);
    delete window.matchMedia;
    vi.useRealTimers();
});

describe('InstallDialog', () => {
    const paid = (overrides = {}) => marketplaceModule({
        name: 'Paid <b>X</b>',
        availability: 'buy',
        price: { amount: 45, currency: 'EUR', onRequest: false },
        checkoutUrl: 'https://checkout/paid-x',
        ...overrides,
    });
    const keyInput = () => dialog().querySelector('.c-licence-field__input');
    const postsTo = (part) => globalThis.humhubStubs.client.post.mock.calls.filter(([url]) => url.includes(part));

    describe('the buy step', () => {
        it('names the module as text, offers the purchase page and adding a key', () => {
            wrapper = mountDialog(InstallDialog, { module: paid(), initialStep: 'buy' });

            expect(title()).toBe('Buy Module');
            const text = dialog().querySelector('.c-mp-dialog__text');
            expect(text.textContent).toBe('To install Paid <b>X</b>, you need a License Key. Buy one with Buy License Key, or choose Add License if you already have one.');
            expect([...text.querySelectorAll('b')].map((b) => b.textContent)).toEqual(['Paid <b>X</b>', 'Buy License Key', 'Add License']);
            expect(text.querySelector('b b')).toBeNull();

            const checkout = dialog().querySelector('a.c-install-dialog__checkout');
            expect(checkout.getAttribute('href')).toBe('https://checkout/paid-x');
            expect(checkout.getAttribute('target')).toBe('_blank');
            expect(checkout.getAttribute('rel')).toBe('noopener noreferrer');
            expect(checkout.textContent).toContain('(opens in a new tab)');
            expect(footerButtons().map((b) => b.textContent.trim())).toEqual(['Cancel', 'Add License']);
        });

        it('closes on Cancel', () => {
            wrapper = mountDialog(InstallDialog, { module: paid(), initialStep: 'buy' });

            click(footerButton('Cancel'));

            expect(wrapper.emitted('close')).toHaveLength(1);
        });

        it('moves to the key step, and back', async () => {
            wrapper = mountDialog(InstallDialog, { module: paid(), initialStep: 'buy' });
            await flushPromises();

            click(footerButton('Add License'));
            await flushPromises();

            expect(title()).toBe('Install Module');
            expect(dialog().querySelector('.c-mp-dialog__text').textContent).toBe('To install Paid <b>X</b>, enter the License Key you purchased.');
            expect(keyInput().getAttribute('placeholder')).toBe('XXXX-XXXX-XXXX-XXXX');
            expect(dialog().querySelector(`label[for="${keyInput().id}"]`).textContent).toBe('License Key');
            expect(document.activeElement).toBe(keyInput());
            expect(footerButton('Install').disabled).toBe(true);
            expect(dialog().querySelector('a.c-install-dialog__checkout')).toBeNull();

            click(footerButton('Back'));
            await flushPromises();
            expect(title()).toBe('Buy Module');
        });
    });

    describe('the key step', () => {
        const openKeyStep = async (module = paid()) => {
            wrapper = mountDialog(InstallDialog, { module, initialStep: 'buy' });
            click(footerButton('Add License'));
            await flushPromises();
        };

        it('checks the key with a spinner in the field, and keeps a rejected key with its message', async () => {
            const pending = deferred();
            globalThis.humhubStubs.client.post = vi.fn(() => pending.promise);
            await openKeyStep();

            type(keyInput(), ' wrong ');
            await flushPromises();
            expect(footerButton('Install').disabled).toBe(false);
            click(footerButton('Install'));
            await flushPromises();

            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/marketplace/licence-key', { data: { licenceKey: 'wrong' } });
            expect(dialog().querySelector('.c-licence-field').classList.contains('is-checking')).toBe(true);
            expect(dialog().querySelector('.c-licence-field__icon')).not.toBeNull();
            expect(footerButton('Install').disabled).toBe(true);
            expect(footerButton('Back').disabled).toBe(true);

            pending.reject({ status: 422, errors: { licenceKey: ['Invalid module license key!'] } });
            await flushPromises();

            expect(title()).toBe('Install Module');
            expect(keyInput().value).toBe(' wrong ');
            expect(keyInput().getAttribute('aria-invalid')).toBe('true');
            const feedback = dialog().querySelector('.invalid-feedback');
            expect(feedback.textContent).toBe('Invalid module license key!');
            expect(keyInput().getAttribute('aria-describedby')).toBe(feedback.id);
            expect(postsTo('/install')).toHaveLength(0);
            expect(wrapper.emitted('registered')).toBeUndefined();

            type(keyInput(), 'other');
            await flushPromises();
            expect(dialog().querySelector('.invalid-feedback')).toBeNull();
        });

        it('shows the message of an unreachable marketplace at the field', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({ status: 503, message: 'Could not connect to HumHub API!' }));
            await openKeyStep();

            type(keyInput(), 'KEY-1');
            keydown(keyInput(), 'Enter');
            await flushPromises();

            expect(dialog().querySelector('.invalid-feedback').textContent).toBe('Could not connect to HumHub API!');
        });

        it('registers the key, then installs the now purchased module', async () => {
            const install = deferred();
            globalThis.humhubStubs.client.post = vi.fn((url) => (url.includes('licence-key') ? Promise.resolve({ message: 'Module license added!' }) : install.promise));
            const module = paid();
            await openKeyStep(module);

            type(keyInput(), 'KEY-1');
            keydown(keyInput(), 'Enter');
            await flushPromises();

            expect(wrapper.emitted('registered')).toHaveLength(1);
            expect(postsTo('/install')).toEqual([['/api/v2/marketplace/module/calendar-x/install']]);
            expect(dialog().querySelector('[role="progressbar"]')).not.toBeNull();

            install.resolve({ ...module, installedVersion: '2.0.0', availability: 'install' });
            await flushPromises();

            expect(wrapper.emitted('installed')).toEqual([[{ ...module, installedVersion: '2.0.0', availability: 'install' }]]);
            expect(title()).toBe('Activate Module');
        });

        it('cannot be closed while the key is checked', async () => {
            const pending = deferred();
            globalThis.humhubStubs.client.post = vi.fn(() => pending.promise);
            await openKeyStep();

            type(keyInput(), 'KEY-1');
            click(footerButton('Install'));
            await flushPromises();

            expect(dialog().querySelector('.btn-close').disabled).toBe(true);
            document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
            dialog().dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
            click(dialog());
            wrapper.vm.close();
            await flushPromises();
            expect(wrapper.emitted('close')).toBeUndefined();

            pending.reject({ status: 422, errors: { licenceKey: ['Invalid module license key!'] } });
            await flushPromises();
            expect(dialog().querySelector('.btn-close').disabled).toBe(false);
        });

        it('starts no installation once unmounted during the key check', async () => {
            const pending = deferred();
            globalThis.humhubStubs.client.post = vi.fn((url) => (url.includes('licence-key') ? pending.promise : Promise.resolve({ ...paid(), installedVersion: '2.0.0' })));
            await openKeyStep();

            type(keyInput(), 'KEY-1');
            click(footerButton('Install'));
            await flushPromises();
            const registered = wrapper.emitted('registered');
            wrapper.unmount();
            pending.resolve({});
            await flushPromises();

            expect(postsTo('/install')).toHaveLength(0);
            expect(registered).toBeUndefined();
            expect(statuses).toEqual([]);
            wrapper = null;
        });

        it('asks for another key when the registered one does not license the module', async () => {
            globalThis.humhubStubs.client.post = vi.fn((url) => (url.includes('licence-key')
                ? Promise.resolve({ message: 'Module license added!' })
                : Promise.reject({ status: 422, errors: { id: ['This module cannot be installed.'] } })));
            await openKeyStep();

            type(keyInput(), 'OTHER-KEY');
            click(footerButton('Install'));
            await flushPromises();

            expect(wrapper.emitted('registered')).toHaveLength(1);
            expect(keyInput()).not.toBeNull();
            expect(keyInput().getAttribute('aria-invalid')).toBe('true');
            expect(dialog().querySelector('.invalid-feedback').textContent).toBe('This License Key does not license Paid <b>X</b>.');
            expect(dialog().querySelector('.c-mp-dialog__error')).toBeNull();
            expect(wrapper.emitted('installed')).toBeUndefined();
        });
    });

    describe('the confirm step', () => {
        it('shows the module and installs nothing before its Install', () => {
            wrapper = mountDialog(InstallDialog, { module: marketplaceModule({ name: 'Calendar <X>' }), initialStep: 'confirm' });

            expect(title()).toBe('Install Module');
            expect(dialog().querySelector('.c-install-dialog__name').textContent).toBe('Calendar <X>');
            expect(dialog().querySelector('.c-install-dialog__version').textContent).toBe('2.0.0');
            expect(dialog().querySelector('.c-install-dialog__image').getAttribute('src')).toBe('/img/calendar.png');
            expect(dialog().querySelector('.c-mp-notice')).toBeNull();
            expect(footerButtons().map((b) => b.textContent.trim())).toEqual(['Cancel', 'Install']);
            expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();
        });

        it('carries the third-party note for a third-party module', () => {
            wrapper = mountDialog(InstallDialog, { module: marketplaceModule({ isThirdParty: true, badge: 'partner' }) });

            const notice = dialog().querySelector('.c-mp-notice');
            expect(notice.getAttribute('role')).toBe('note');
            expect(notice.classList.contains('c-mp-notice--warning')).toBe(false);
            expect(notice.textContent).toContain('This Module was developed by a third-party.');
            expect(notice.textContent).toContain('Third-party Modules are not covered by Professional Edition agreements.');
            expect(notice.textContent).not.toContain('Unverified Community');
        });

        it('adds the community warning for an unverified community module, on the buy step too', () => {
            const community = { isThirdParty: true, isCommunity: true, badge: 'community' };
            wrapper = mountDialog(InstallDialog, { module: marketplaceModule(community) });

            let notice = dialog().querySelector('.c-mp-notice');
            expect(notice.classList.contains('c-mp-notice--warning')).toBe(true);
            expect(notice.querySelector('strong').textContent).toBe('"Unverified Community"');
            expect(notice.textContent).toContain('neither tested nor maintained by the HumHub project team');
            wrapper.unmount();

            wrapper = mountDialog(InstallDialog, { module: paid(community), initialStep: 'buy' });
            notice = dialog().querySelector('.c-mp-notice');
            expect(notice.textContent).toContain('This Module was developed by a third-party.');
            expect(notice.textContent).toContain('Unverified Community');
        });

        it('shows the note only on the step it opened on', async () => {
            wrapper = mountDialog(InstallDialog, { module: paid({ isThirdParty: true }), initialStep: 'buy' });

            click(footerButton('Add License'));
            await flushPromises();

            expect(dialog().querySelector('.c-mp-notice')).toBeNull();
        });
    });

    describe('installing', () => {
        it('shows the progress and cannot be cancelled or closed', async () => {
            const install = deferred();
            globalThis.humhubStubs.client.post = vi.fn(() => install.promise);
            wrapper = mountDialog(InstallDialog, { module: marketplaceModule({ name: 'Calendar <X>' }) });

            click(footerButton('Install'));
            await flushPromises();

            expect(title()).toBe('Install Module');
            const text = dialog().querySelector('.c-mp-dialog__text');
            expect(text.textContent).toBe('Installing Calendar <X>...');
            expect(text.querySelector('b').textContent).toBe('Calendar <X>');
            const bar = dialog().querySelector('[role="progressbar"]');
            expect(bar.getAttribute('aria-valuenow')).toBe('0');
            expect(footerButtons()).toHaveLength(1);
            expect(footerButtons()[0].textContent.trim()).toBe('Installing');
            expect(footerButtons()[0].disabled).toBe(true);
            expect(dialog().querySelector('.btn-close').disabled).toBe(true);

            document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
            dialog().dispatchEvent(new MouseEvent('mousedown', { bubbles: true }));
            click(dialog());
            await flushPromises();
            expect(wrapper.emitted('close')).toBeUndefined();

            install.resolve({ ...marketplaceModule(), installedVersion: '2.0.0' });
            await flushPromises();
            expect(dialog().querySelector('.btn-close').disabled).toBe(false);
        });

        it('eases the bar towards its limit and fills it when the installation is done', async () => {
            vi.useFakeTimers();
            setReducedMotion(false);
            const install = deferred();
            globalThis.humhubStubs.client.post = vi.fn(() => install.promise);
            wrapper = mountDialog(InstallDialog, { module: marketplaceModule() });

            click(footerButton('Install'));
            await flushPromises();
            await vi.advanceTimersByTimeAsync(1200);
            const early = Number(dialog().querySelector('[role="progressbar"]').getAttribute('aria-valuenow'));
            await vi.advanceTimersByTimeAsync(30000);
            const late = Number(dialog().querySelector('[role="progressbar"]').getAttribute('aria-valuenow'));

            expect(early).toBeGreaterThan(0);
            expect(late).toBeGreaterThan(early);
            expect(late).toBeLessThanOrEqual(PROGRESS_LIMIT * 100);

            install.resolve({ ...marketplaceModule(), installedVersion: '2.0.0' });
            await flushPromises();
            expect(dialog().querySelector('[role="progressbar"]').getAttribute('aria-valuenow')).toBe('100');
            expect(wrapper.emitted('installed')).toBeUndefined();

            await vi.advanceTimersByTimeAsync(300);
            expect(wrapper.emitted('installed')).toHaveLength(1);
            expect(title()).toBe('Activate Module');
        });

        it('reports the installation and offers activating', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ ...marketplaceModule(), installedVersion: '2.0.0' }));
            wrapper = mountDialog(InstallDialog, { module: marketplaceModule() });

            click(footerButton('Install'));
            await flushPromises();

            expect(statuses).toEqual([expect.objectContaining({ level: 'success', message: 'Calendar X installed successfully.' })]);
            expect(title()).toBe('Activate Module');
            expect(dialog().querySelector('.c-mp-dialog__text').textContent)
                .toBe('You are all done! Calendar X was installed. To make it available on your network, activate it now or later in Module Administration.');
            expect(footerButtons().map((b) => b.textContent.trim())).toEqual(['Close', 'Activate']);
        });

        it('returns to the confirm step with the error of a module that cannot be installed', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({ status: 422, errors: { id: ['This module cannot be installed.'] } }));
            wrapper = mountDialog(InstallDialog, { module: marketplaceModule() });

            click(footerButton('Install'));
            await flushPromises();

            expect(title()).toBe('Install Module');
            expect(dialog().querySelector('[role="alert"]').textContent).toBe('This module cannot be installed.');
            expect(footerButton('Install').disabled).toBe(false);
            expect(wrapper.emitted('installed')).toBeUndefined();
            expect(statuses).toEqual([]);
            expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();

            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ ...marketplaceModule(), installedVersion: '2.0.0' }));
            click(footerButton('Install'));
            await flushPromises();
            expect(title()).toBe('Activate Module');
            expect(dialog().querySelector('[role="alert"]')).toBeNull();
        });

        describe('a failure without a definite answer (5xx, network)', () => {
            it('re-reads the module and continues when it is installed after all', async () => {
                globalThis.humhubStubs.client.post = vi.fn((url) => (url.includes('/enable')
                    ? Promise.resolve({ id: 'calendar-x', isEnabled: true, configUrl: null })
                    : Promise.reject({ status: 504, message: 'Gateway Timeout' })));
                const reread = deferred();
                globalThis.humhubStubs.client.get = vi.fn(() => reread.promise);
                wrapper = mountDialog(InstallDialog, { module: marketplaceModule() });

                click(footerButton('Install'));
                await flushPromises();

                expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/marketplace/module?pageSize=100&id=calendar-x');
                expect(dialog().querySelector('[role="progressbar"]')).not.toBeNull();
                expect(dialog().querySelector('.btn-close').disabled).toBe(true);

                reread.resolve({ results: [{ ...marketplaceModule(), installedVersion: '2.0.0' }] });
                await flushPromises();

                expect(wrapper.emitted('installed')).toEqual([[{ ...marketplaceModule(), installedVersion: '2.0.0' }]]);
                expect(title()).toBe('Activate Module');
                expect(dialog().querySelector('[role="alert"]')).toBeNull();
            });

            it('does not offer Install again, but asks to reload the page', async () => {
                globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({ status: 500, message: 'Internal Server Error' }));
                globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ results: [marketplaceModule()] }));
                wrapper = mountDialog(InstallDialog, { module: marketplaceModule() });

                click(footerButton('Install'));
                await flushPromises();

                expect(title()).toBe('Install Module');
                expect(dialog().querySelector('[role="alert"]').textContent).toBe('Internal Server Error');
                expect(dialog().querySelector('.c-mp-dialog__text').textContent).toBe('The installation did not finish. Please reload the page before trying again.');
                expect(footerButtons().map((b) => b.textContent.trim())).toEqual(['Close']);
                expect(dialog().querySelector('.btn-close').disabled).toBe(false);
                expect(wrapper.emitted('installed')).toBeUndefined();

                click(footerButton('Close'));
                expect(wrapper.emitted('close')).toHaveLength(1);
            });

            it('asks to reload the page when the module cannot be re-read either', async () => {
                globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({ status: 0 }));
                globalThis.humhubStubs.client.get = vi.fn(() => Promise.reject({ status: 0 }));
                wrapper = mountDialog(InstallDialog, { module: marketplaceModule() });

                click(footerButton('Install'));
                await flushPromises();

                expect(dialog().querySelector('[role="alert"]').textContent).toBe('Could not install the module.');
                expect(footerButtons().map((b) => b.textContent.trim())).toEqual(['Close']);
            });

            it('asks to reload the page after a registered key, too', async () => {
                globalThis.humhubStubs.client.post = vi.fn((url) => (url.includes('licence-key') ? Promise.resolve({}) : Promise.reject({ status: 502, message: 'Bad Gateway' })));
                wrapper = mountDialog(InstallDialog, { module: paid(), initialStep: 'buy' });
                click(footerButton('Add License'));
                await flushPromises();

                type(keyInput(), 'KEY-1');
                click(footerButton('Install'));
                await flushPromises();

                expect(dialog().querySelector('.c-licence-field__input')).toBeNull();
                expect(dialog().querySelector('[role="alert"]').textContent).toBe('Bad Gateway');
                expect(footerButtons().map((b) => b.textContent.trim())).toEqual(['Close']);
            });
        });

        it('reports nothing once unmounted during the installation', async () => {
            const install = deferred();
            globalThis.humhubStubs.client.post = vi.fn(() => install.promise);
            wrapper = mountDialog(InstallDialog, { module: marketplaceModule() });

            click(footerButton('Install'));
            await flushPromises();
            wrapper.unmount();
            install.resolve({ ...marketplaceModule(), installedVersion: '2.0.0' });
            await flushPromises();

            expect(wrapper.emitted('installed')).toBeUndefined();
            expect(statuses).toEqual([]);
            wrapper = null;
        });

        it('re-reads nothing once unmounted during a failing installation', async () => {
            const install = deferred();
            globalThis.humhubStubs.client.post = vi.fn(() => install.promise);
            wrapper = mountDialog(InstallDialog, { module: marketplaceModule() });

            click(footerButton('Install'));
            await flushPromises();
            wrapper.unmount();
            install.reject({ status: 504 });
            await flushPromises();

            expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
            wrapper = null;
        });
    });

    describe('activating', () => {
        const installed = async (activation) => {
            globalThis.humhubStubs.client.post = vi.fn((url) => (url.includes('/enable')
                ? activation()
                : Promise.resolve({ ...marketplaceModule(), installedVersion: '2.0.0' })));
            wrapper = mountDialog(InstallDialog, { module: marketplaceModule() });
            click(footerButton('Install'));
            await flushPromises();
        };

        it('activates, then offers the configuration', async () => {
            const pending = deferred();
            await installed(() => pending.promise);

            click(footerButton('Activate'));
            await flushPromises();
            expect(globalThis.humhubStubs.client.post).toHaveBeenLastCalledWith('/api/v2/module/calendar-x/enable');
            expect(footerButton('Activate').disabled).toBe(true);
            expect(footerButton('Activate').querySelector('.c-mp-spin')).not.toBeNull();
            expect(footerButton('Close').disabled).toBe(true);

            pending.resolve({ id: 'calendar-x', isEnabled: true, configUrl: '/calendar/config' });
            await flushPromises();

            expect(wrapper.emitted('activated')).toEqual([[expect.objectContaining({ id: 'calendar-x', installedVersion: '2.0.0', isEnabled: true, configUrl: '/calendar/config' })]]);
            expect(title()).toBe('Configure Module');
            expect(dialog().querySelector('.c-mp-dialog__text').textContent).toBe('Calendar X was activated! Configure it now or later in Module Administration.');
            expect(footerButton('Configure').getAttribute('href')).toBe('/calendar/config');

            click(footerButton('Close'));
            expect(wrapper.emitted('close')).toHaveLength(1);
        });

        it('closes with a success message without a configuration', async () => {
            await installed(() => Promise.resolve({ id: 'calendar-x', isEnabled: true, configUrl: null }));

            click(footerButton('Activate'));
            await flushPromises();

            expect(statuses.map((entry) => entry.message)).toEqual(['Calendar X installed successfully.', 'Calendar X was activated.']);
            expect(wrapper.emitted('close')).toHaveLength(1);
        });

        it('reports nothing once unmounted during the activation', async () => {
            const pending = deferred();
            await installed(() => pending.promise);

            click(footerButton('Activate'));
            await flushPromises();
            wrapper.unmount();
            pending.resolve({ id: 'calendar-x', isEnabled: true, configUrl: null });
            await flushPromises();

            expect(wrapper.emitted('activated')).toBeUndefined();
            expect(wrapper.emitted('close')).toBeUndefined();
            expect(statuses.map((entry) => entry.message)).toEqual(['Calendar X installed successfully.']);
            wrapper = null;
        });

        it('cannot be closed while activating', async () => {
            const pending = deferred();
            await installed(() => pending.promise);

            click(footerButton('Activate'));
            await flushPromises();

            expect(dialog().querySelector('.btn-close').disabled).toBe(true);
            document.dispatchEvent(new KeyboardEvent('keydown', { key: 'Escape' }));
            await flushPromises();
            expect(wrapper.emitted('close')).toBeUndefined();
            pending.reject({ message: 'x' });
            await flushPromises();
        });

        it('stays and shows the error when activating fails', async () => {
            await installed(() => Promise.reject({ message: 'Could not enable module!' }));

            click(footerButton('Activate'));
            await flushPromises();

            expect(title()).toBe('Activate Module');
            expect(dialog().querySelector('[role="alert"]').textContent).toBe('Could not enable module!');
            expect(footerButton('Activate').disabled).toBe(false);
            expect(wrapper.emitted('activated')).toBeUndefined();
        });
    });
});

describe('SettingsDialog', () => {
    const settings = (overrides = {}) => ({ includeBetaUpdates: false, includeCommunityModules: false, ...overrides });
    const tab = (label) => [...dialog().querySelectorAll('[role="tab"]')].find((t) => t.textContent.trim() === label);
    const panel = (key) => dialog().querySelector(`[role="tabpanel"][id$="-panel-${key}"]`);
    const rows = () => [...dialog().querySelectorAll('.c-licence-list__item')];
    const keyInput = () => dialog().querySelector('.c-licence-field__input');
    const addButton = () => dialog().querySelector('.c-mp-settings__add');
    const settingInput = (label) => {
        const row = [...dialog().querySelectorAll('.c-setting-row')].find((r) => r.querySelector('.c-setting-row__title').textContent === label);
        return row.querySelector('input[data-setting]');
    };
    const toggle = (input, checked) => {
        input.checked = checked;
        input.dispatchEvent(new Event('change'));
    };
    const purchased = [
        marketplaceModule({ id: 'survey', name: 'Survey', licenceKey: 'A240-7204', purchased: true }),
        marketplaceModule({ id: 'map', name: 'Members Map', licenceKey: 'F544-7E1D', purchased: true, installedVersion: '2.8.1', marketplaceUrl: null }),
    ];

    const open = async (props = {}) => {
        wrapper = mountDialog(SettingsDialog, { settings: settings(), installationId: 'abc123', moduleAdministrationUrl: '/admin/module', ...props });
        await flushPromises();
    };

    describe('the tabs', () => {
        it('opens on the licences, and switches with click and arrow keys', async () => {
            await open();

            expect(dialog().querySelector('.modal-title').textContent).toBe('Marketplace Settings');
            expect(dialog().querySelector('[role="tablist"]')).not.toBeNull();
            expect(tab('Licenses').getAttribute('aria-selected')).toBe('true');
            expect(tab('Licenses').querySelector('.ti-key')).not.toBeNull();
            expect(tab('Settings').getAttribute('aria-selected')).toBe('false');
            expect(tab('Settings').getAttribute('tabindex')).toBe('-1');
            expect(tab('Settings').querySelector('.ti-settings')).not.toBeNull();
            expect(panel('licenses').style.display).toBe('');
            expect(panel('settings').style.display).toBe('none');
            expect(panel('licenses').getAttribute('aria-labelledby')).toBe(tab('Licenses').id);

            keydown(tab('Licenses'), 'ArrowRight');
            await flushPromises();
            expect(tab('Settings').getAttribute('aria-selected')).toBe('true');
            expect(document.activeElement).toBe(tab('Settings'));
            expect(panel('licenses').style.display).toBe('none');

            keydown(tab('Settings'), 'Home');
            await flushPromises();
            expect(tab('Licenses').getAttribute('aria-selected')).toBe('true');

            click(tab('Settings'));
            await flushPromises();
            expect(panel('settings').style.display).toBe('');
        });

        it('can open on the settings', async () => {
            await open({ initialTab: 'settings' });

            expect(tab('Settings').getAttribute('aria-selected')).toBe('true');
        });

        it('closes with its full-width Close', async () => {
            await open();

            expect(footerButtons().map((b) => b.textContent.trim())).toEqual(['Close']);
            click(footerButton('Close'));
            expect(wrapper.emitted('close')).toHaveLength(1);
        });
    });

    describe('the licences', () => {
        beforeEach(() => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ results: purchased }));
        });

        it('lists the purchased modules with their key and the installation id', async () => {
            await open();

            expect(globalThis.humhubStubs.client.get).toHaveBeenCalledWith('/api/v2/marketplace/module?pageSize=100&tag=purchased');
            expect(rows().map((r) => r.querySelector('.c-licence-list__name').textContent)).toEqual(['Survey', 'Members Map']);
            expect(rows()[0].querySelector('.c-licence-list__key').textContent).toBe('License: A240-7204');

            const install = rows()[0].querySelector('.c-licence-list__action');
            expect(install.textContent.trim()).toBe('Install');
            expect(install.getAttribute('aria-label')).toBe('Install Survey');
            expect(install.classList.contains('btn-primary')).toBe(true);
            const info = rows()[0].querySelector('a[target="_blank"]');
            expect(info.getAttribute('href')).toBe('https://marketplace.humhub.com/module/calendar-x');
            expect(info.getAttribute('aria-label')).toBe('Module information');

            const installed = rows()[1].querySelector('.c-licence-list__action');
            expect(installed.textContent.trim()).toBe('Installed');
            expect(installed.disabled).toBe(true);
            expect(rows()[1].querySelector('a')).toBeNull();

            expect(dialog().querySelector('.c-mp-settings__installation').textContent).toBe('Installation ID: abc123');
        });

        it('says so when nothing was purchased', async () => {
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ results: [] }));
            await open();

            expect(dialog().querySelector('.c-mp-settings__empty').textContent).toBe('No purchased modules found!');
        });

        it('asks for the installation of a licensed module, unless another action runs', async () => {
            await open();

            click(rows()[0].querySelector('.c-licence-list__action'));
            expect(wrapper.emitted('install')).toEqual([[purchased[0]]]);

            await wrapper.setProps({ installLocked: true });
            expect(rows()[0].querySelector('.c-licence-list__action').disabled).toBe(true);
        });

        it('registers a key: spinner, the new licence at the top, a status, a cleared field', async () => {
            vi.useFakeTimers();
            const meetings = marketplaceModule({ id: 'meetings', name: 'Meetings', licenceKey: 'MEET-INGS', purchased: true });
            const pending = deferred();
            globalThis.humhubStubs.client.post = vi.fn(() => pending.promise);
            await open();
            globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ results: [...purchased, meetings] }));

            expect(addButton().getAttribute('aria-label')).toBe('Add License Key');
            expect(addButton().getAttribute('title')).toBe('Add License Key');
            expect(addButton().disabled).toBe(true);
            type(keyInput(), ' MEET-INGS ');
            await flushPromises();
            click(addButton());
            await flushPromises();

            expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/marketplace/licence-key', { data: { licenceKey: 'MEET-INGS' } });
            expect(keyInput().disabled).toBe(true);
            expect(addButton().disabled).toBe(true);
            expect(addButton().querySelector('.ti-loader')).not.toBeNull();

            pending.resolve({ message: 'Module license added!' });
            await flushPromises();

            expect(wrapper.emitted('registered')).toHaveLength(1);
            expect(rows().map((r) => r.querySelector('.c-licence-list__name').textContent)).toEqual(['Meetings', 'Survey', 'Members Map']);
            expect(rows()[0].classList.contains('is-entering')).toBe(true);
            expect(rows()[1].classList.contains('is-entering')).toBe(false);
            expect(statuses).toEqual([expect.objectContaining({ level: 'success', message: 'License added for Meetings.' })]);
            expect(keyInput().value).toBe('');
            expect(keyInput().disabled).toBe(false);
            expect(keyInput().classList.contains('is-valid')).toBe(true);
            expect(addButton().querySelector('.ti-check')).not.toBeNull();

            await vi.advanceTimersByTimeAsync(ADDED_MS);
            expect(addButton().querySelector('.ti-plus')).not.toBeNull();
            expect(keyInput().classList.contains('is-valid')).toBe(false);
        });

        it('says "License added." when no new module shows up', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ message: 'ok' }));
            await open();

            type(keyInput(), 'KEY');
            keydown(keyInput(), 'Enter');
            await flushPromises();

            expect(statuses.map((entry) => entry.message)).toEqual(['License added.']);
            expect(rows().some((r) => r.classList.contains('is-entering'))).toBe(false);
        });

        it('cannot add a key before the licences are loaded', async () => {
            const initial = deferred();
            globalThis.humhubStubs.client.get = vi.fn(() => initial.promise);
            await open();

            expect(keyInput().disabled).toBe(true);
            type(keyInput(), 'KEY');
            await flushPromises();
            expect(addButton().disabled).toBe(true);
            wrapper.vm.register();
            await flushPromises();
            expect(globalThis.humhubStubs.client.post).not.toHaveBeenCalled();

            initial.resolve({ results: purchased });
            await flushPromises();
            expect(keyInput().disabled).toBe(false);
            expect(addButton().disabled).toBe(false);
        });

        it('never lets an older load overwrite a newer one', async () => {
            const loads = [];
            globalThis.humhubStubs.client.get = vi.fn(() => {
                const next = deferred();
                loads.push(next);
                return next.promise;
            });
            await open();
            wrapper.vm.load();
            await flushPromises();

            loads[1].resolve({ results: [purchased[1]] });
            await flushPromises();
            loads[0].resolve({ results: purchased });
            await flushPromises();

            expect(rows().map((r) => r.querySelector('.c-licence-list__name').textContent)).toEqual(['Members Map']);
            expect(dialog().querySelector('.c-mp-settings__loading')).toBeNull();
        });

        it('marks no licence as new when the list could not be loaded before', async () => {
            let fail = true;
            globalThis.humhubStubs.client.get = vi.fn(() => (fail ? Promise.reject({ status: 503 }) : Promise.resolve({ results: purchased })));
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({ message: 'ok' }));
            await open();
            fail = false;

            type(keyInput(), 'KEY');
            keydown(keyInput(), 'Enter');
            await flushPromises();

            expect(rows()).toHaveLength(2);
            expect(rows().some((r) => r.classList.contains('is-entering'))).toBe(false);
            expect(statuses.map((entry) => entry.message)).toEqual(['License added.']);
        });

        it('keeps a rejected key in the field with its message', async () => {
            globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject({ status: 422, errors: { licenceKey: ['Invalid module license key!'] } }));
            await open();

            type(keyInput(), 'wrong');
            click(addButton());
            await flushPromises();

            expect(keyInput().value).toBe('wrong');
            expect(keyInput().getAttribute('aria-invalid')).toBe('true');
            expect(dialog().querySelector('.invalid-feedback').textContent).toBe('Invalid module license key!');
            expect(keyInput().getAttribute('aria-describedby')).toBe(dialog().querySelector('.invalid-feedback').id);
            expect(wrapper.emitted('registered')).toBeUndefined();
            expect(statuses).toEqual([]);
        });
    });

    describe('the settings', () => {
        const saved = () => globalThis.humhubStubs.client.ajax.mock.calls.filter(([, cfg]) => cfg && cfg.method === 'PATCH');

        it('shows both checkboxes with their hints and the module administration link', async () => {
            await open({ settings: settings({ includeCommunityModules: true }) });

            const texts = [...dialog().querySelectorAll('.c-setting-row')].map((r) => r.querySelector('.form-check').textContent.trim());
            expect(texts).toEqual([
                'Beta ModulesAllow modules in Beta to be installed.',
                'Unverified ModulesShow modules contributed by the community that are not tested or maintained by the HumHub team.',
            ]);
            expect(settingInput('Beta Modules').checked).toBe(false);
            expect(settingInput('Unverified Modules').checked).toBe(true);
            expect(dialog().querySelector(`label[for="${settingInput('Beta Modules').id}"]`)).not.toBeNull();
            expect(dialog().querySelector('.c-mp-settings__admin').getAttribute('href')).toBe('/admin/module');
        });

        it('saves a checkbox at once', async () => {
            globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ includeBetaUpdates: true, includeCommunityModules: false }));
            await open();

            toggle(settingInput('Beta Modules'), true);
            await flushPromises();

            expect(saved()).toHaveLength(1);
            expect(saved()[0][0]).toBe('/api/v2/marketplace/settings');
            expect(saved()[0][1]).toMatchObject({ data: { includeBetaUpdates: 1 } });
            expect(wrapper.emitted('settings-changed')).toEqual([[{ includeBetaUpdates: true, includeCommunityModules: false }]]);
            expect(settingInput('Beta Modules').checked).toBe(true);
        });

        it('unchecks again and shows the error when saving fails', async () => {
            globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.reject({ message: 'Not allowed' }));
            await open();

            toggle(settingInput('Beta Modules'), true);
            await flushPromises();

            expect(settingInput('Beta Modules').checked).toBe(false);
            expect(dialog().querySelector('[role="alert"]').textContent).toBe('Not allowed');
            expect(wrapper.emitted('settings-changed')).toBeUndefined();
        });

        it('saves unverified modules only after the risk acknowledgement', async () => {
            globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ includeBetaUpdates: false, includeCommunityModules: true }));
            await open();
            const confirm = () => [...dialog().querySelectorAll('.c-setting-row button')].find((b) => b.textContent.trim() === 'Confirm');

            toggle(settingInput('Unverified Modules'), true);
            await flushPromises();

            expect(saved()).toHaveLength(0);
            const row = settingInput('Unverified Modules').closest('.c-setting-row');
            expect(row.classList.contains('is-expanded')).toBe(true);
            expect(settingInput('Unverified Modules').hasAttribute('aria-expanded')).toBe(false);
            expect(settingInput('Unverified Modules').hasAttribute('aria-controls')).toBe(false);
            expect(row.textContent).toContain('not tested or maintained by the HumHub team');
            expect(row.textContent).toContain('I understand the risk and want to continue.');
            expect(confirm().disabled).toBe(true);

            const acknowledge = row.querySelector('input[type="checkbox"]:not([data-setting])');
            expect(document.activeElement).toBe(acknowledge);
            acknowledge.checked = true;
            acknowledge.dispatchEvent(new Event('change'));
            await flushPromises();
            expect(confirm().disabled).toBe(false);

            click(confirm());
            await flushPromises();

            expect(saved()).toHaveLength(1);
            expect(saved()[0][1]).toMatchObject({ data: { includeCommunityModules: 1 } });
            expect(row.classList.contains('is-expanded')).toBe(false);
            expect(settingInput('Unverified Modules').checked).toBe(true);
            expect(wrapper.emitted('settings-changed')).toEqual([[{ includeBetaUpdates: false, includeCommunityModules: true }]]);
        });

        it('keeps the open acknowledgement when another setting was saved meanwhile', async () => {
            globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ includeBetaUpdates: true, includeCommunityModules: false }));
            await open();

            toggle(settingInput('Unverified Modules'), true);
            await flushPromises();
            const row = settingInput('Unverified Modules').closest('.c-setting-row');
            const acknowledge = row.querySelector('input[type="checkbox"]:not([data-setting])');
            acknowledge.checked = true;
            acknowledge.dispatchEvent(new Event('change'));
            await flushPromises();

            toggle(settingInput('Beta Modules'), true);
            await flushPromises();

            expect(saved()).toHaveLength(1);
            expect(settingInput('Beta Modules').checked).toBe(true);
            expect(row.classList.contains('is-expanded')).toBe(true);
            expect(settingInput('Unverified Modules').checked).toBe(true);
            expect(row.querySelector('input[type="checkbox"]:not([data-setting])').checked).toBe(true);
        });

        it('saves nothing when unverified modules are switched off before confirming', async () => {
            await open();

            toggle(settingInput('Unverified Modules'), true);
            await flushPromises();
            toggle(settingInput('Unverified Modules'), false);
            await flushPromises();

            expect(settingInput('Unverified Modules').closest('.c-setting-row').classList.contains('is-expanded')).toBe(false);
            expect(settingInput('Unverified Modules').checked).toBe(false);
            expect(saved()).toHaveLength(0);
        });

        it('switches unverified modules off without asking', async () => {
            globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({ includeBetaUpdates: false, includeCommunityModules: false }));
            await open({ settings: settings({ includeCommunityModules: true }) });

            toggle(settingInput('Unverified Modules'), false);
            await flushPromises();

            expect(saved()[0][1]).toMatchObject({ data: { includeCommunityModules: 0 } });
            expect(wrapper.emitted('settings-changed')).toHaveLength(1);
        });
    });
});
