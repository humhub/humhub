import { beforeEach, describe, expect, it, vi } from 'vitest';
import { userPickerSource, userPickerImageProps } from '../../vue/components/pickerSource.js';

await import('../../../../resources/js/humhub/humhub.url.js');
await import('../../../../resources/js/humhub/humhub.vue.js');

describe('user picker source', () => {
    beforeEach(() => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ results: [
            { id: 4, guid: 'g4', displayName: 'Anna', displayNameSub: 'PM', imageUrl: '/a.jpg', url: '/u/anna' },
        ] }));
    });

    it('searches the user picker endpoint and maps users to items by item key', async () => {
        const items = await userPickerSource({ itemKey: 'guid', params: { spaceId: 3 } }).search('an');

        expect(globalThis.humhubStubs.client.get.mock.calls[0][0]).toContain('user/picker?q=an&pageSize=20&spaceId=3');
        expect(items).toEqual([{ id: 'g4', text: 'Anna', subText: 'PM', image: { type: 'user', guid: 'g4', displayName: 'Anna', imageUrl: '/a.jpg', url: '/u/anna' } }]);
    });

    it('answers nothing for an empty term without a space', async () => {
        expect(await userPickerSource({ itemKey: 'id', params: {} }).search('')).toEqual([]);
        expect(globalThis.humhubStubs.client.get).not.toHaveBeenCalled();
    });

    it('maps a user image to UserImage props', () => {
        expect(userPickerImageProps({ type: 'user', guid: 'g4', displayName: 'Anna', imageUrl: '/a.jpg' }, 24))
            .toEqual({ guid: 'g4', displayName: 'Anna', url: '', imageUrl: '/a.jpg', size: 24, link: false });
    });
});
