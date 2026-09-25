import { beforeEach, describe, expect, it, vi } from 'vitest';
import {
    enableModule, fetchCoreVersion, fetchModules, installModule, payload, registerLicenceKey, saveSettings, updateModule,
} from '../../modules/marketplace/vue/components/marketplaceApi.js';

await import('../../resources/js/humhub/humhub.vue.js');

describe('marketplaceApi requests', () => {
    beforeEach(() => {
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.resolve({}));
        globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({}));
    });

    it('installModule POSTs to the install endpoint', async () => {
        await installModule('calendar-x');

        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/marketplace/module/calendar-x/install');
    });

    it('updateModule POSTs to the update endpoint', async () => {
        await updateModule('calendar-x');

        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/marketplace/module/calendar-x/update');
    });

    it('enableModule POSTs to the enable endpoint', async () => {
        await enableModule('calendar-x');

        expect(globalThis.humhubStubs.client.post).toHaveBeenCalledWith('/api/v2/module/calendar-x/enable');
    });

    it('saveSettings PATCHes the given values and maps the response to strict booleans', async () => {
        globalThis.humhubStubs.client.ajax = vi.fn(() => Promise.resolve({
            includeBetaUpdates: true,
            includeCommunityModules: 'yes', // anything not === true is treated as false
        }));

        const result = await saveSettings({ includeBetaUpdates: true, includeCommunityModules: false });

        expect(globalThis.humhubStubs.client.ajax).toHaveBeenCalledWith(
            '/api/v2/marketplace/settings',
            expect.objectContaining({
                method: 'PATCH',
                data: { includeBetaUpdates: true, includeCommunityModules: false },
            }),
        );
        expect(result).toEqual({ includeBetaUpdates: true, includeCommunityModules: false });
    });
});

describe('marketplaceApi payloads', () => {
    // What humhub.client resolves: its Response wrapper, with the JSON body under `response`
    // and also merged onto the wrapper itself.
    const wrapped = (body) => ({ status: 200, xhr: {}, textStatus: 'success', response: body, ...body });

    it('unwraps a Response-like object to its JSON body', () => {
        expect(payload({ status: 200, xhr: {}, response: { id: 'a' }, id: 'a' })).toEqual({ id: 'a' });
    });

    it('passes a plain payload through', () => {
        expect(payload({ id: 'a' })).toEqual({ id: 'a' });
        expect(payload(null)).toBeNull();
    });

    it('resolves plain payloads from every call', async () => {
        const module = { id: 'a', name: 'A' };
        globalThis.humhubStubs.client.post = vi.fn((url) => Promise.resolve(wrapped(url.includes('licence-key') ? { message: 'ok' } : module)));
        globalThis.humhubStubs.client.ajax = vi.fn((url) => Promise.resolve(wrapped(url.includes('settings')
            ? { includeBetaUpdates: true, includeCommunityModules: false }
            : module)));
        globalThis.humhubStubs.client.get = vi.fn((url) => Promise.resolve(wrapped(url.includes('core-version')
            ? { latest: '1.20.1', updateAvailable: true }
            : { results: [module], total: 1 })));

        await expect(installModule('a')).resolves.toEqual(module);
        await expect(updateModule('a')).resolves.toEqual(module);
        await expect(enableModule('a')).resolves.toEqual(module);
        await expect(registerLicenceKey('K')).resolves.toEqual({ message: 'ok' });
        await expect(fetchCoreVersion()).resolves.toEqual({ latest: '1.20.1', updateAvailable: true });
        await expect(fetchModules({ tag: 'purchased' })).resolves.toEqual([module]);
        await expect(saveSettings({ includeBetaUpdates: 1 })).resolves.toEqual({ includeBetaUpdates: true, includeCommunityModules: false });
    });

    it('leaves rejections unchanged', async () => {
        const failure = { status: 422, response: { errors: { id: ['x'] } }, errors: { id: ['x'] } };
        globalThis.humhubStubs.client.post = vi.fn(() => Promise.reject(failure));

        await expect(installModule('a')).rejects.toBe(failure);
    });
});
