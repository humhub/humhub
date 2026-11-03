import { beforeEach, describe, expect, it, vi } from 'vitest';
import { spacePickerSource, spacePickerImageProps } from '../../vue/components/pickerSource.js';

await import('../../../../resources/js/humhub/humhub.url.js');
await import('../../../../resources/js/humhub/humhub.vue.js');

describe('space picker source', () => {
    beforeEach(() => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ results: [
            { id: 2, guid: 's2', name: 'Marketing', description: 'Departments', color: '#6fdbe8', imageUrl: null },
        ] }));
    });

    it('searches the space list for the picker purpose', async () => {
        const items = await spacePickerSource({ itemKey: 'guid', params: { scope: 'all' } }).search('mar');

        const url = globalThis.humhubStubs.client.get.mock.calls[0][0];
        expect(url).toContain('space?purpose=picker&scope=all&q=mar&pageSize=20');
        expect(items).toEqual([{ id: 's2', text: 'Marketing', subText: 'Departments', image: { type: 'space', id: 2, name: 'Marketing', color: '#6fdbe8', imageUrl: null } }]);
    });

    it('defaults the scope to member', async () => {
        await spacePickerSource({ itemKey: 'guid', params: {} }).search('');
        expect(globalThis.humhubStubs.client.get.mock.calls[0][0]).toContain('scope=member');
    });

    it('maps a space image to SpaceImage props', () => {
        expect(spacePickerImageProps({ type: 'space', name: 'Marketing', color: '#6fdbe8', imageUrl: null }, 32))
            .toEqual({ id: null, name: 'Marketing', color: '#6fdbe8', imageUrl: null, width: 32, link: false });
    });
});
