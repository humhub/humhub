import { beforeEach, describe, expect, it, vi } from 'vitest';
import { topicPickerSource } from '../../vue/components/pickerSource.js';

await import('../../../../resources/js/humhub/humhub.url.js');
await import('../../../../resources/js/humhub/humhub.vue.js');

describe('topic picker source', () => {
    beforeEach(() => {
        globalThis.humhubStubs.client.get = vi.fn(() => Promise.resolve({ results: [
            { id: 8, name: 'Roadmap', color: '#ff0000', container: null },
            { id: 9, name: 'Docs', color: null, container: null },
        ] }));
    });

    it('searches the topic picker endpoint within the container', async () => {
        const items = await topicPickerSource({ itemKey: 'id', params: { containerId: 5, icon: 'star' } }).search('ro');

        expect(globalThis.humhubStubs.client.get.mock.calls[0][0]).toContain('topic/picker?q=ro&pageSize=20&containerId=5');
        expect(items).toEqual([
            { id: '8', text: 'Roadmap', image: { type: 'color', color: '#ff0000' } },
            { id: '9', text: 'Docs', image: { type: 'icon', name: 'star' } },
        ]);
    });

    it('gives a new topic the module icon', () => {
        expect(topicPickerSource({ itemKey: 'id', params: { icon: 'tag' } }).newImage).toEqual({ type: 'icon', name: 'tag' });
        expect(topicPickerSource({ itemKey: 'id', params: {} }).newImage).toEqual({ type: 'icon', name: 'star' });
    });

    it('answers nothing for an empty term without a container', async () => {
        expect(await topicPickerSource({ itemKey: 'id', params: {} }).search('')).toEqual([]);
    });
});
